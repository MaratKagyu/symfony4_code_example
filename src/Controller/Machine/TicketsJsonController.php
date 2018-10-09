<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Machine;

use App\Entity\Machine\Ticket;
use App\Entity\Machine\TicketFaultCategory;
use App\Entity\Machine\TicketResponse;
use App\Entity\Product;
use App\Entity\Subsidiary;
use App\Entity\User\User;
use App\Entity\User\UserGroup;
use App\Repository\Machine\TicketRepository;
use App\Repository\User\UserRepository;
use App\Service\AccessProvider;
use App\Service\Journal;
use App\Service\NotificationManager;
use App\Service\SpecialAccessProviders\TicketAccessProvider;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class TicketsJsonController extends Controller
{
    /**
     * @param Request $request
     * @param TicketAccessProvider $ticketAccessProvider
     * @Route("/ticket-list/json", name="ticketListJsonAction")
     * @return \Symfony\Component\HttpFoundation\Response
     * @deprecated
     */
    public function ticketListJsonAction(Request $request, TicketAccessProvider $ticketAccessProvider)
    {
        $ticketAccessProvider->accessToServicingSectionRequired();

        /* @var User $authUser */
        $authUser = $this->getUser();


        // Check if customerId was mentioned
        $customerId = (int)$request->get("customerId", 0);

        // Check if machineId was mentioned
        $machineId = (int)$request->get("machineId", 0);

        // Check if machineId was mentioned
        $assignedToId = (int)$request->get("assignedTo", 0);

        /* @var TicketRepository $ticketRepo */
        $ticketRepo = $this->getDoctrine()->getRepository(Ticket::class);

        $authUserSubsidiariesIds = array_map(
            function (Subsidiary $subsidiary) {
                return $subsidiary->getId();
            },
            $authUser->getSubsidiaryList()->toArray()
        );


        $result = array_map(
            function (Ticket $ticket) {
                return $ticket->toCorrectArray();
            },
            (
                $assignedToId
                    ? $ticketRepo->loadAssignedTickets($assignedToId)
                    : $ticketRepo->loadBundledTickets(
                    $customerId ?: null,
                    $machineId ?: null,
                    ($customerId || $machineId)
                        ? null
                        : $authUserSubsidiariesIds
                    )
            )

        /*
         * $authUser->getSubsidiaryList()->toArray()
         */
        );
        //
        return $this->json($result);

    }

    /**
     * @param Ticket $ticket
     * @param TicketAccessProvider $ticketAccessProvider
     * @Route(
     *     "/json/ticket/{token}",
     *     name="getTicketJsonAction"
     * )
     * @ParamConverter("ticket", options={"mapping": {"token": "token"}})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTicketJsonAction(Ticket $ticket, TicketAccessProvider $ticketAccessProvider)
    {
        $ticketAccessProvider->readAccessRequired($ticket);

        return $this->json($ticket->toCorrectArray());
    }

    /**
     * @Route(
     *     "/json/ticket-fault-categories",
     *     name="getTicketFaultCategoriesListJsonAction"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTicketFaultCategoriesListJsonAction()
    {
        return $this->json(array_map(
            function (TicketFaultCategory $category) {
                return $category->getCategory();
            },
            $this
                ->getDoctrine()
                ->getRepository(TicketFaultCategory::class)
                ->findBy(["status" => TicketFaultCategory::STATUS_AVAILABLE])
        ));
    }

    /**
     * @param TicketAccessProvider $accessProvider
     * @Route(
     *     "/json/ticket-assignee-list",
     *     name="getTicketAssigneeListJsonAction"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTicketAssigneeListJsonAction(TicketAccessProvider $accessProvider)
    {
        $accessProvider->accessToServicingSectionRequired();

        $em = $this->getDoctrine()->getManager();

        // Load Assignee List
        $groupList = array_filter(
            $em->getRepository(UserGroup::class)->findAll(),
            function (UserGroup $group) {
                return in_array(
                    $group->getAccessLevelTo(TicketAccessProvider::SECTION_CODE),
                    [ TicketAccessProvider::ACL_SERVICE_ENGINEER, TicketAccessProvider::ACL_SERVICE_MANAGER ]
                );
            }
        );

        /* @var UserRepository $userRepo*/
        $userRepo = $em->getRepository(User::class);

        return $this->json(array_map(
            function (User $assignee) {
                return $assignee->toShortInfoArray();
            },
            $userRepo->findUsersInGroups($groupList)
        ));
    }

    /**
     * @param Ticket $ticket
     * @param Request $request
     * @param TicketAccessProvider $accessProvider
     * @param NotificationManager $notificationManager
     * @param Journal $journal
     * @Route(
     *     "/json/new-ticket-response/{token}",
     *     name="addNewTicketResponseAction",
     *     methods={"POST"}
     * )
     * @ParamConverter("ticket", options={"mapping": {"token": "token"}})
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addNewTicketResponseAction(
        Ticket $ticket,
        Request $request,
        TicketAccessProvider $accessProvider,
        NotificationManager $notificationManager,
        Journal $journal
    ){
        $previousState = $ticket->toCorrectArray();

        $em = $this->getDoctrine()->getManager();

        $userRepo = $em->getRepository(User::class);

        $accessProvider->writeAccessRequired($ticket);

        //
        $newResponse = new TicketResponse();
        $newResponse
            ->assignTicket($ticket)
            ->setCreator($accessProvider->getUser())
            ->setAssignedTo($userRepo->find($request->request->getInt('assignedToId', 0)) ?: null)
            ->setStatus($request->request->getInt('status', 0))
            ->setMessage($request->request->get('message', ""))
            ->setResponseType($request->request->getInt('responseType', 0))
            ->setDataArray($request->request->get('data', []))
        ;


        if ($newResponse->getAssignedTo()) {
            if ($newResponse->getStatus() == Ticket::STATUS_OPEN) {
                $newResponse->setStatus(Ticket::STATUS_ASSIGNED);
            }
        } else {
            if ($newResponse->getStatus() == Ticket::STATUS_ASSIGNED) {
                $newResponse->setStatus(Ticket::STATUS_OPEN);
            }
        }

        $ticket
            ->setStatus($newResponse->getStatus())
            ->setPriority($newResponse->getPriority())
            ->setAssignedTo($newResponse->getAssignedTo())
        ;

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

        $notificationManager->processTicketNotifications($previousState, $ticket);



        return $this->json(["message" => "ok"]);
    }


    /**
     * @param Ticket $ticket
     * @param TicketAccessProvider $accessProvider
     * @Route(
     *     "/json/ticket-blank-response/{token}",
     *     name="getTicketBlankResponseAction"
     * )
     * @ParamConverter("ticket", options={"mapping": {"token": "token"}})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTicketBlankResponseAction(Ticket $ticket, TicketAccessProvider $accessProvider)
    {
        $accessProvider->writeAccessRequired($ticket);

        $newResponse = new TicketResponse();

        $newResponse
            ->assignTicket($ticket)
            ->setCreator($accessProvider->getUser())
            ->setResponseType(TicketResponse::RESPONSE_TYPE_OTHER)
        ;


        /* @var TicketResponse[] $previousResponseList */
        $previousResponseList = array_reverse($ticket->getResponseList()->toArray());

        foreach ($previousResponseList as $prevResponse) {
            switch ($prevResponse->getResponseType()) {
                case TicketResponse::RESPONSE_TYPE_TRAVEL:
                    $newResponse
                        ->setTravelTable($prevResponse->getTravelTable())
                        ->setTravelLabourCharged($prevResponse->getTravelLabourCharged())
                    ;
                    break;

                case TicketResponse::RESPONSE_TYPE_MACHINE_INFO:
                    $newResponse
                        ->setMachineInfo($prevResponse->getMachineInfo())
                    ;
                    break;

                case TicketResponse::RESPONSE_TYPE_SERVICE_CHECKLIST:
                    $newResponse
                        ->setServiceChecklist($prevResponse->getServiceChecklist())
                    ;
                    break;

                case TicketResponse::RESPONSE_TYPE_FAULT:
                    $newResponse
                        ->setProblemsAndSolutionsList($prevResponse->getProblemsAndSolutionsList())
                    ;
                    break;
                case TicketResponse::RESPONSE_TYPE_INSTALLATION_CHECKLIST:
                    $newResponse
                        ->setInstallationChecklist($prevResponse->getInstallationChecklist())
                    ;
                    break;

                case TicketResponse::RESPONSE_TYPE_PARTS:
                    $newResponse
                        ->setPartsTable($prevResponse->getPartsTable())
                    ;
                    break;
            }
        }


        return $this->json($newResponse->toArray());
    }

    /**
     * @param TicketAccessProvider $accessProvider
     * @Route("/json/tickets/available-parts", name="getAvailablePartsAction")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAvailablePartsAction(
        TicketAccessProvider $accessProvider
    ){
        $accessProvider->accessToServicingSectionRequired();

        return $this->json(array_map(
            function (Product $product) {
                return $product->toInfoArray();
            },
            $this->getDoctrine()
                ->getRepository(Product::class)
                ->findBy(
                    ["status" => Product::STATUS_ACTIVE],
                    ["name" => "ASC"]
                )
        ));
    }
}