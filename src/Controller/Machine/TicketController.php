<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Machine;

use App\Entity\Machine\Machine;
use App\Entity\Machine\MachineServiceContract;
use App\Entity\Machine\Ticket;
use App\Entity\Machine\TicketResponse;
use App\Entity\User\User;
use App\Forms\Machine\TicketForm;
use App\Repository\Machine\TicketRepository;
use App\Service\FileUploader;
use App\Service\GoogleMaps;
use App\Service\Journal;
use App\Service\NotificationManager;
use App\Service\SpecialAccessProviders\TicketAccessProvider;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Service\SpecialAccessProviders\ServiceContractsAccessProvider as ContractsAccessProvider;


class TicketController extends Controller
{
    /**
     * @param TicketAccessProvider $ticketAccessProvider
     * @param GoogleMaps $googleMaps
     * @Route("/tickets", name="ticketListPage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ticketListPage(
        TicketAccessProvider $ticketAccessProvider,
        GoogleMaps $googleMaps
    ){
        $ticketAccessProvider->accessToServicingSectionRequired();

        $isServiceEngineer = (
            $ticketAccessProvider->getAccessLevelTo(TicketAccessProvider::SECTION_CODE)
            == TicketAccessProvider::ACL_SERVICE_ENGINEER
        );
        //
        return $this->render(
            "machines/tickets/ticket-list.html.twig",
            [
                "prioritiesList" => Ticket::$priorities,
                "ticketAccessProvider" => $ticketAccessProvider,
                "isServiceEngineer" => $isServiceEngineer,
                "authUser" => $ticketAccessProvider->getUser(),
                "googleMaps" => $googleMaps
            ]
        );

    }

    /**
     * @param string $token
     * @param Request $request
     * @param TicketAccessProvider $ticketAccessProvider
     * @param Journal $journal
     * @param NotificationManager $notificationManager
     * @Route(
     *     "/tickets/edit/{token}",
     *     defaults={"token"=""},
     *     name="ticketEditPage"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function ticketEditPage(
        string $token,
        Request $request,
        TicketAccessProvider $ticketAccessProvider,
        Journal $journal,
        NotificationManager $notificationManager
    ){

        /* @var User $authUser */
        $authUser = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        /* @var TicketRepository $ticketRepo*/
        $ticketRepo = $em->getRepository(Ticket::class);

        if ($token) {
            $ticket = $ticketRepo->findOneBy(["token" => $token]);

            if (! $ticket) {
                throw $this->createNotFoundException("Ticket not found");
            }

            $ticketAccessProvider->writeAccessRequired($ticket);

            $previousState = $ticket->toCorrectArray();

        } else {
            if (! $ticketAccessProvider->canAddNewTickets()) {
                throw $this->createAccessDeniedException("You cannot create new tickets");
            }


            // if it's a new record
            $ticket = new Ticket();

            // If we need to set machine
            $machineId = $request->query->getInt('machineId');
            if ($machineId) {
                /* @var Machine $machine */
                $machine = $em->getRepository(Machine::class)->find($machineId);
                if (! $machine) {
                    throw $this->createNotFoundException("Machine not found (id={$machineId})");
                }

                $ticket->setMachine($machine);
            }

            $previousState = [];
        }

        $ticketForm = $this->createForm(
            TicketForm::class,
            $ticket,
            // This form requires Entity Manager
            [
                "entity_manager" => $em,
                "authorizedUser" => $authUser
            ]
        );

        $ticketForm->handleRequest($request);

