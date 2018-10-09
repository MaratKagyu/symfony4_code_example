<?php

namespace App\Entity\Machine;

use App\Entity\Customer\Customer;
use App\Entity\Subsidiary;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Machine\MachineServiceContractRepository")
 */
class MachineServiceContract
{
    const STATUS_CANCELED = -2;
    const STATUS_VOIDED = -1;
    const STATUS_DRAFT = 0;
    const STATUS_AWAITING_SIGNATURE = 5;
    const STATUS_ACTIVE = 10;

    const FLAG_IS_EVERSIGN_CONTRACT = 1;

    /**
     * @var array
     */
    public static $statusesList = [
        self::STATUS_CANCELED => "Canceled",
        self::STATUS_VOIDED => "Voided",
        self::STATUS_DRAFT => "Draft",
        self::STATUS_ACTIVE => "Active",
        self::STATUS_AWAITING_SIGNATURE => "Awaiting signature"
    ];

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id = 0;

    /**
     * @var Subsidiary
     * @ORM\ManyToOne(targetEntity="\App\Entity\Subsidiary")
     * @ORM\JoinColumn(name="subsidiary_id", referencedColumnName="id")
     */
    private $subsidiary;

    /**
     * @var Machine
     * @ORM\ManyToOne(targetEntity="Machine")
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
    private $contact;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $contactEmail = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $customerAddress = "";


    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $contractNumber = "";

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $contractType = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $filesListJson = "";

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_DRAFT;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdDateTime;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    private $creator;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $eversignDocumentHash = "";

    /**
     * Action hash using which Eversign.com can access document data
     * @var string
     * @ORM\Column(type="text")
     */
    private $eversignActionHash = "";

    /**
     * @var int
     * @ORM\Column(type="bigint")
     */
    private $flags = 0;




    /**
     * @param int $contractId
     * @return string
     */
    public static function getFilesLocationById(int $contractId): string
    {
        if (! $contractId) {
            return "var/temp/contract_files/";
        }

        $idString = sprintf('%08d', $contractId);

        return (
            "private/upload/contract_files/"
            . substr($idString, 0, 2) . "/"
            . substr($idString, 0, 4)  . "/"
            . substr($idString, 0, 6) . "/"
            . $idString . "/"
        );
    }




