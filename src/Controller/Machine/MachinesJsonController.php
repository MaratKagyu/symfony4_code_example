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
use App\Entity\Machine\MachineServiceContract;
use App\Entity\Machine\Ticket;
use App\Repository\Machine\MachineRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\SpecialAccessProviders\MachineAccessProvider;


class MachinesJsonController extends Controller
{
    /**
     * @param Machine $machine
     * @param MachineAccessProvider $accessProvider
     * @Route(
     *     "/json/machine-timeline/{id}",
     *     name="jsonGetMachineTimeline"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jsonGetMachineTimeline(Machine $machine, MachineAccessProvider $accessProvider)
    {
        $accessProvider->readAccessRequired($machine);

        $em = $this->getDoctrine()->getManager();

        /*
         * Timeline format
         * [
         *     date: "Y-m-d",
         *
         *     title: "here goes the title"
         *     descriptions: [
         *
         *     ]
         * ]
         */
        $timeLine = [];


        // When created
        if ($machine->getCreator()) {
            $timeLine[] = [
                "date" => $machine->getCreatedDateTime()->format("Y-m-d"),
                "title" => "<strong>The Machine was added to the system</strong>",
                "description" => [
                    "<strong>Created By</strong>: " . $machine->getCreator()->getFullName()
                ],
                "type" => "created"
            ];
        }



        // Movements
        array_map(
            function (MachineMovement $movement) use (&$timeLine, $machine){
                switch ($movement->getDirection()) {
                    case MachineMovement::DIRECTION_OUT:
                        $timeLine[] = [
                            "date" => $movement->getMovementDate()->format("Y-m-d"),
                            "title" => (
                                "<strong>Moved OUT</strong> to <strong>"
                                . "<a href='"
                                . $this->generateUrl(
                                    "customerViewPage",
                                    [ "customerId" => $movement->getCustomer()->getId() ],
                                    UrlGeneratorInterface::ABSOLUTE_URL
                                )
                                . "' target='_blank'>"
                                . htmlspecialchars($movement->getCustomer()->getFullName())
                                . "</a>"
                                . "</strong>"
                            ),
                            "description" => [
                                "<strong>Company</strong>: " . $movement->getCustomer()->getFullName(),
                                "<strong>Location</strong>: " . $movement->getFullAddress()
                            ],
                            "type" => "movement"
                        ];

                        break;

                    case MachineMovement::DIRECTION_IN:
                        $timeLine[] = [
                            "date" => $movement->getMovementDate()->format("Y-m-d"),
                            "title" => (
                                "<strong>Moved IN</strong> to " .
                                ($movement->getSubsidiary() ? $movement->getSubsidiary()->getName() : "")
                            ),
                            "description" => [
                                "<strong>Location</strong>: " .
                                ($movement->getSubsidiary() ? $movement->getSubsidiary()->getLocation() : "")
                            ],
                            "type" => "movement"
                        ];

                        break;

                    case MachineMovement::DIRECTION_TO_ANOTHER_SUBSIDIARY:
                        $timeLine[] = [
                            "date" => $movement->getMovementDate()->format("Y-m-d"),
                            "title" => (
                                "<strong>Moved</strong> to " .
                                ($movement->getSubsidiary() ? $movement->getSubsidiary()->getName() : "")
                            ),
                            "description" => [
                                "<strong>Location</strong>: " .
                                ($movement->getSubsidiary() ? $movement->getSubsidiary()->getLocation() : "")
                            ],
                            "type" => "movement"
                        ];

                        break;
                }
            },
            $em->getRepository(MachineMovement::class)->findBy([ "machine" => $machine ])
        );

