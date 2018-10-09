<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Machine;

use App\Entity\Machine\Machine;
use App\Forms\Machine\MachineForm;
use App\Repository\Machine\MachineRepository;
use App\Service\GoogleMaps;
use App\Service\Journal;
use App\Service\SpecialAccessProviders\TicketAccessProvider;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\SpecialAccessProviders\MachineAccessProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class MachinesController extends Controller
{

    /**
     * @param MachineAccessProvider $accessProvider
     * @param GoogleMaps $googleMaps
     * @Route("/machines", name="machinesPage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function machinesPage(
        MachineAccessProvider $accessProvider,
        GoogleMaps $googleMaps
    ){
        $accessProvider->requiresAccessToTheSection();

        return $this->render(
            "machines/machines-list.html.twig",
            [
                "isWritable" => $accessProvider->canAddNewMachines(),
                "googleMaps" => $googleMaps
            ]
        );
    }



    /**
     * @param int $machineId
     * @param Request $request
     * @param Journal $journal
     * @param MachineAccessProvider $accessProvider
     * @Route("/machines/edit/{machineId}", name="machineEditPage", requirements={"machineId"="\d+"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function machineEditPage(
        int $machineId,
        Request $request,
        MachineAccessProvider $accessProvider,
        Journal $journal
    ){
        $em = $this->getDoctrine()->getManager();

        /* @var MachineRepository $machinesRepo*/
        $machinesRepo = $em->getRepository(Machine::class);

        if ($machineId) {
            /* @var \App\Entity\Machine\Machine $machine */
            $machine = $machinesRepo->find($machineId);

            if (! $machine) {
                throw $this->createNotFoundException("Machine not found");
            }

            // Check if the user can edit this record
            $accessProvider->writeAccessRequired($machine);

            $previousState = $machine->toArray();

        } else {
            // Check if the user can add new machines
            $accessProvider->canAddNewMachines();

            // if it's a new record
            $machine = new Machine();

            $previousState = [];
        }



        $machineForm = $this->createForm(
            MachineForm::class,
            $machine,
            [
                'entity_manager' => $em,
                'access_provider' => $accessProvider
            ]
        );

        $machineForm->handleRequest($request);

        $errorText = '';

        // If the form is submitted
        if ($machineForm->isSubmitted() && $machineForm->isValid()) {

            // If no errors appeared
            if (! $errorText) {


                if (! $machine->getId()) {
                    $machine
                        ->setCurrentLocationCountry($machine->getSubsidiary()->getCountry())
                        ->setCurrentLocationCity($machine->getSubsidiary()->getCity())
                        ->setCurrentLocationState($machine->getSubsidiary()->getState())
                        ->setCurrentLocationAddress($machine->getSubsidiary()->getAddress())
                        ->setCurrentLocationZip($machine->getSubsidiary()->getZip())
                        ->setCoordinatesLongitude($machine->getSubsidiary()->getCoordinatesLongitude())
                        ->setCoordinatesLatitude($machine->getSubsidiary()->getCoordinatesLatitude())
                        ->setLastMovementDate(new \DateTime())
                        ->setCreator($accessProvider->getUser())
                    ;

                    // if the record doesn't exist, we add him to the repo
                    $this->getDoctrine()->getManager()->persist($machine);
                }

                // Check if the user has access to the record (after is updated it
                // in order to avoid situations when user creates a record he cannot access)
                $accessProvider->writeAccessRequired($machine);

                $em->flush();


                //
                $journal->registerEvent(
                    Journal::ENTITY_TYPE_MACHINE,
                    $machine->getId(),
                    $machineId ? Journal::ACTION_EDIT : Journal::ACTION_ADD,
                    Journal::getArrayChange($previousState, $machine->toArray())
                );


                return $this->redirectToRoute("machineViewPage", ["machineId" => $machine->getId()]);
            }
        }


        return $this->render(
            "machines/machine-edit.html.twig",
            [
                "machineForm" => $machineForm->createView(),
                "machine" => $machine
            ]
        );
    }


    /**
     * @param Machine $machine
     * @param MachineAccessProvider  $accessProvider
     * @param TicketAccessProvider $ticketAccessProvider
     * @Route(
     *     "/machines/view/{machineId}",
     *     name="machineViewPage",
     *     requirements={"machineId"="\d+"}
     * )
     * @ParamConverter("machine", class="App\Entity\Machine\Machine", options={"id" = "machineId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function machineViewPage(
        Machine $machine,
        MachineAccessProvider $accessProvider,
        TicketAccessProvider $ticketAccessProvider
    ){
        $accessProvider->readAccessRequired($machine);

        return $this->render(
            "machines/machine-view.html.twig",
            [
                "machine" => $machine,
                "isWritable" => $accessProvider->isWritable($machine),
                "canSeeTickets" => $ticketAccessProvider->hasAccessToServicingSection(),
                "canAddTickets" => $ticketAccessProvider->canAddNewTickets()
            ]
        );
    }



}