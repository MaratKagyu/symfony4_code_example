<?php

namespace App\Entity\Machine;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\HttpException;
use \Symfony\Component\Security\Core\Exception\InvalidArgumentException;


/**
 * @ORM\Entity()
 */
class TicketResponse
{

    const RESPONSE_TYPE_UPDATE = 0;
    const RESPONSE_TYPE_TRAVEL = 1;
    const RESPONSE_TYPE_MACHINE_INFO = 2;
    const RESPONSE_TYPE_SERVICE_CHECKLIST = 3;
    const RESPONSE_TYPE_UPLOAD_DOC = 4;
    const RESPONSE_TYPE_FAULT = 5;
    const RESPONSE_TYPE_INSTALLATION_CHECKLIST = 6;
    const RESPONSE_TYPE_PARTS = 7;
    const RESPONSE_TYPE_COMPLETE = 8;
    const RESPONSE_TYPE_OTHER = 100;


    public static $responses = [
        self::RESPONSE_TYPE_UPDATE => "Update",
        self::RESPONSE_TYPE_TRAVEL => "Travel",
        self::RESPONSE_TYPE_MACHINE_INFO => "Machine Info",
        self::RESPONSE_TYPE_PARTS => "Parts",
        self::RESPONSE_TYPE_SERVICE_CHECKLIST => "Service Checklist",

        self::RESPONSE_TYPE_UPLOAD_DOC => "Add attachments",
        self::RESPONSE_TYPE_FAULT => "Fault",

        self::RESPONSE_TYPE_INSTALLATION_CHECKLIST => "Installation checklist",
        self::RESPONSE_TYPE_PARTS  => "Parts",
        self::RESPONSE_TYPE_COMPLETE => "Complete",
        self::RESPONSE_TYPE_OTHER => "Other",
    ];


    public static $installationChecklist = [
        "Check Carton for Damage During Delivery",
        "Check Site Requirements: Gas Bottle Present and Correct",
        "Check Asscessories List for Completeness",
        "Roll Generator Right Side Up and Fix Trolley Top Screws",
        "Check and/or fit Correct Mains Plug",
        "Check Mains Voltage for Suitability & Rating  (VAC, Amps)",
        "Set Transfer Tappings if Required",
        "Check and reconnect Rechargeable Battery if necessary",
        "Check and fit Gas Regulator and Hose to Gas Bottle",
        "Plug in and attach Argon Hose to Unit",
        "Check presence of In Line Low Pressure Regulator",
        "Switch On Gas at Generator",
        "Fit Ash Tray",
        "Complete Function Check Carried Out",
        "Ensure that User Manual is handed to Customer Rep",
        "Complete Warranty Card",
    ];