        // Tickets
        array_map(
            function (Ticket $ticket) use (&$timeLine, $machine){
                $ticketViewUrl = $this->generateUrl(
                    "ticketViewPage",
                    ["token" => $ticket->getToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $timeLine[] = [
                    "date" => $ticket->getCreatedDateTime()->format("Y-m-d"),
                    "title" => (
                        "New Ticket "
                        . "<strong><a href='{$ticketViewUrl}' target='_blank'>"
                        . $ticket->getTicketId()
                        . "</a></strong>"
                    ),
                    "description" => [
                        "<strong>Type</strong>: " . $ticket->getServiceTypeString(),
                        "<strong>Description</strong>: " . $ticket->getDescription(),
                    ],
                    "type" => "ticket"
                ];


                foreach ($ticket->getResponseList() as $response) {
                    if (in_array("status", $response->getTicketChangesList())) {
                        switch ($response->getStatus()) {
                            case Ticket::STATUS_IN_PROGRESS:
                                $timeLine[] = [
                                    "date" => $response->getCreatedDateTime()->format("Y-m-d"),
                                    "title" => (
                                        "Ticket "
                                        . "<strong><a href='{$ticketViewUrl}' target='_blank'>"
                                        . $ticket->getTicketId()
                                        . "</a></strong>"
                                        . " state changed to <strong>In Progress</strong>"
                                    ),
                                    "description" => [
                                        $response->getMessage(),
                                    ],
                                    "type" => "ticket"
                                ];
                                break;

                            case Ticket::STATUS_SERVICE_COMPLETE:
                                $timeLine[] = [
                                    "date" => $response->getCreatedDateTime()->format("Y-m-d"),
                                    "title" => (
                                        "Ticket "
                                        . "<strong><a href='{$ticketViewUrl}' target='_blank'>"
                                        . $ticket->getTicketId()
                                        . "</a></strong>"
                                        . " state changed to <strong>Service Complete</strong>"
                                    ),
                                    "description" => [
                                        $response->getMessage(),
                                    ],
                                    "type" => "ticket"
                                ];
                                break;

                            case Ticket::STATUS_CLOSED_INVOICED:
                                $timeLine[] = [
                                    "date" => $response->getCreatedDateTime()->format("Y-m-d"),
                                    "title" => (
                                        "Ticket "
                                        . "<strong><a href='{$ticketViewUrl}' target='_blank'>"
                                        . $ticket->getTicketId()
                                        . "</a></strong>"
                                        . " was <strong>Closed (Invoiced)</strong>"
                                    ),
                                    "description" => [
                                        $response->getMessage(),
                                    ],
                                    "type" => "ticket"
                                ];
                                break;

                            case Ticket::STATUS_CLOSED_ZERO_INVOICE:
                                $timeLine[] = [
                                    "date" => $response->getCreatedDateTime()->format("Y-m-d"),
                                    "title" => (
                                        "Ticket "
                                        . "<strong><a href='{$ticketViewUrl}' target='_blank'>"
                                        . $ticket->getTicketId()
                                        . "</a></strong>"
                                        . " was <strong>Closed (No Invoice)</strong>"
                                    ),
                                    "description" => [
                                        $response->getMessage(),
                                    ],
                                    "type" => "ticket"
                                ];
                                break;
                        }
                    }
                }
            },
            $em->getRepository(Ticket::class)->findBy([ "machine" => $machine ])
        );

        // Service Contracts
        array_map(
            function (MachineServiceContract $contract) use (&$timeLine, $machine){
                $timeLine[] = [
                    "date" => $contract->getCreatedDateTime()->format("Y-m-d"),
                    "title" => (
                        "<strong>New Contract "
                        . "<a href='"
                        . $this->generateUrl(
                            "serviceContractViewPage",
                            [ "id" => $contract->getId() ],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                        . "' target='_blank'>{$contract->getContractNumber()}</a>"
                        . "</strong>"
                    ),
                    "description" => [
                        "<strong>Created by</strong>: " . (
                        $contract->getCreator() ? $contract->getCreator()->getFullName() : ""
                        ),
                        "<strong>Type</strong>: " . $contract->getContractType(),
                    ],
                    "type" => "contract"
                ];
            },
            $em->getRepository(MachineServiceContract::class)->findBy([ "machine" => $machine ])
        );

        usort(
            $timeLine,
            function ($item1, $item2) {
                return strtotime($item2['date']) - strtotime($item1['date']);
            }
        );

        return $this->json($timeLine);
    }



    /**
     * @param Request $request
     * @param MachineAccessProvider $accessProvider
     * @Route(
     *     "/json/machine-list",
     *     name="jsonGetMachinesList"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jsonGetMachinesList(Request $request, MachineAccessProvider $accessProvider)
    {
        $accessLevel = $accessProvider->requiresAccessToTheSection();

        $customerId = (int)$request->get("customerId", 0);


        /* @var MachineRepository $machinesRepo*/
        $machinesRepo = $this->getDoctrine()->getRepository(Machine::class);

        if ($accessLevel >= MachineAccessProvider::ACL_FULL) {
            $machineList = $machinesRepo->findAll();
        } else {
            $machineList = $machinesRepo->loadUserAssocBundledMachines($accessProvider->getUser());
        }

        // If we have to show specific customer related machines, then we filter the list
        if ($customerId) {
            $machineList = array_values(array_filter(
                $machineList,
                function (Machine $machine) use ($customerId) {
                    if (! $machine->getCurrentHolder()) {
                        return false;
                    }

                    return ($machine->getCurrentHolder()->getId() === $customerId);
                }
            ));
        }

        return $this->json(
            array_map(
                function (Machine $machine) use ($accessLevel){
                    $infoArray = $machine->toInfoArray();

                    $infoArray['actions'] = [
                        "View" => $this->generateUrl(
                            "machineViewPage",
                            [ "machineId" => $machine->getId() ],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ];


                    if ($accessLevel >= MachineAccessProvider::ACL_SUBSIDIARY_FULL) {
                        $infoArray['actions']["Edit"] = $this->generateUrl(
                            "machineEditPage",
                            [ "machineId" => $machine->getId() ],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    }

                    return $infoArray;
                },
                $machineList
            )
        );
    }


    /**
     * @param int $machineId
     * @param MachineAccessProvider $accessProvider
     * @Route(
     *     "/ajax/get-machine/{machineId}",
     *     name="ajaxGetMachineByIdAction",
     *     requirements={"machineId"="\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxGetMachineByIdAction(int $machineId, MachineAccessProvider $accessProvider)
    {
        $em = $this->getDoctrine()->getManager();

        /* @var Machine $machine */
        $machine = $em->getRepository(Machine::class)->find($machineId);

        if (! $machine) {
            return $this->json([
                "status" => "error",
                "message" => "Machine not found",
                "machineId" => $machineId,
            ], 404);
        }

        if (! $accessProvider->isReadable($machine)) {
            return $this->json([
                "status" => "error",
                "message" => "Access Denied",
                "machineId" => $machineId,
            ], 403);
        }

        $machineInfo = $machine->toInfoArray();

        // Load machine tickets
        $machineInfo['ticketList'] = array_map(
            function (Ticket $ticket) {
                return $ticket->toArray();
            },
            $machine->getTicketList()->toArray()
        );


        $machineInfo['currentHolderContactList'] = [];
        if ($machine->getCurrentHolder()) {
            foreach ($machine->getCurrentHolder()->getContactsList() as $contact) {
                if (! $contact->isEnabled()) continue;
                $machineInfo['currentHolderContactList'][] = $contact->toContactInfoArray();
            }
        }

        // Add contracts info
        $machineInfo['activeContracts'] = array_map(
            function (MachineServiceContract $contract) {
                return $contract->toInfoArray();
            },
            $em->getRepository(MachineServiceContract::class)->findBy([
                "machine" => $machine,
                "status" => MachineServiceContract::STATUS_ACTIVE
            ])
        );

        return $this->json($machineInfo);
    }



}