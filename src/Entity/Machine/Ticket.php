<?php

namespace App\Entity\Machine;

use App\Entity\Customer\Customer;
use App\Entity\Subsidiary;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Machine\TicketRepository")
 * @ORM\Table(name="ticket",indexes={@ORM\Index(name="token", columns={"token"})})
 * @UniqueEntity("ticketId")
 */
class Ticket
{
    // Statuses
    const STATUS_OPEN = 10;
    const STATUS_ASSIGNED = 30;
    const STATUS_IN_PROGRESS = 50;
    const STATUS_SERVICE_COMPLETE = 80;
    const STATUS_TO_INVOICE = 85;
    const STATUS_CLOSED_ZERO_INVOICE = 90;
    const STATUS_CLOSED_INVOICED = 100;

    // Service types
    const SERVICE_TYPE_OTHER = 1;
    const SERVICE_TYPE_MAINTENANCE = 2;
    const SERVICE_TYPE_INSTALLATION = 3;
    const SERVICE_TYPE_REPAIR = 4;

    // Priorities
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 5;
    const PRIORITY_HIGH = 10;

    const TASK_CHECK_TRAVEL = 'Travel';
    const TASK_CHECK_MACHINE_INFO = 'Machine Info';
    const TASK_CHECK_INSTALLATION_CHECKLIST = 'Installation Checklist';
    const TASK_CHECK_SERVICE_CHECKLIST = 'Service Checklist';
    const TASK_CHECK_FAULT = 'Fault';
    const TASK_CHECK_OTHER = 'Other';

    /**
     * Statuses and their descriptions
     * @var array
     */
    public static $statuses = [
        self::STATUS_OPEN => "Open",
        self::STATUS_ASSIGNED => "Assigned",
        self::STATUS_IN_PROGRESS => "In Progress",
        self::STATUS_SERVICE_COMPLETE => "Service Complete",
        self::STATUS_TO_INVOICE => "To Invoice",
        self::STATUS_CLOSED_ZERO_INVOICE => "Closed zero invoice",
        self::STATUS_CLOSED_INVOICED => "Closed Invoiced"

    ];

    /**
     * Service types and their descriptions
     * @var array
     */
    public static $serviceTypes = [
        self::SERVICE_TYPE_INSTALLATION => "Installation",
        self::SERVICE_TYPE_MAINTENANCE => "Maintenance & Calibration",
        self::SERVICE_TYPE_REPAIR => "Repair & Calibration",
        self::SERVICE_TYPE_OTHER => "Other",
    ];

    /**
     * @var array
     */
    public static $priorities = [
        self::PRIORITY_LOW => "Low",
        self::PRIORITY_NORMAL => "Normal",
        self::PRIORITY_HIGH => "High",
    ];

    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id = 0;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     */
    private $token = "";

    /**
     * @var Subsidiary
     * @ORM\ManyToOne(targetEntity="\App\Entity\Subsidiary")
     * @ORM\JoinColumn(name="subsidiary_id", referencedColumnName="id")
     */
    private $subsidiary;

    /**
     * @var Machine|null
     * @ORM\ManyToOne(targetEntity="Machine", inversedBy="ticketList")
     * @ORM\JoinColumn(name="machine_id", referencedColumnName="id")
     */
    private $machine;

    /**
     * @var Customer|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\Customer\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="customer_contact_id", referencedColumnName="id")
     */
    private $customerContact;

    /**
     * @var MachineServiceContract|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\Machine\MachineServiceContract")
     * @ORM\JoinColumn(name="service_contract_id", referencedColumnName="id")
     */
    private $serviceContract;

    /**
     * @var Ticket|null
     * @ORM\ManyToOne(targetEntity="Ticket")
     * @ORM\JoinColumn(name="previous_ticket_id", referencedColumnName="id")
     */
    private $previousTicket;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $machineLocationCountry = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $machineLocationState = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $machineLocationCity = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $machineLocationAddress = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $machineLocationZip = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $coordinatesJson = "";