    /**
     * MachineServiceContract constructor.
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
     * @return MachineServiceContract
     */
    public function setId(int $id): MachineServiceContract
    {
        $this->id = $id;
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
     * @return MachineServiceContract
     */
    public function setSubsidiary(Subsidiary $subsidiary): MachineServiceContract
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
     * @return MachineServiceContract
     */
    public function setMachine(?Machine $machine): MachineServiceContract
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
     * @return MachineServiceContract
     */
    public function setCustomer(?Customer $customer): MachineServiceContract
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getContact(): ?User
    {
        return $this->contact;
    }

    /**
     * @param User|null $contact
     * @return MachineServiceContract
     */
    public function setContact(?User $contact): MachineServiceContract
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * @return string
     */
    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    /**
     * @param string $contactEmail
     * @return MachineServiceContract
     */
    public function setContactEmail(string $contactEmail): MachineServiceContract
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerAddress(): string
    {
        return $this->customerAddress;
    }

    /**
     * @param string $customerAddress
     * @return MachineServiceContract
     */
    public function setCustomerAddress(string $customerAddress): MachineServiceContract
    {
        $this->customerAddress = $customerAddress;
        return $this;
    }




    /**
     * @return string
     */
    public function getContractNumber(): string
    {
        return $this->contractNumber;
    }

    /**
     * @param string $contractNumber
     * @return MachineServiceContract
     */
    public function setContractNumber(string $contractNumber): MachineServiceContract
    {
        $this->contractNumber = $contractNumber;
        return $this;
    }



    /**
     * @return \DateTime|null
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime|null $startDate
     * @return MachineServiceContract
     */
    public function setStartDate(?\DateTime $startDate): MachineServiceContract
    {
        $this->startDate = $startDate;
        return $this;
    }


    /**
     * @return string
     */
    public function getStartDateString(): string
    {
        return $this->startDate ? $this->startDate->format("d/m/Y") : "";
    }

    /**
     * @param string $startDateString
     * @return MachineServiceContract
     */
    public function setStartDateString(string $startDateString): MachineServiceContract
    {
        $dateValue = \DateTime::createFromFormat("d/m/Y", $startDateString);
        $this->startDate = $dateValue ? $dateValue : null;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime|null $endDate
     * @return MachineServiceContract
     */
    public function setEndDate(?\DateTime $endDate): MachineServiceContract
    {
        $this->endDate = $endDate;
        return $this;
    }


    /**
     * @return string
     */
    public function getEndDateString(): string
    {
        return $this->endDate ? $this->endDate->format("d/m/Y") : "";
    }

    /**
     * @param string $endDateString
     * @return MachineServiceContract
     */
    public function setEndDateString(string $endDateString): MachineServiceContract
    {
        $dateValue = \DateTime::createFromFormat("d/m/Y", $endDateString);
        $this->endDate = $dateValue ? $dateValue : null;
        return $this;
    }

    /**
     * @return string
     */
    public function getContractType(): string
    {
        return $this->contractType;
    }

    /**
     * @param string $contractType
     * @return MachineServiceContract
     */
    public function setContractType(string $contractType): MachineServiceContract
    {
        $this->contractType = $contractType;
        return $this;
    }


    /**
     * @return string
     */
    public function getFilesListJson(): string
    {
        return $this->filesListJson ? $this->filesListJson : "";
    }

    /**
     * @return string[]
     */
    public function getFilesList(): array
    {
        // make sure we return only file names, not their paths
        $fileList = @json_decode($this->filesListJson, true);

        $resultList = [];
        if ($fileList) {
            foreach ($fileList as $fileName) {
                $fileNameArray = explode('/', $fileName);
                $resultList[] = array_pop($fileNameArray);
            }
        }

        return $resultList;
    }

    /**
     * @param string|null $filesListJson
     * @return MachineServiceContract
     */
    public function setFilesListJson(?string $filesListJson): MachineServiceContract
    {
        // make sure we save only file names, not their paths
        $filesListJson = $filesListJson ?: '[]';
        $fileList = @json_decode($filesListJson, true);

        $resultList = [];
        if ($fileList) {
            foreach ($fileList as $fileName) {
                $fileNameArray = explode('/', $fileName);
                $resultList[] = array_pop($fileNameArray);
            }
        }

        $this->filesListJson = json_encode($resultList, JSON_UNESCAPED_UNICODE);
        return $this;
    }

    /**
     * @param string[] $filesList
     * @return MachineServiceContract
     */
    public function setFilesList(array $filesList): MachineServiceContract
    {
        // make sure we save only file names, not their paths
        $resultList = [];

        foreach ($filesList as $fileName) {
            $fileNameArray = explode('/', $fileName);
            $resultList[] = array_pop($fileNameArray);
        }

        $this->filesListJson = json_encode($resultList, JSON_UNESCAPED_UNICODE);
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
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->getStatus() !== self::STATUS_ACTIVE) return false;

        $currentTime = time();
        if ($this->getStartDate()) {
            if ($this->getStartDate()->getTimestamp() > $currentTime) return false;
        }

        if ($this->getEndDate()) {
            if ($this->getEndDate()->getTimestamp() < $currentTime) return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getStatusDescription(): string
    {
        return isset(self::$statusesList[$this->status]) ? self::$statusesList[$this->status] : "n/a";
    }

    /**
     * @param int $status
     * @return MachineServiceContract
     */
    public function setStatus(int $status): MachineServiceContract
    {
        $this->status = $status;
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
     * @return MachineServiceContract
     */
    public function setCreatedDateTime(\DateTime $createdDateTime): MachineServiceContract
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
     * @return MachineServiceContract
     */
    public function setCreator(?User $creator): MachineServiceContract
    {
        $this->creator = $creator;
        return $this;
    }



    /**
     * @return string
     */
    public function getEversignDocumentHash(): string
    {
        return $this->eversignDocumentHash;
    }

    /**
     * @param string $eversignDocumentHash
     * @return MachineServiceContract
     */
    public function setEversignDocumentHash(string $eversignDocumentHash): MachineServiceContract
    {
        $this->eversignDocumentHash = $eversignDocumentHash;
        return $this;
    }

    /**
     * @return string
     */
    public function getEversignActionHash(): string
    {
        return $this->eversignActionHash;
    }

    /**
     * @param string $eversignActionHash
     * @return MachineServiceContract
     */
    public function setEversignActionHash(string $eversignActionHash): MachineServiceContract
    {
        $this->eversignActionHash = $eversignActionHash;
        return $this;
    }

    /**
     * @return MachineServiceContract
     */
    public function generateEversignActionHash(): MachineServiceContract
    {
        $this->eversignActionHash = md5(rand() * rand());
        return $this;
    }



    /**
     * @param int $flag
     * @return MachineServiceContract
     */
    public function addFlag($flag): MachineServiceContract
    {
        $this->flags = $this->flags | $flag;
        return $this;
    }

    /**
     * @param int $flag
     * @return bool
     */
    public function hasFlag($flag): bool
    {
        return !! ($this->flags & $flag);
    }

    /**
     * @param int $flag
     * @return MachineServiceContract
     */
    public function removeFlag($flag): MachineServiceContract
    {
        if ($this->hasFlag($flag)) {
            $this->flags = $this->flags ^ $flag;
        }
        return $this;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "id" => $this->id,
            "machineId" => $this->machine ? $this->machine->getId() : 0,
            "subsidiaryId" => $this->subsidiary ? $this->subsidiary->getId() : 0,
            "customerId" => $this->customer ? $this->customer->getId() : 0,
            "contactId" => $this->contact ? $this->contact->getId() : 0,
            "customerAddress" => $this->customerAddress,
            "contactEmail" => $this->contactEmail,
            "contractNumber" => $this->contractNumber,
            "contractType" => $this->contractType,
            "startDate" => $this->startDate ? $this->startDate->format("Y-m-d") : "",
            "endDate" => $this->endDate ? $this->endDate->format("Y-m-d") : "",
            "fileList" => $this->getFilesList(),
            "status" => $this->status,
            "createdDateTime" => $this->createdDateTime ? $this->createdDateTime->format("Y-m-d") : "",
            "creatorId" => $this->creator ? $this->creator->getId() : 0,
            "creatorFullName" => $this->creator ? $this->creator->getFullName() : "",
            "eversignActionHash" => $this->eversignActionHash,
            "eversignDocumentHash" => $this->eversignDocumentHash,
            "flags" => $this->flags,
        ];
    }


    /**
     * @return array
     */
    public function toInfoArray()
    {
        return [
            "id" => $this->id,
            "machineId" => $this->machine ? $this->machine->getId() : 0,
            "machineSerialId" => $this->machine ? $this->machine->getSerialId() : "",
            "subsidiaryId" => $this->subsidiary ? $this->subsidiary->getId() : 0,
            "subsidiaryName" => $this->subsidiary ? $this->subsidiary->getName() : "",
            "subsidiaryShortCode" => $this->subsidiary ? $this->subsidiary->getShortCode() : "",
            "customerId" => $this->customer ? $this->customer->getId() : 0,
            "customerFullName" => $this->customer ? $this->customer->getFullName() : "",
            "contactId" => $this->contact ? $this->contact->getId() : 0,
            "contactFullName" => $this->contact ? $this->contact->getFullName() : "",
            "customerAddress" => $this->customerAddress,
            "contactEmail" => $this->contactEmail,
            "contractNumber" => $this->contractNumber,
            "contractType" => $this->contractType,
            "startDate" => $this->startDate ? $this->startDate->format("Y-m-d") : "",
            "endDate" => $this->endDate ? $this->endDate->format("Y-m-d") : "",
            "fileList" => $this->getFilesList(),
            "status" => $this->status,
            "createdDateTime" => $this->createdDateTime ? $this->createdDateTime->format("Y-m-d") : "",
            "creatorId" => $this->creator ? $this->creator->getId() : 0,
            "creatorFullName" => $this->creator ? $this->creator->getFullName() : "",
            "flags" => $this->flags,
        ];
    }

}