        // If the form is submitted
        if ($ticketForm->isSubmitted() && $ticketForm->isValid()) {

            $errorOccurred = false;

            // If no errors appeared
            if (! $errorOccurred) {

                // If it's a new ticket
                if (! $ticket->getId()) {
                    $activeServiceContract = $em->getRepository(MachineServiceContract::class)->findOneBy([
                        "machine" => $ticket->getMachine(),
                        "status" => MachineServiceContract::STATUS_ACTIVE
                    ]);

                    $machine = $ticket->getMachine();

                    $ticket
                        // If a technician is assigned, the status is Assigned, otherwise - Open
                        ->setStatus(
                            $ticket->getAssignedTo()
                                ? Ticket::STATUS_ASSIGNED
                                : Ticket::STATUS_OPEN
                        )
                        ->setCreator($authUser)
                        ->setCustomer($machine ? $machine->getCurrentHolder() : null)
                        // Associated the ticket with machine subsidiary
                        ->setSubsidiary($ticket->getMachine()->getSubsidiary())
                        ->setMachineLocationCountry($machine ? $machine->getCurrentLocationCountry() : "")
                        ->setMachineLocationState($machine ? $machine->getCurrentLocationState() : "")
                        ->setMachineLocationCity($machine ? $machine->getCurrentLocationCity() : "")
                        ->setMachineLocationAddress($machine ? $machine->getCurrentLocationAddress() : "")
                        ->setMachineLocationZip($machine ? $machine->getCurrentLocationZip() : "")
                        ->setCoordinatesLatitude($machine ? $machine->getCoordinatesLatitude() : 0)
                        ->setCoordinatesLongitude($machine ? $machine->getCoordinatesLongitude() : 0)
                        ->setMachineDistance(
                            $ticket->getMachine()
                                ? $ticket->getMachine()->getCurrentDistanceFormHQ()
                                : 0
                        )
                        ->setServiceContract($activeServiceContract ?: null)
                        ->setCreatedDateTime(new \DateTime());

                    // Assign new ID and token
                    $ticketRepo->assignTicketId($ticket);
                    $ticket->generateToken();

                    // Assign Previous Ticket
                    $ticketRepo->assignPreviousTicket($ticket);

                    // if the record doesn't exist, we add him to the repo
                    $this->getDoctrine()->getManager()->persist($ticket);
                }

                // if there are changes, we need to detect them
                $ticketResponse = new TicketResponse();
                $ticketResponse->detectTicketChanges($previousState, $ticket->toCorrectArray());

                // if something is changed, we create a new TicketResponse
                if (count($ticketResponse->getTicketChangesList()) && $token) {
                    $ticketResponse
                        ->assignTicket($ticket)
                        ->setCreator($authUser)
                        ->setResponseType(TicketResponse::RESPONSE_TYPE_UPDATE);

                    $em->persist($ticketResponse);
                }


                $em->flush();


                $journal->registerEvent(
                    Journal::ENTITY_TYPE_TICKET,
                    $ticket->getId(),
                    $token ? Journal::ACTION_EDIT : Journal::ACTION_ADD,
                    Journal::getArrayChange($previousState, $ticket->toCorrectArray())
                );

                // Send notifications if needed
                $notificationManager->processTicketNotifications($previousState, $ticket);


                return $this->redirectToRoute("ticketViewPage", ["token" => $ticket->getToken()]);
            }
        }


