<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Machine;

use App\Entity\Machine\Machine;
use App\Entity\Machine\MachineMovement;
use App\Forms\Machine\MoveMachineInForm;
use App\Forms\Machine\MoveMachineOutForm;
use App\Forms\Machine\MoveMachineSubsidiaryForm;
use App\Service\FileUploader;
use App\Service\GoogleMaps;
use App\Service\Journal;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\SpecialAccessProviders\MachineAccessProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;



class MovementController extends Controller
{
    /**
     * @param Machine $machine
     * @param Request $request
     * @param Journal $journal
     * @param MachineAccessProvider $accessProvider
     * @param GoogleMaps $googleMaps
     * @Route(
     *     "/machines/add-movement/{machineId}",
     *     name="addMachineMovementPage",
     *     requirements={"machineId"="\d+"}
     * )
     * @ParamConverter("machine", class="App\Entity\Machine\Machine", options={"id" = "machineId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addMachineMovementPage(
        Machine $machine,
        Request $request,
        Journal $journal,
        MachineAccessProvider $accessProvider,
        GoogleMaps $googleMaps
    ){
        $accessProvider->writeAccessRequired($machine);

        $em = $this->getDoctrine()->getManager();

        $machinePreviousState = $machine->toArray();

        // Decide what direction it should be (if there is a holder, then it's a move in, otherwise - out )
        $moveOut = ! $machine->getCurrentHolder();

        // Prepare initial data
        $movementRecord = new MachineMovement();
        $movementRecord
            ->setMachine($machine)
            ->setDirection($moveOut ? MachineMovement::DIRECTION_OUT : MachineMovement::DIRECTION_IN);


        $movementForm = $this->createForm(
            $moveOut ? MoveMachineOutForm::class : MoveMachineInForm::class,
            $movementRecord
        );

        $movementForm->handleRequest($request);

        $errorText = '';

        if ($movementForm->isSubmitted() && $movementForm->isValid()) {

            $em->persist($movementRecord);

            $movementRecord->setAddedBy($accessProvider->getUser());

            if ($moveOut) {
                // if move out

                $geoResults = $googleMaps->getGeoData(
                    $movementRecord->getLocationAddress()
                    . ($movementRecord->getLocationCity() ? ", " . $movementRecord->getLocationCity() : "")
                    . ($movementRecord->getLocationState() ? ", " . $movementRecord->getLocationState() : "")
                    . ", " . $movementRecord->getLocationCountry()
                );


                if (! $geoResults) {
                    $errorText = "Couldn't recognize the given address location";
                } else {
                    // Set coordinates
                    $movementRecord
                        ->setCoordinatesLatitude($geoResults->getLatitude())
                        ->setCoordinatesLongitude($geoResults->getLongitude())
                    ;

                    // Set address data for fields which weren't filled
                    if (! $movementRecord->getLocationState()) {
                        $movementRecord->setLocationState($geoResults->getState());
                    }

                    if (! $movementRecord->getLocationZip()) {
                        $movementRecord->setLocationZip($geoResults->getZipCode());
                    }

                    if (! $movementRecord->getLocationCity()) {
                        $movementRecord->setLocationCity($geoResults->getState());
                    }
                }

                $machine
                    ->setStatus($movementRecord->getMachineStatus())
                    ->setSubsidiary($machine->getSubsidiary())
                    ->setCurrentHolder($movementRecord->getCustomer())
                    ->setLastMovementDate($movementRecord->getMovementDate())
                    ->setCurrentLocationCity($movementRecord->getLocationCity())
                    ->setCurrentLocationCountry($movementRecord->getLocationCountry())
                    ->setCurrentLocationState($movementRecord->getLocationState())
                    ->setCurrentLocationAddress($movementRecord->getLocationAddress())
                    ->setCurrentLocationZip($movementRecord->getLocationZip())
                    ->setCurrentDistanceFormHQ($movementRecord->getDistance())
                    ->setCoordinatesLongitude($movementRecord->getCoordinatesLongitude())
                    ->setCoordinatesLatitude($movementRecord->getCoordinatesLatitude())
                    ->setCurrentContractType($movementRecord->getContractType())
                ;

            } else {
                // if move in
                $movementRecord
                    ->setDistance($machine->getCurrentDistanceFormHQ())
                    ->setSubsidiary($machine->getSubsidiary())
                    ->setLocationCountry($machine->getSubsidiary()->getCountry())
                    ->setLocationCity($machine->getSubsidiary()->getCity())
                    ->setLocationAddress($machine->getSubsidiary()->getAddress())
                    ->setLocationZip($machine->getSubsidiary()->getZip())
                    ->setCoordinatesLatitude($machine->getSubsidiary()->getCoordinatesLatitude())
                    ->setCoordinatesLongitude($machine->getSubsidiary()->getCoordinatesLongitude())
                ;


                $machine
                    ->setStatus($movementRecord->getMachineStatus())
                    ->setCurrentHolder(null)
                    ->setLastMovementDate($movementRecord->getMovementDate())
                    ->setCurrentLocationCountry($machine->getSubsidiary()->getCountry())
                    ->setCurrentLocationCity($machine->getSubsidiary()->getCity())
                    ->setCurrentLocationState($machine->getSubsidiary()->getState())
                    ->setCurrentLocationAddress($machine->getSubsidiary()->getAddress())
                    ->setCurrentLocationZip($machine->getSubsidiary()->getZip())
                    ->setCurrentDistanceFormHQ(0)
                    ->setCoordinatesLatitude($machine->getSubsidiary()->getCoordinatesLatitude())
                    ->setCoordinatesLongitude($machine->getSubsidiary()->getCoordinatesLongitude())
                    ->setCurrentContractType('')
                ;

            }

            if (! $errorText) {

                $em->flush();

                // Register events in the Journal
                $journal->registerEvent(
                    Journal::ENTITY_TYPE_MACHINE_MOVEMENT,
                    $movementRecord->getId(),
                    Journal::ACTION_ADD,
                    array_filter(
                        $movementRecord->toArray(),
                        function ($value) { return !! $value; }
                    )
                );

                // Register events in the Journal
                $journal->registerEvent(
                    Journal::ENTITY_TYPE_MACHINE,
                    $machine->getId(),
                    (
                    $moveOut
                        ? Journal::ACTION_MACHINE_MOVE_OUT
                        : Journal::ACTION_MACHINE_MOVE_IN
                    ),
                    Journal::getArrayChange(
                        $machinePreviousState,
                        $machine->toArray()
                    )
                );


                return $this->redirectToRoute("machineViewPage", [ "machineId" => $machine->getId() ]);
            }
        }





        return $this->render(
            (
            $moveOut
                ? "machines/move-machine-out-page.html.twig"
                : "machines/move-machine-in-page.html.twig"
            ),
            [
                "machine" => $machine,
                "movementForm" => $movementForm->createView(),
                "errorText" => $errorText
            ]
        );
    }


    /**
     * @param Machine $machine
     * @param Request $request
     * @param Journal $journal
     * @param MachineAccessProvider $accessProvider
     * @Route(
     *     "/machines/move-to-another-subsidiary/{id}",
     *     name="moveMachineToAnotherSubsidiary"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveMachineToAnotherSubsidiaryPage(
        Machine $machine,
        Request $request,
        Journal $journal,
        MachineAccessProvider $accessProvider
    ){
        $accessProvider->writeAccessRequired($machine);

        $em = $this->getDoctrine()->getManager();

        $machinePreviousState = $machine->toArray();

        // Prepare initial data
        $movementRecord = new MachineMovement();
        $movementRecord
            ->setMachine($machine)
            ->setDirection(MachineMovement::DIRECTION_TO_ANOTHER_SUBSIDIARY );


        $movementForm = $this->createForm(
            MoveMachineSubsidiaryForm::class,
            $movementRecord,
            [
                'access_provider' => $accessProvider
            ]
        );

        $movementForm->handleRequest($request);



        if ($movementForm->isSubmitted() && $movementForm->isValid()) {

            $em->persist($movementRecord);

            $movementRecord->setAddedBy($accessProvider->getUser());


            // if move in
            $movementRecord
                ->setLocationCountry($movementRecord->getSubsidiary()->getCountry())
                ->setLocationCity($movementRecord->getSubsidiary()->getCity())
                ->setLocationAddress($movementRecord->getSubsidiary()->getAddress())
                ->setLocationZip($movementRecord->getSubsidiary()->getZip())
                ->setCoordinatesLongitude($movementRecord->getSubsidiary()->getCoordinatesLongitude())
                ->setCoordinatesLatitude($movementRecord->getSubsidiary()->getCoordinatesLatitude())
                //->setDistance(0)
            ;

            $machine
                ->setStatus($movementRecord->getMachineStatus())
                ->setCurrentHolder(null)
                ->setLastMovementDate($movementRecord->getMovementDate())
                ->setSubsidiary($movementRecord->getSubsidiary())
                ->setCurrentLocationCountry($movementRecord->getSubsidiary()->getCountry())
                ->setCurrentLocationCity($movementRecord->getSubsidiary()->getCity())
                ->setCurrentLocationState($movementRecord->getSubsidiary()->getState())
                ->setCurrentLocationAddress($movementRecord->getSubsidiary()->getAddress())
                ->setCurrentLocationZip($movementRecord->getSubsidiary()->getZip())
                ->setCoordinatesLongitude($movementRecord->getSubsidiary()->getCoordinatesLongitude())
                ->setCoordinatesLatitude($movementRecord->getSubsidiary()->getCoordinatesLatitude())
                ->setCurrentDistanceFormHQ(0)
                ->setCurrentContractType("")
            ;

            $em->flush();

            // Register events in the Journal
            $journal->registerEvent(
                Journal::ENTITY_TYPE_MACHINE_MOVEMENT,
                $movementRecord->getId(),
                Journal::ACTION_ADD,
                array_filter(
                    $movementRecord->toArray(),
                    function ($value) { return !! $value; }
                )
            );

            $machineDataChanges = Journal::getArrayChange(
                $machinePreviousState,
                $machine->toArray()
            );

            $machineDataChanges["_initialSubsidiaryId"] = $machinePreviousState['subsidiaryId'];
            $machineDataChanges["_initialSubsidiaryName"] = $machinePreviousState['subsidiaryName'];
            $machineDataChanges["_newSubsidiaryId"] = $movementRecord->getSubsidiary()->getId();
            $machineDataChanges["_newSubsidiaryName"] = $movementRecord->getSubsidiary()->getName();

            // Register events in the Journal
            $journal->registerEvent(
                Journal::ENTITY_TYPE_MACHINE,
                $machine->getId(),
                Journal::ACTION_MACHINE_MOVE_TO_ANOTHER_SUBSIDIARY,
                $machineDataChanges
            );


            return $this->redirectToRoute("machineViewPage", [ "machineId" => $machine->getId() ]);
        }





        return $this->render(
            "machines/move-machine-to-another-subsidiary.html.twig",
            [
                "machine" => $machine,
                "movementForm" => $movementForm->createView()
            ]
        );
    }

    /**
     * @param Machine $machine
     * @param string $fileName
     * @param FileUploader $fileUploader
     * @param MachineAccessProvider $accessProvider
     * @Route(
     *     "/machines/download-contract/{machineId}/{fileName}",
     *     name="downloadMovementContractAction",
     *     requirements={"machineId"="\d+"}
     * )
     * @ParamConverter("machine", class="App\Entity\Machine\Machine", options={"id" = "machineId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadMovementContractAction(
        Machine $machine,
        string $fileName,
        FileUploader $fileUploader,
        MachineAccessProvider $accessProvider
    ){
        $accessProvider->readAccessRequired($machine);

        $fileFullPath = (
            $fileUploader->getProjectRootPath() .
            MachineMovement::getFilesLocationByMachineId($machine->getId()) .
            preg_replace('#\.{2,}#isu', "", $fileName)
        );

        if (! file_exists($fileFullPath)) {
            throw $this->createNotFoundException("File not found");
        }

        return $this->file($fileFullPath);
    }

}