    /**
     * @var string
     * @ORM\Column(type="bigint")
     */
    private $machineDistance = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $ticketId =  "";

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="assigned_user_id", referencedColumnName="id")
     */
    private $assignedTo;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $preferredDateTime;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $dueDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdDateTime;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="creator_user_id", referencedColumnName="id")
     */
    private $creator;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $serviceType =  self::SERVICE_TYPE_INSTALLATION;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $repairReason =  "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description =  "";

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_OPEN;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $priority = self::PRIORITY_NORMAL;

    /**
     * @var ArrayCollection|TicketResponse[]
     * @ORM\OneToMany(targetEntity="TicketResponse", mappedBy="ticket")
     * @ORM\OrderBy({"createdDateTime" = "DESC"})
     */
    private $responseList;

    /**
     * @param int $ticketId
     * @return string
     */
    public static function getFilesLocationByTicketId(int $ticketId): string
    {
        $idString = sprintf('%03d', $ticketId);

        return (
            "private/upload/ticket_files/" .
            substr($idString, 0, 1) . "/" .
            substr($idString, 0, 2)  . "/" .
            substr($idString, 0, 3) . "/"
        );
    }

    /**
     * Ticket constructor.
     */
    public function __construct()
    {
        $this->preferredDateTime = new \DateTime();
        $this->createdDateTime = new \DateTime();
        $this->responseList = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Ticket
     */
    public function setId(int $id): Ticket
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return Ticket
     */
    public function setToken(string $token): Ticket
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return Ticket
     */
    public function generateToken(): Ticket
    {
        $this->token = md5(uniqid("_ticket_token", true));
        return $this;
    }

    /**
     * @return Subsidiary
     */
    public function getSubsidiary(): Subsidiary
    {
        return $this->subsidiary;
    }

    /**
     * @param Subsidiary $subsidiary
     * @return Ticket
     */
    public function setSubsidiary(Subsidiary $subsidiary): Ticket
    {
        $this->subsidiary = $subsidiary;
        return $this;
    }

    /**
     * @return Machine|null
     */
    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    /**
     * @param Machine|null $machine
     * @return Ticket
     */
    public function setMachine(?Machine $machine): Ticket
    {
        $this->machine = $machine;
        return $this;
    }

    /**
     * @return Customer|null
     */
    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     * @return Ticket
     */
    public function setCustomer(?Customer $customer): Ticket
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getCustomerContact(): ?User
    {
        return $this->customerContact;
    }

    /**
     * @param User|null $customerContact
     * @return Ticket
     */
    public function setCustomerContact(?User $customerContact): Ticket
    {
        $this->customerContact = $customerContact;
        return $this;
    }

    /**
     * @return MachineServiceContract|null
     */
    public function getServiceContract(): ?MachineServiceContract
    {
        return $this->serviceContract;
    }

    /**
     * @param MachineServiceContract|null $serviceContract
     * @return Ticket
     */
    public function setServiceContract(?MachineServiceContract $serviceContract): Ticket
    {
        $this->serviceContract = $serviceContract;
        return $this;
    }

    /**
     * @return Ticket|null
     */
    public function getPreviousTicket(): ?Ticket
    {
        return $this->previousTicket;
    }

    /**
     * @param Ticket|null $previousTicket
     * @return Ticket
     */
    public function setPreviousTicket(?Ticket $previousTicket): Ticket
    {
        $this->previousTicket = $previousTicket;
        return $this;
    }

    /**
     * @return string
     */
    public function getMachineLocationCountry(): string
    {
        return $this->machineLocationCountry;
    }

    /**
     * @param string $machineLocationCountry
     * @return Ticket
     */
    public function setMachineLocationCountry(string $machineLocationCountry): Ticket
    {
        $this->machineLocationCountry = $machineLocationCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getMachineLocationState(): string
    {
        return $this->machineLocationState;
    }

    /**
     * @param string $machineLocationState
     * @return Ticket
     */
    public function setMachineLocationState(string $machineLocationState): Ticket
    {
        $this->machineLocationState = $machineLocationState;
        return $this;
    }

    /**
     * @return string
     */
    public function getMachineLocationCity(): string
    {
        return $this->machineLocationCity;
    }

    /**
     * @param string $machineLocationCity
     * @return Ticket
     */
    public function setMachineLocationCity(string $machineLocationCity): Ticket
    {
        $this->machineLocationCity = $machineLocationCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getMachineLocationAddress(): string
    {
        return $this->machineLocationAddress;
    }

    /**
     * @param string $machineLocationAddress
     * @return Ticket
     */
    public function setMachineLocationAddress(string $machineLocationAddress): Ticket
    {
        $this->machineLocationAddress = $machineLocationAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getMachineLocationZip(): string
    {
        return $this->machineLocationZip;
    }

    /**
     * @param string $machineLocationZip
     * @return Ticket
     */
    public function setMachineLocationZip(string $machineLocationZip): Ticket
    {
        $this->machineLocationZip = $machineLocationZip;
        return $this;
    }

    /**
     * @return string
     */
    public function getCoordinatesJson(): string
    {
        return $this->coordinatesJson;
    }

    /**
     * @param string $coordinatesJson
     * @return Ticket
     */
    public function setCoordinatesJson(string $coordinatesJson): Ticket
    {
        $this->coordinatesJson = $coordinatesJson;
        return $this;
    }

    /**
     * @return float
     */
    public function getCoordinatesLongitude(): float
    {
        $coordinates = @json_decode($this->getCoordinatesJson(), true) ?: ["lat" => 0, "lng" => 0];
        return (float)($coordinates['lng'] ?? 0);
    }

    /**
     * @param float $value
     * @return Ticket
     */
    public function setCoordinatesLongitude(float $value): Ticket
    {
        $coordinates = @json_decode($this->getCoordinatesJson(), true) ?: ["lat" => 0, "lng" => 0];
        $coordinates['lng'] = $value;

        $this->setCoordinatesJson(json_encode($coordinates));
        return $this;
    }

    /**
     * @return float
     */
    public function getCoordinatesLatitude(): float
    {
        $coordinates = @json_decode($this->getCoordinatesJson(), true) ?: ["lat" => 0, "lng" => 0];
        return (float)($coordinates['lat'] ?? 0);
    }

    /**
     * @param float $value
     * @return Ticket
     */
    public function setCoordinatesLatitude(float $value): Ticket
    {
        $coordinates = @json_decode($this->getCoordinatesJson(), true) ?: ["lat" => 0, "lng" => 0];
        $coordinates['lat'] = $value;

        $this->setCoordinatesJson(json_encode($coordinates));
        return $this;
    }

    /**
     * @return string
     */
    public function getFullAddress(): string
    {
        return implode(
            ", ",
            array_filter(
                [
                    $this->getMachineLocationAddress(),
                    $this->getMachineLocationCity(),
                    $this->getMachineLocationState(),
                    $this->getMachineLocationZip(),
                    $this->getMachineLocationCountry()
                ],
                function ($itemString) {
                    return !! trim($itemString);
                }
            )
        );
    }

    /**
     * @return string
     */
    public function getMachineDistance(): string
    {
        return $this->machineDistance;
    }

    /**
     * @param string $machineDistance
     * @return Ticket
     */
    public function setMachineDistance(string $machineDistance): Ticket
    {
        $this->machineDistance = $machineDistance;
        return $this;
    }

    /**
     * @return string
     */
    public function getTicketId(): string
    {
        return $this->ticketId;
    }

    /**
     * @param string $ticketId
     * @return Ticket
     */
    public function setTicketId(string $ticketId): Ticket
    {
        $this->ticketId = $ticketId;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    /**
     * @param User|null $assignedTo
     * @return Ticket
     */
    public function setAssignedTo(?User $assignedTo): Ticket
    {
        $this->assignedTo = $assignedTo;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getPreferredDateTime(): ?\DateTime
    {
        return $this->preferredDateTime;
    }

    /**
     * @param \DateTime|null $preferredDateTime
     * @return Ticket
     */
    public function setPreferredDateTime(?\DateTime $preferredDateTime): Ticket
    {
        $this->preferredDateTime = $preferredDateTime;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    /**
     * @return string
     */
    public function getDueDateString(): string
    {
        return $this->dueDate ? $this->dueDate->format("d/m/Y") : "";
    }

    /**
     * @param \DateTime|null $dueDate
     * @return Ticket
     */
    public function setDueDate(?\DateTime $dueDate): Ticket
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    /**
     * @param string|null $dueDateString
     * @return Ticket
     */
    public function setDueDateString(?string $dueDateString): Ticket
    {
        if ($dueDateString) {
            $dueDate = \DateTime::createFromFormat("Y-m-d", $dueDateString);
            $this->dueDate = $dueDate ?: null;
        } else {
            $this->dueDate = null;
        }
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDateTime(): \DateTime
    {
        return $this->createdDateTime;
    }

    /**
     * @param \DateTime $createdDateTime
     * @return Ticket
     */
    public function setCreatedDateTime(\DateTime $createdDateTime): Ticket
    {
        $this->createdDateTime = $createdDateTime;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getCreator(): ?User
    {
        return $this->creator;
    }

    /**
     * @param User|null $creator
     * @return Ticket
     */
    public function setCreator(?User $creator): Ticket
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return int
     */
    public function getServiceType(): int
    {
        return $this->serviceType;
    }

    /**
     * @return string
     */
    public function getServiceTypeString(): string
    {
        return self::$serviceTypes[$this->serviceType] ?? "n/a";
    }

    /**
     * @param int $serviceType
     * @return Ticket
     */
    public function setServiceType(int $serviceType): Ticket
    {
        $this->serviceType = $serviceType;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepairReason(): string
    {
        return $this->repairReason;
    }

    /**
     * @param string $repairReason
     * @return Ticket
     */
    public function setRepairReason(string $repairReason): Ticket
    {
        $this->repairReason = $repairReason;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Ticket
     */
    public function setDescription(string $description): Ticket
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getStatusString(): string
    {
        return self::$statuses[$this->status] ?? "n/a";
    }

    /**
     * @param int $status
     * @return Ticket
     */
    public function setStatus(int $status): Ticket
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getPriorityString(): string
    {
        return self::$priorities[$this->priority] ?? "n/a";
    }

    /**
     * @param int $priority
     * @return Ticket
     */
    public function setPriority(int $priority): Ticket
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return TicketResponse[]|ArrayCollection
     */
    public function getResponseList()
    {
        return $this->responseList;
    }

    /**
     * @param TicketResponse[]|ArrayCollection $responseList
     * @return Ticket
     */
    public function setResponseList($responseList)
    {
        $this->responseList = $responseList;
        return $this;
    }

    /**
     * @param int $responseType
     * @return TicketResponse|null
     */
    public function getLastResponseByType($responseType): ?TicketResponse
    {
        /* @var TicketResponse[] $responseList */
        $responseList = $this->getResponseList()->toArray();
        usort(
            $responseList,
            function (TicketResponse $rA, TicketResponse $rB) {
                return $rB->getCreatedDateTime()->getTimestamp() - $rA->getCreatedDateTime()->getTimestamp();
            }
        );

        foreach ($responseList as $response) {
            if ($response->getResponseType() == $responseType) {
                return $response;
            }
        }

        return null;
    }


    public function getTaskChecklist()
    {
        $checksAvailable = [];

        switch ($this->getServiceType()) {
            case self::SERVICE_TYPE_INSTALLATION:
                $checksAvailable = [
                    self::TASK_CHECK_TRAVEL,
                    self::TASK_CHECK_MACHINE_INFO,
                    self::TASK_CHECK_INSTALLATION_CHECKLIST,
                ];
                break;

            case self::SERVICE_TYPE_MAINTENANCE:
                $checksAvailable = [
                    self::TASK_CHECK_TRAVEL,
                    self::TASK_CHECK_MACHINE_INFO,
                    self::TASK_CHECK_SERVICE_CHECKLIST,
                ];
                break;

            case self::SERVICE_TYPE_REPAIR:
                $checksAvailable = [
                    self::TASK_CHECK_TRAVEL,
                    self::TASK_CHECK_MACHINE_INFO,
                    self::TASK_CHECK_FAULT,
                ];
                break;

            case self::SERVICE_TYPE_OTHER:
                $checksAvailable = [
                    self::TASK_CHECK_TRAVEL,
                    self::TASK_CHECK_MACHINE_INFO,
                    self::TASK_CHECK_OTHER,
                ];
                break;

        }

        $result = [];

        foreach ($checksAvailable as $checkType) {
            switch ($checkType) {
                case self::TASK_CHECK_INSTALLATION_CHECKLIST:
                    $response = $this->getLastResponseByType(
                        TicketResponse::RESPONSE_TYPE_INSTALLATION_CHECKLIST
                    );

                    if ($response) {
                        $checkedOptions = array_intersect(
                            array_keys(TicketResponse::$installationChecklist),
                            $response->getInstallationChecklist()
                        );

                        $result[$checkType] = count($checkedOptions) == count(TicketResponse::$installationChecklist);
                    } else {
                        $result[$checkType] = false;
                    }

                    break;

                case self::TASK_CHECK_SERVICE_CHECKLIST:
                    $response = $this->getLastResponseByType(
                        TicketResponse::RESPONSE_TYPE_SERVICE_CHECKLIST
                    );

                    if ($response) {
                        $checkedOptions = array_intersect(
                            array_keys(TicketResponse::$serviceChecklist),
                            $response->getServiceChecklist()
                        );

                        $result[$checkType] = count($checkedOptions) == count(TicketResponse::$serviceChecklist);
                    } else {
                        $result[$checkType] = false;
                    }


                    break;

                case self::TASK_CHECK_TRAVEL:
                    $result[$checkType] = !! $this->getLastResponseByType(
                        TicketResponse::RESPONSE_TYPE_TRAVEL
                    );
                    break;

                case self::TASK_CHECK_MACHINE_INFO:
                    $result[$checkType] = !! $this->getLastResponseByType(
                        TicketResponse::RESPONSE_TYPE_MACHINE_INFO
                    );
                    break;

                case self::TASK_CHECK_FAULT:
                    $result[$checkType] = !! $this->getLastResponseByType(
                        TicketResponse::RESPONSE_TYPE_FAULT
                    );
                    break;

                case self::TASK_CHECK_OTHER:
                    $result[$checkType] = !! $this->getLastResponseByType(
                        TicketResponse::RESPONSE_TYPE_OTHER
                    );
                    break;
            }

        }
        return $result;
    }

    /**
     * @return array
     * @deprecated
     */
    public function toArray()
    {
        return [
            "id" => $this->getId(),
            "subsidiaryId" => $this->getSubsidiary() ? $this->getSubsidiary()->getId() : 0,
            "token" => $this->getToken(),
            "machineId" => $this->machine ? $this->machine->getId() : 0,
            "customerId" => $this->customer ? $this->customer->getId() : 0,
            "customerContactId" => $this->customerContact ? $this->customerContact->getId() : 0,
            "serviceContractId" => $this->serviceContract ? $this->serviceContract->getId() : 0,
            "previousTicketId" => $this->previousTicket ? $this->previousTicket->getId() : 0,
            "machineLocationCountry" => $this->machineLocationCountry,
            "machineLocationState" => $this->machineLocationState,
            "machineLocationCity" => $this->machineLocationCity,
            "machineLocationAddress" => $this->machineLocationAddress,
            "machineLocationZip" => $this->machineLocationZip,
            "coordinatesLatitude" => $this->getCoordinatesLatitude(),
            "coordinatesLongitude" => $this->getCoordinatesLongitude(),
            "machineDistance" => $this->machineDistance,
            "ticketId" => $this->ticketId,
            "assignedToId" => $this->assignedTo ? $this->assignedTo->getId() : 0,
            "assignedToFullName" => $this->assignedTo ? $this->assignedTo->getFullName() : "",
            "preferredDateTime" => (
                $this->preferredDateTime
                    ? $this->preferredDateTime->format("Y-m-d H:i:s")
                    : ""
            ),
            "dueDate" => (
                $this->dueDate
                    ? $this->dueDate->format("Y-m-d")
                    : ""
            ),
            "createdDateTime" => (
                $this->createdDateTime
                    ? $this->createdDateTime->format("Y-m-d H:i:s")
                    : ""
            ),
            "creatorId" => $this->creator ? $this->creator->getId() : 0,
            "serviceType" => $this->serviceType,
            "serviceTypeString" => $this->getServiceTypeString(),
            "repairReason" => $this->getRepairReason(),
            "description" => $this->description,
            "status" => $this->status,
            "statusString" => $this->getStatusString(),
            "priority" => $this->priority,
        ];
    }

    /**
     * @return array
     */
    public function toCorrectArray()
    {
        return [
            "id" => $this->getId(),
            "ticketId" => $this->getTicketId(),
            "token" => $this->getToken(),

            "subsidiaryId" => $this->getSubsidiary() ? $this->getSubsidiary()->getId() : 0,
            "subsidiaryName" => $this->getSubsidiary() ? $this->getSubsidiary()->getName() : "",
            "subsidiaryShortCode" => $this->getSubsidiary() ? $this->getSubsidiary()->getShortCode() : "",

            "machineId" => $this->getMachine() ? $this->getMachine()->getId() : 0,
            "machineSerialId" => $this->getMachine() ? $this->getMachine()->getSerialId() : "",

            "assignedToId" => $this->getAssignedTo() ? $this->getAssignedTo()->getId() : 0,
            "assignedToFullName" => $this->getAssignedTo() ? $this->getAssignedTo()->getFullName() : "",

            "creatorId" => $this->getCreator() ? $this->getCreator()->getId() : 0,
            "creatorFullName" => $this->getCreator() ? $this->getCreator()->getFullName() : "",

            "preferredDateTime" =>
                $this->getPreferredDateTime()
                    ? $this->getPreferredDateTime()->format("Y-m-d H:i:s") :
                    "",
            "createdDateTime" =>
                $this->getCreatedDateTime()
                    ? $this->getCreatedDateTime()->format("Y-m-d H:i:s")
                    : "",

            "serviceType" => $this->getServiceType(),
            "serviceTypeString" => $this->getServiceTypeString(),

            "description" => $this->getDescription(),

            "status" => $this->getStatus(),
            "statusString" => $this->getStatusString(),


            "priority" => $this->getPriority(),
            "priorityString" => $this->getPriorityString(),

            "customerId" => $this->getCustomer() ? $this->getCustomer()->getId() : 0,
            "customerCompanyName" => $this->getCustomer() ? $this->getCustomer()->getCompanyName() : "",

            "customerContactId" => $this->getCustomerContact() ? $this->getCustomerContact()->getId() : 0,
            "customerContactFullName" =>
                $this->getCustomerContact()
                    ? $this->getCustomerContact()->getFullName()
                    : "",

            "serviceContractId" => $this->getServiceContract() ? $this->getServiceContract()->getId() : 0,
            "serviceContractNumber" =>
                $this->getServiceContract()
                    ? $this->getServiceContract()->getContractNumber()
                    : "",
            "serviceContractStartDate" =>
                $this->getServiceContract() ? $this->getServiceContract()->getStartDate()
                    ? $this->getServiceContract()->getStartDate()->format("Y-m-d")
                    : "" : "",

            "serviceContractEndDate" =>
                $this->getServiceContract() ? $this->getServiceContract()->getEndDate()
                    ? $this->getServiceContract()->getEndDate()->format("Y-m-d")
                    : "" : "",


            "machineLocationCountry" => $this->getMachineLocationCountry(),
            "machineLocationState" => $this->getMachineLocationState(),
            "machineLocationCity" => $this->getMachineLocationCity(),
            "machineLocationAddress" => $this->getMachineLocationAddress(),
            "machineLocationZip" => $this->getMachineLocationZip(),
            "machineDistance" => $this->getMachineDistance(),
            "coordinatesLatitude" => $this->getCoordinatesLatitude(),
            "coordinatesLongitude" => $this->getCoordinatesLongitude(),

            "previousTicketId" => $this->getPreviousTicket() ? $this->getPreviousTicket()->getId() : 0,
            "previousTicketIdString" =>
                $this->getPreviousTicket()
                    ? $this->getPreviousTicket()->getTicketId()
                    : "",
            "previousTicketToken" =>
                $this->getPreviousTicket()
                    ? $this->getPreviousTicket()->getToken()
                    : "",

            "previousTicketServiceDate" =>
                $this->getPreviousTicket() ? $this->getPreviousTicket()->getPreferredDateTime()
                    ? $this->getPreviousTicket()->getPreferredDateTime()->format("Y-m-d")
                    : "" : "",

            "dueDate" =>
                $this->getDueDate()
                    ? $this->getDueDate()->format("Y-m-d")
                    : "",

            "repairReason" => $this->getRepairReason(),
        ];
    }
}