        return $this->render(
            "machines/tickets/ticket-edit.html.twig",
            [
                "ticketForm" => $ticketForm->createView(),
                "ticket" => $ticket,
                "ticketAccessProvider" => $ticketAccessProvider,
            ]
        );
    }


    /**
     * @param Ticket $ticket
     * @param TicketAccessProvider $accessProvider
     * @Route(
     *     "/tickets/view/{token}",
     *     name="ticketViewPage"
     * )
     * @ParamConverter("ticket", options={"mapping": {"token": "token"}})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ticketViewPage(
        Ticket $ticket,
        TicketAccessProvider $accessProvider
    ){
        $accessProvider->readAccessRequired($ticket);

        $em = $this->getDoctrine()->getManager();

        // Loading service contracts
        $contractList = $em
            ->getRepository(MachineServiceContract::class)
            ->findBy(
                [
                    "machine" => $ticket->getMachine(),
                    "status" => MachineServiceContract::STATUS_ACTIVE
                ],
                [ "startDate" => "ASC" ]
            );

        // Can complete the service
        $canCompleteService = $accessProvider->isCompletable($ticket);

        return $this->render(
            "machines/tickets/ticket-view.html.twig",
            [
                "ticket" => $ticket,
                "contractList" => $contractList,
                "ticketAccessProvider" => $accessProvider,
                "authUserServicingRoles" => $accessProvider->getServicingRoles(),
                "hasAccessToContracts" => (
                    !! $accessProvider->getAccessLevelTo(ContractsAccessProvider::SECTION_CODE)
                ),
                "canCompleteService" => $canCompleteService
            ]
        );
    }


    /**
     * @param Ticket $ticket
     * @param TicketAccessProvider $accessProvider
     * @Route(
     *     "/tickets/complete/{token}",
     *     name="ticketCompletePage"
     * )
     * @ParamConverter("ticket", options={"mapping": {"token": "token"}})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ticketCompletePage(
        Ticket $ticket,
        TicketAccessProvider $accessProvider
    ){
        $accessProvider->accessToCompleteRequired($ticket);

        $em = $this->getDoctrine()->getManager();

        // Loading service contracts
        $contractList = $em
            ->getRepository(MachineServiceContract::class)
            ->findBy(
                [
                    "machine" => $ticket->getMachine(),
                    "status" => MachineServiceContract::STATUS_ACTIVE
                ],
                [ "startDate" => "ASC" ]
            );

        /* @var TicketResponse[] $lastResponses */
        $lastResponses = [];

        /* @var TicketResponse[] $responseList */
        $responseList = array_reverse($ticket->getResponseList()->toArray());
        foreach ($responseList as $response) {
            $lastResponses[$response->getResponseType()] = $response;
        }

        return $this->render(
            "machines/tickets/ticket-complete.html.twig",
            [
                "ticket" => $ticket,
                "contractList" => $contractList,
                "ticketAccessProvider" => $accessProvider,
                "authUserServicingRoles" => $accessProvider->getServicingRoles(),
                "hasAccessToContracts" => (
                    !! $accessProvider->getAccessLevelTo(ContractsAccessProvider::SECTION_CODE)
                ),
                "lastResponses" => $lastResponses
            ]
        );
    }

    /**
     * @param Ticket $ticket
     * @param TicketAccessProvider $accessProvider
     * @param Request $request
     * @param Journal $journal
     * @Route(
     *     "/tickets/complete-action/{token}",
     *     methods={"POST"},
     *     name="ticketCompleteAction"
     * )
     * @ParamConverter("ticket", options={"mapping": {"token": "token"}})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ticketCompleteAction(
        Ticket $ticket,
        TicketAccessProvider $accessProvider,
        Request $request,
        Journal $journal
    ){
        $accessProvider->accessToCompleteRequired($ticket);

        $previousState = $ticket->toCorrectArray();

        $em = $this->getDoctrine()->getManager();

        //
        $newResponse = new TicketResponse();
        $newResponse
            ->assignTicket($ticket)
            ->setCreator($accessProvider->getUser())
            ->setStatus(Ticket::STATUS_SERVICE_COMPLETE)
            ->setResponseType(TicketResponse::RESPONSE_TYPE_COMPLETE)

            ->setSignatureCustomerName($request->request->get('customer-name', ''))
            ->setSignatureCustomerTitle($request->request->get('customer-title', ''))
            ->setSignatureCustomerSignature($request->request->get('customer-signature', ''))
            ->setSignatureUserName($request->request->get('user-name', ''))
            ->setSignatureUserSignature($request->request->get('user-signature', ''))
        ;

        $ticket->setStatus($newResponse->getStatus());

        $newResponse->detectTicketChanges($previousState, $ticket->toCorrectArray());

        $em->persist($newResponse);

        $em->flush();

        //
        $journal->registerEvent(
            Journal::ENTITY_TYPE_TICKET,
            $ticket->getId(),
            Journal::ACTION_EDIT,
            Journal::getArrayChange($previousState, $ticket->toCorrectArray())
        );

        return $this->redirectToRoute("ticketViewPage", ["token" => $ticket->getToken()]);
    }

    /**
     * @param Ticket $ticket
     * @param string $fileName
     * @param TicketAccessProvider $ticketAccessProvider
     * @param \App\Service\FileUploader $fileUploader
     * @Route(
     *     "/tickets/download/{id}/{fileName}",
     *     name="downloadTicketFileAction", requirements={"id"="\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadTicketFileAction(
        Ticket $ticket,
        string $fileName,
        FileUploader $fileUploader,
        TicketAccessProvider $ticketAccessProvider
    ){
        //
        $ticketAccessProvider->readAccessRequired($ticket);

        $fileFullPath = (
            $fileUploader->getProjectRootPath() .
            Ticket::getFilesLocationByTicketId($ticket->getId()) .
            preg_replace('#\.{2,}#isu', "", $fileName)
        );

        if (! file_exists($fileFullPath)) {
            throw $this->createNotFoundException("File not found");
        }

        return $this->file($fileFullPath);
    }

}