    public static $serviceChecklist = [
        "Remove cover",
        "Check unit for signs of non-compliance to Cyclomedica Specification",
        "Check Power Cable for Damage",
        "Check Calibration of Argon Regulator",
        "Remove Drawer and Clean",
        "Tighten/Adjust all Drawer Connections",
        "Calibrate Pedestal Spring",
        "Clean Inside Lower Chamber",
        "Clean Inside Expansion Chamber",
        "Clean Inside Chamber Cap",
        "Check Interlock Lever Function",
        "Check Boat Crush Function",
        "Check Optical",
        "Check Seals for Degradation",
        "Refit Drawer and Lubricate Slider",
        "Check Unit Calibration",
        "Check Unit Function",
        "Remote Button Operation (TP Only)",
        "Check Easy Breather Function (TP Only)",
        "Check Simmer Box Function (TP Only)",
        "Special Function Tests (TP Only)",
        "Yield Test (only if required)",
        "Check Trolley Assembly (and Wheel Tracking of TP Machines Only)",
        "Reassemble",
        "Report Completion to Department Head",
        "Complete Service Documents",
        "Staff Retraining (if required)",
    ];


    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="bigint")
     */
    private $id = 0;

    /**
     * @var Ticket
     * @ORM\ManyToOne(targetEntity="Ticket", inversedBy="responseList")
     * @ORM\JoinColumn(name="ticket_id", referencedColumnName="id")
     */
    private $ticket;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdDateTime;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="creator_user_id", referencedColumnName="id")
     */
    private $creator;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="assigned_user_id", referencedColumnName="id")
     */
    private $assignedTo;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="customer_contact_user_id", referencedColumnName="id")
     */
    private $customerContact;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $message =  "";

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $responseType = self::RESPONSE_TYPE_UPDATE;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $dataJson =  "{}";

    /**
     * TicketResponse constructor.
     */
    public function __construct()
    {
        $this->createdDateTime = new \DateTime();
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
     * @return TicketResponse
     */
    public function setId(int $id): TicketResponse
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Ticket
     */
    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     * @return TicketResponse
     */
    public function setTicket(Ticket $ticket): TicketResponse
    {
        $this->ticket = $ticket;
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
     * @return TicketResponse
     */
    public function setCreatedDateTime(\DateTime $createdDateTime): TicketResponse
    {
        $this->createdDateTime = $createdDateTime;
        return $this;
    }

    /**
     * @return User
     */
    public function getCreator(): User
    {
        return $this->creator;
    }

    /**
     * @param User $creator
     * @return TicketResponse
     */
    public function setCreator(User $creator): TicketResponse
    {
        $this->creator = $creator;
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
     * @return TicketResponse
     */
    public function setAssignedTo(?User $assignedTo): TicketResponse
    {
        $this->assignedTo = $assignedTo;
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
     * @return TicketResponse
     */
    public function setCustomerContact(?User $customerContact): TicketResponse
    {
        $this->customerContact = $customerContact;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return TicketResponse
     */
    public function setMessage(?string $message): TicketResponse
    {
        $this->message = $message ?: "";
        return $this;
    }

    /**
     * @return int
     */
    public function getResponseType(): int
    {
        return $this->responseType;
    }

    /**
     * @return string
     */
    public function getResponseTypeString(): string
    {
        return self::$responses[$this->getResponseType()] ?? "n/a";
    }

    /**
     * @param int $responseType
     * @return TicketResponse
     */
    public function setResponseType(int $responseType): TicketResponse
    {
        $this->responseType = $responseType;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataJson(): string
    {
        return $this->dataJson;
    }

    /**
     * @param string $dataJson
     * @return TicketResponse
     */
    public function setDataJson(string $dataJson): TicketResponse
    {
        $this->dataJson = $dataJson;
        return $this;
    }

    /**
     * @return array
     */
    public function getDataArray(): array
    {
        $dataArray = @json_decode($this->dataJson, true);
        return is_array($dataArray) ? $dataArray : [];
    }

    /**
     * @param array $dataArray
     * @return TicketResponse
     */
    public function setDataArray(array $dataArray): TicketResponse
    {
        $this->dataJson = json_encode($dataArray);
        return $this;
    }

    /**
     * @param string[] $path - example: ["level1", "level2"] will be like $data["level1"]["level2"]
     * @param string $varType (string|int|datetime|date)
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getDataPropertyValue(array $path, string $varType, $defaultValue = null)
    {
        $value = $this->getDataArray();


        while (count($path)) {
            $pathItem = array_shift($path);

            if (isset($value[$pathItem])) {
                $value = $value[$pathItem];
            } else {
                $value = null;
                break;
            }
        }

        if (is_null($value)) return $defaultValue;

        switch ($varType) {
            case 'int':
            case 'integer':
            case 'number':
                return (int)$value;

            case 'float':
            case 'double':
                return (float)$value;

            case 'string':
            case 'text':
                return $value;


            case 'datetime':
                $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $value);
                return $dateTime ?: $defaultValue;

            case 'date':
                $dateTime = \DateTime::createFromFormat("Y-m-d", $value);
                return $dateTime ?: $defaultValue;

            case 'array':
                return is_array($value) ? $value : [];

            default:
                throw new HttpException(500, "Unrecognized varType = '{$varType}'");
        }
    }

    /**
     * @param string[] $path - example: ["level1", "level2"] will be like $data["level1"]["level2"]
     * @param string $varType (string|int|datetime|date)
     * @param mixed $value
     * @return static
     */
    public function setDataPropertyValue(array $path, $varType, $value)
    {
        $sourceArray = $this->getDataArray();
        $currentLevel = &$sourceArray;

        while (count($path)) {
            $pathItem = array_shift($path);

            if (count($path) > 0) {
                if (! isset($currentLevel[$pathItem])) {
                    $currentLevel[$pathItem] = [];
                }

                $currentLevel = &$currentLevel[$pathItem];
            } else {
                switch ($varType) {
                    case 'int':
                    case 'integer':
                    case 'number':
                        $currentLevel[$pathItem] = (int)$value;
                        break;

                    case 'float':
                    case 'double':
                        $currentLevel[$pathItem] = (float)$value;
                        break;

                    case 'string':
                    case 'text':
                        $currentLevel[$pathItem] = $value;
                        break;


                    case 'datetime':
                        if (! $value) {
                            $currentLevel[$pathItem] = null;
                        } else {
                            /* @var \DateTime $value */
                            $currentLevel[$pathItem] = $value->format("Y-m-d H:i:s");
                        }
                        break;

                    case 'date':
                        if (! $value) {
                            $currentLevel[$pathItem] = null;
                        } else {
                            /* @var \DateTime $value */
                            $currentLevel[$pathItem] = $value->format("Y-m-d");
                        }
                        break;

                    case 'array':
                        $currentLevel[$pathItem] = is_array($value) ? $value : [];
                        break;

                    default:
                        throw new HttpException(500, "Unrecognized varType = '{$varType}'");
                }
            }

        }

        $this->setDataArray($sourceArray);

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getPreferredDateTime(): ?\DateTime
    {
        return $this->getDataPropertyValue(['preferredDateTime'], 'datetime');
    }

    /**
     * @param \DateTime|null $preferredDateTime
     * @return TicketResponse
     */
    public function setPreferredDateTime(?\DateTime $preferredDateTime): TicketResponse
    {
        return $this->setDataPropertyValue(['preferredDateTime'], 'datetime', $preferredDateTime);
    }

    /**
     * @return int
     */
    public function getServiceType(): int
    {
        return $this->getDataPropertyValue(['serviceType'], "int", 0);
    }

    /**
     * @return string
     */
    public function getServiceTypeString(): string
    {
        return Ticket::$serviceTypes[$this->getServiceType()] ?? "n/a";
    }


    /**
     * @param int $serviceType
     * @return TicketResponse
     */
    public function setServiceType(int $serviceType): TicketResponse
    {
        return $this->setDataPropertyValue(['serviceType'], "int", $serviceType);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $dataArray = $this->getDataArray();
        return $dataArray['description'] ?? "";
    }

    /**
     * @param string $description
     * @return TicketResponse
     */
    public function setDescription(string $description): TicketResponse
    {
        $dataArray = $this->getDataArray();
        $dataArray['description'] = $description;

        $this->setDataArray($dataArray);
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        $dataArray = $this->getDataArray();
        return $dataArray['status'] ?? 0;
    }

    /**
     * @return string
     */
    public function getStatusString(): string
    {
        return Ticket::$statuses[$this->getStatus()] ?? "n/a";
    }

    /**
     * @param int $status
     * @return TicketResponse
     */
    public function setStatus(int $status): TicketResponse
    {
        $dataArray = $this->getDataArray();
        $dataArray['status'] = $status;

        $this->setDataArray($dataArray);
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        $dataArray = $this->getDataArray();
        return $dataArray['priority'] ?? 0;
    }

    /**
     * @return string
     */
    public function getPriorityString(): string
    {
        return Ticket::$priorities[$this->getPriority()] ?? "n/a";
    }

    /**
     * @param int $priority
     * @return TicketResponse
     */
    public function setPriority(int $priority): TicketResponse
    {
        $dataArray = $this->getDataArray();
        $dataArray['priority'] = $priority;

        $this->setDataArray($dataArray);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getFilesList(): array
    {
        return $this->getDataPropertyValue(['filesList'], "array", []);
    }

    /**
     * @param string[] $files
     * @return TicketResponse
     */
    public function setFilesList(array $files): TicketResponse
    {
        $this->setDataPropertyValue(['filesList'], "array", $files);
        return $this;
    }

    /**
     * @return array
     */
    public function getFilesNames(): array
    {
        return array_map(
            function ($filePath) {
                return preg_replace('#^.*/([^/]+)$#isu', '$1', $filePath);
            },
            $this->getFilesList()
        );
    }

    /**
     * @return array
     */
    public function getProblemsAndSolutionsList(): array
    {
        return $this->getDataPropertyValue(['problems'], "array", []);
    }

    /**
     * @param array $problemsAndSolutionsList
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setProblemsAndSolutionsList(array $problemsAndSolutionsList): TicketResponse
    {
        $this->setDataPropertyValue(['problems'], "array", $problemsAndSolutionsList);
        return $this;
    }

    /**
     * @return array
     */
    public function getTicketChangesList(): array
    {
        $dataArray = $this->getDataArray();
        return $dataArray['ticketChangesList'] ?? [];
    }

    /**
     * @param array $initialState
     * @param array $finalState
     */
    public function detectTicketChanges(array $initialState, array $finalState)
    {
        $changesList = [];

        foreach ($finalState as $fieldName => $fieldValue) {
            if (isset($initialState[$fieldName])) {
                if ($initialState[$fieldName] == $fieldValue) continue;
            }

            switch ($fieldName) {

                case "assignedToId":
                case "customerContactId":
                case "preferredDateTime":
                case "serviceType":
                case "repairReason":
                case "description":
                case "status":
                case "priority":
                    // These fields are going to be noted
                    $changesList[] = $fieldName;
                    break;


                case "id":
                case "ticketId":
                case "machineId":
                case "customerId":
                case "machineLocation":
                case "createdDateTime":
                case "creatorId":
                default;
                    // We ignore these chages
                    break;

            }
        }

        $dataArray = $this->getDataArray();
        $dataArray['ticketChangesList'] = $changesList;

        $this->setDataArray($dataArray);
    }

    /**
     * @return array
     */
    public function getInstallationChecklist(): array
    {
        return $this->getDataPropertyValue(['installationChecklist'], "array", []);
    }

    /**
     * @return array
     */
    public function getInstallationExplainedChecklist(): array
    {

        $result = [];

        foreach ($this->getInstallationChecklist() as $checkedIndex) {
            $result[$checkedIndex] = self::$installationChecklist[$checkedIndex] ?? "n/a[{$checkedIndex}]";
        }

        return $result;
    }

    /**
     * @param array $checkedQuestions
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setInstallationChecklist(array $checkedQuestions): TicketResponse
    {
        $this->setDataPropertyValue(['installationChecklist'], "array", $checkedQuestions);
        return $this;
    }

    /**
     * @return array
     */
    public function getServiceChecklist(): array
    {
        return $this->getDataPropertyValue(['serviceChecklist'], "array", []);
    }

    /**
     * @return array
     */
    public function getServiceExplainedChecklist(): array
    {
        $result = [];

        foreach ($this->getServiceChecklist() as $checkedIndex) {
            $result[$checkedIndex] = self::$serviceChecklist[$checkedIndex] ?? "n/a[{$checkedIndex}]";
        }

        return $result;
    }

    /**
     * @param array $checkedQuestions
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setServiceChecklist(array $checkedQuestions): TicketResponse
    {
        $this->setDataPropertyValue(['serviceChecklist'], "array", $checkedQuestions);
        return $this;
    }

    /**
     * @return array
     */
    public function getMachineInfo(): array
    {
        return $this->getDataPropertyValue(
            ['machineInfo'],
            "array",
            ["burnCount" => "", "mainSoftwareVersion" => "", "displaySoftwareVersion" => ""]
        );
    }

    /**
     * @param array $machineInfo
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setMachineInfo(array $machineInfo): TicketResponse
    {
        $this->setDataPropertyValue(['machineInfo'], "array", $machineInfo);
        return $this;
    }

    /**
     * @return array
     */
    public function getTravelTable(): array
    {
        return $this->getDataPropertyValue(['travelTable'], "array", []);
    }

    /**
     * @param array $travelTableRow
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setTravelTable(array $travelTableRow): TicketResponse
    {
        $this->setDataPropertyValue(['travelTable'], "array", $travelTableRow);
        return $this;
    }

    /**
     * @return string
     */
    public function getTravelLabourCharged(): string
    {
        return $this->getDataPropertyValue(['travelLabourCharged'], "string", "");
    }

    /**
     * @param string $value
     * @return TicketResponse
     */
    public function setTravelLabourCharged(?string $value): TicketResponse
    {
        $this->setDataPropertyValue(['travelLabourCharged'], "string", $value ?? "");
        return $this;
    }

    /**
     * @return array
     */
    public function getPartsTable(): array
    {
        return $this->getDataPropertyValue(['partsTable'], "array", []);
    }

    /**
     * @param array $travelTableRow
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setPartsTable(array $travelTableRow): TicketResponse
    {
        $this->setDataPropertyValue(['partsTable'], "array", $travelTableRow);
        return $this;
    }

    /**
     * @return string
     */
    public function getSignatureUserName(): string
    {
        return $this->getDataPropertyValue(['signature', 'userName'], "string", "");
    }

    /**
     * @param string $value
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setSignatureUserName(string $value): TicketResponse
    {
        $this->setDataPropertyValue(['signature', 'userName'], "string", $value);
        return $this;
    }

    /**
     * @return string
     */
    public function getSignatureCustomerName(): string
    {
        return $this->getDataPropertyValue(['signature', 'customerName'], "string", "");
    }

    /**
     * @param string $value
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setSignatureCustomerName(string $value): TicketResponse
    {
        $this->setDataPropertyValue(['signature', 'customerName'], "string", $value);
        return $this;
    }

    /**
     * @return string
     */
    public function getSignatureCustomerTitle(): string
    {
        return $this->getDataPropertyValue(['signature', 'customerTitle'], "string", "");
    }

    /**
     * @param string $value
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setSignatureCustomerTitle(string $value): TicketResponse
    {
        $this->setDataPropertyValue(['signature', 'customerTitle'], "string", $value);
        return $this;
    }


    /**
     * @return string
     */
    public function getSignatureUserSignature(): string
    {
        return $this->getDataPropertyValue(['signature', 'userSignature'], "string", "");
    }

    /**
     * @param string $value
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setSignatureUserSignature(string $value): TicketResponse
    {
        $this->setDataPropertyValue(['signature', 'userSignature'], "string", $value);
        return $this;
    }

    /**
     * @return string
     */
    public function getSignatureCustomerSignature(): string
    {
        return $this->getDataPropertyValue(['signature', 'customerSignature'], "string", "");
    }

    /**
     * @param string $value
     * @return TicketResponse
     * @throws InvalidArgumentException
     */
    public function setSignatureCustomerSignature(string $value): TicketResponse
    {
        $this->setDataPropertyValue(['signature', 'customerSignature'], "string", $value);
        return $this;
    }


    /**
     * @param Ticket $ticket
     * @return TicketResponse
     */
    public function assignTicket(Ticket $ticket): TicketResponse
    {
        $this
            ->setTicket($ticket)
            ->setAssignedTo($ticket->getAssignedTo())
            ->setCustomerContact($ticket->getCustomerContact())
            ->setPreferredDateTime($ticket->getPreferredDateTime())
            ->setServiceType($ticket->getServiceType())
            ->setDescription($ticket->getDescription())
            ->setStatus($ticket->getStatus())
            ->setPriority($ticket->getPriority())
        ;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id" => $this->getId(),

            "ticketId" => $this->getTicket() ? $this->getTicket()->getId() : 0,
            "ticketIdString" => $this->getTicket() ? $this->getTicket()->getTicketId() : "",

            "creatorId" => $this->getCreator() ? $this->getCreator()->getId() : 0,
            "creatorFullName" => $this->getCreator() ? $this->getCreator()->getFullName() : "",

            "assignedToId" => $this->getAssignedTo() ? $this->getAssignedTo()->getId() : 0,
            "assignedToFullName" => $this->getAssignedTo() ? $this->getAssignedTo()->getFullName() : "",

            "createdDateTime" =>
                $this->getCreatedDateTime()
                    ? $this->getCreatedDateTime()->format("Y-m-d H:i:s")
                    : "",

            "message" => $this->getMessage(),
            "responseType" => $this->getResponseType(),
            "responseTypeString" => $this->getResponseTypeString(),
            "data" => $this->getDataArray(),

            "customerContactId" => $this->getCustomerContact() ? $this->getCustomerContact()->getId() : 0,
            "customerContactFullName" => $this->getCustomerContact() ? $this->getCustomerContact()->getFullName() : "",
        ];
    }
}

