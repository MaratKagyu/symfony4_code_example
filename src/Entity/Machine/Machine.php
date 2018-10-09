<?php

namespace App\Entity\Machine;

use App\Entity\Customer\Customer;
use App\Entity\Subsidiary;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Machine\MachineRepository")
 * @ORM\Table(name="machine",indexes={@ORM\Index(name="serial_id", columns={"serial_id"})})
 * @UniqueEntity("serialId")
 */
class Machine
{
    const STATUS_INACTIVE = -1;
    const STATUS_RETURNED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_LOAN = 2;
    const STATUS_IN_REPAIR = 3;



    /**
     * @var array
     */
    public static $statusesList = [
        self::STATUS_INACTIVE => "Inactive",
        self::STATUS_RETURNED => "Returned",
        self::STATUS_ACTIVE => "Active",
        self::STATUS_LOAN => "Loan",
        self::STATUS_IN_REPAIR => "Repair"
    ];


    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id = 0;


    /**
     * @var string
     * @ORM\Column(name="serial_id", type="string", length=100)
     */
    private $serialId = "";


    /**
     * @var \DateTime
     * @ORM\Column(type="date")
     */
    private $buildDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="date")
     */
    private $lastMovementDate;


    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $buildLocation = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $currentLocationCountry = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $currentLocationState = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $currentLocationCity = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $currentLocationAddress = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $currentLocationZip = "";

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $currentDistanceFormHQ = 0;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $currentContractType = "";


    /**
     * @var ArrayCollection|MachineType[]
     * @ORM\ManyToMany(targetEntity="MachineType")
     * @ORM\JoinTable(name="machine_types_relations",
     *      joinColumns={@ORM\JoinColumn(name="machine_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="type_id", referencedColumnName="id")}
     * )
     */
    private $types;


    /**
     * @var Subsidiary
     * @ORM\ManyToOne(targetEntity="\App\Entity\Subsidiary")
     * @ORM\JoinColumn(name="subsidiary_id", referencedColumnName="id")
     */
    private $subsidiary;


    /**
     * @var Customer
     * @ORM\ManyToOne(targetEntity="\App\Entity\Customer\Customer")
     * @ORM\JoinColumn(name="current_holder_id", referencedColumnName="id")
     */
    private $currentHolder;


    /**
     * @var Ticket[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\Machine\Ticket", mappedBy="machine")
     * @ORM\OrderBy({"preferredDateTime" = "ASC"})
     */
    private $ticketList;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdDateTime;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    private $creator;


    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_RETURNED;


    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $coordinatesJson = "";

    /**
     * Partners (Subsidiary type == Subsidiary::TYPE_PARTNER) associated with the Machine
     * THIS IS NOT a direct Machine <-> Subsidiary relation.
     * A machine can be associated with one main Subsidiary
     * and with multiple Partners (Subsidiary type == Subsidiary::TYPE_PARTNER)
     *
     * @var Subsidiary[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="\App\Entity\Subsidiary", mappedBy="associatedMachines")
     */
    private $associatedPartners;

    /**
     * Machine constructor.
     */
    public function __construct()
    {
        $this->buildDate = new \DateTime();
        $this->lastMovementDate = new \DateTime();
        $this->types = new ArrayCollection();
        $this->ticketList = new ArrayCollection();
        $this->createdDateTime = new \DateTime();
        $this->associatedPartners = new ArrayCollection();
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
     * @return Machine
     */
    public function setId(int $id): Machine
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSerialId(): string
    {
        return $this->serialId;
    }

    /**
     * @param string $serialId
     * @return Machine
     */
    public function setSerialId(string $serialId): Machine
    {
        $this->serialId = $serialId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBuildDate(): \DateTime
    {
        return $this->buildDate;
    }

    /**
     * @return string
     */
    public function getBuildDateString(): string
    {
        return $this->buildDate ? $this->buildDate->format("d/m/Y") : "";
    }

    /**
     * @param \DateTime $buildDate
     * @return Machine
     */
    public function setBuildDate(\DateTime $buildDate): Machine
    {
        $this->buildDate = $buildDate;
        return $this;
    }


    /**
     * @param string $buildDateString
     * @return Machine
     */
    public function setBuildDateString($buildDateString): Machine
    {
        $this->buildDate = \DateTime::createFromFormat("d/m/Y", $buildDateString);
        return $this;
    }


    /**
     * @return \DateTime|null
     */
    public function getLastMovementDate(): ?\DateTime
    {
        return $this->lastMovementDate;
    }

    /**
     * @param \DateTime|null $lastMovementDate
     * @return Machine
     */
    public function setLastMovementDate(?\DateTime $lastMovementDate): Machine
    {
        $this->lastMovementDate = $lastMovementDate;
        return $this;
    }




    /**
     * @return string
     */
    public function getBuildLocation(): string
    {
        return $this->buildLocation;
    }

    /**
     * @param string $buildLocation
     * @return Machine
     */
    public function setBuildLocation(string $buildLocation): Machine
    {
        $this->buildLocation = $buildLocation;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentLocationCountry(): string
    {
        return $this->currentLocationCountry;
    }

    /**
     * @param string $currentLocationCountry
     * @return Machine
     */
    public function setCurrentLocationCountry(string $currentLocationCountry): Machine
    {
        $this->currentLocationCountry = $currentLocationCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentLocationState(): string
    {
        return $this->currentLocationState;
    }

    /**
     * @param string $currentLocationState
     * @return Machine
     */
    public function setCurrentLocationState(string $currentLocationState): Machine
    {
        $this->currentLocationState = $currentLocationState;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentLocationCity(): string
    {
        return $this->currentLocationCity;
    }

    /**
     * @param string $currentLocationCity
     * @return Machine
     */
    public function setCurrentLocationCity(string $currentLocationCity): Machine
    {
        $this->currentLocationCity = $currentLocationCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentLocationAddress(): string
    {
        return $this->currentLocationAddress;
    }

    /**
     * @param string $currentLocationAddress
     * @return Machine
     */
    public function setCurrentLocationAddress(string $currentLocationAddress): Machine
    {
        $this->currentLocationAddress = $currentLocationAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentLocationZip(): string
    {
        return $this->currentLocationZip;
    }

    /**
     * @param string $currentLocationZip
     * @return Machine
     */
    public function setCurrentLocationZip(string $currentLocationZip): Machine
    {
        $this->currentLocationZip = $currentLocationZip;
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
                    $this->getCurrentLocationAddress(),
                    $this->getCurrentLocationCity(),
                    $this->getCurrentLocationState(),
                    $this->getCurrentLocationZip(),
                    $this->getCurrentLocationCountry()
                ],
                function ($itemString) {
                    return !! trim($itemString);
                }
            )
        );
    }

    /**
     * @return string
     * @deprecated
     */
    public function getCurrentLocation(): string
    {
        return $this->getFullAddress();
    }


    /**
     * @return MachineType[]|ArrayCollection
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return array
     */
    public function getTypesArray()
    {
        $result = [];

        foreach ($this->getTypes()->toArray() as $type) {
            /* @var MachineType $type */
            $result[] = $type->toArray();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getTypesString(): string
    {
        return implode(
            " ",
            array_map(
                function (MachineType $machineType) { return $machineType->getTypeName(); },
                $this->getTypes()->toArray()
            )
        );
    }

    /**
     * @param MachineType[]|ArrayCollection $types
     * @return Machine
     */
    public function setTypes($types): Machine
    {
        $this->types = $types;
        return $this;
    }


    /**
     * @param MachineType $machineType
     * @return Machine
     */
    public function addType(MachineType $machineType): Machine
    {
        if (! $this->types->indexOf($machineType) === false) {
            $this->types->add($machineType);
        }
        return $this;
    }

    /**
     * @param MachineType $machineType
     * @return Machine
     */
    public function removeType(MachineType $machineType): Machine
    {
        if (! $this->types->indexOf($machineType) !== false) {
            $this->types->removeElement($machineType);
        }
        return $this;
    }


    /**
     * @return Subsidiary
     */
    public function getSubsidiary(): ?Subsidiary
    {
        return $this->subsidiary;
    }

    /**
     * @param Subsidiary $subsidiary
     * @return Machine
     */
    public function setSubsidiary(Subsidiary $subsidiary): Machine
    {
        $this->subsidiary = $subsidiary;
        return $this;
    }

    /**
     * @return Customer
     */
    public function getCurrentHolder(): ?Customer
    {
        return $this->currentHolder;
    }

    /**
     * @param Customer|null $currentHolder
     * @return Machine
     */
    public function setCurrentHolder(?Customer $currentHolder): Machine
    {
        $this->currentHolder = $currentHolder;
        return $this;
    }

    /**
     * @return Ticket[]|ArrayCollection
     */
    public function getTicketList()
    {
        return $this->ticketList;
    }

    /**
     * @param Ticket[]|ArrayCollection $ticketList
     * @return Machine
     */
    public function setTicketList($ticketList)
    {
        $this->ticketList = $ticketList;
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
     * @param int $status
     * @return Machine
     */
    public function setStatus(int $status): Machine
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusDescription(): string
    {
        if (isset(self::$statusesList[$this->status])) return self::$statusesList[$this->status];

        return "n/a";
    }



    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return (
            $this->getCurrentHolder()
            ? $this->getCurrentHolder()->getCompanyName()
            : ($this->getSubsidiary() ? $this->getSubsidiary()->getName() : "n/a")
        );
    }

    /**
     * @return int
     */
    public function getCurrentDistanceFormHQ(): int
    {
        return $this->currentDistanceFormHQ;
    }

    /**
     * @param int $currentDistanceFormHQ
     * @return Machine
     */
    public function setCurrentDistanceFormHQ(int $currentDistanceFormHQ): Machine
    {
        $this->currentDistanceFormHQ = $currentDistanceFormHQ;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentContractType(): string
    {
        return $this->currentContractType;
    }

    /**
     * @param string $currentContractType
     * @return Machine
     */
    public function setCurrentContractType(string $currentContractType): Machine
    {
        $this->currentContractType = $currentContractType;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedDateTime(): ?\DateTime
    {
        return $this->createdDateTime;
    }

    /**
     * @param \DateTime $createdDateTime
     * @return Machine
     */
    public function setCreatedDateTime(\DateTime $createdDateTime): Machine
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
     * @return Machine
     */
    public function setCreator(?User $creator): Machine
    {
        $this->creator = $creator;
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
     * @return Machine
     */
    public function setCoordinatesJson(string $coordinatesJson): Machine
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
     * @return Machine
     */
    public function setCoordinatesLongitude(float $value): Machine
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
     * @return Machine
     */
    public function setCoordinatesLatitude(float $value): Machine
    {
        $coordinates = @json_decode($this->getCoordinatesJson(), true) ?: ["lat" => 0, "lng" => 0];
        $coordinates['lat'] = $value;

        $this->setCoordinatesJson(json_encode($coordinates));
        return $this;
    }

    /**
     * Partners (Subsidiary type == Subsidiary::TYPE_PARTNER) associated with the Machine
     * THIS IS NOT a direct Machine <-> Subsidiary relation.
     * A machine can be associated with one main Subsidiary
     * and with multiple Partners (Subsidiary type == Subsidiary::TYPE_PARTNER)
     *
     * @return Subsidiary[]|ArrayCollection
     */
    public function getAssociatedPartners()
    {
        return $this->associatedPartners;
    }

    /**
     * Partners (Subsidiary type == Subsidiary::TYPE_PARTNER) associated with the Machine
     * THIS IS NOT a direct Machine <-> Subsidiary relation.
     * A machine can be associated with one main Subsidiary
     * and with multiple Partners (Subsidiary type == Subsidiary::TYPE_PARTNER)
     *
     * @param Subsidiary[]|ArrayCollection $associatedPartners
     * @return Machine
     */
    public function setAssociatedPartners($associatedPartners)
    {
        $this->associatedPartners = $associatedPartners;
        return $this;
    }

    /**
     * @param Subsidiary $associatedPartner
     * @return Machine
     */
    public function addAssociatedPartners(Subsidiary $associatedPartner)
    {
        $this->associatedPartners->add($associatedPartner);
        return $this;
    }

    /**
     * @param Subsidiary $associatedPartner
     * @return Machine
     */
    public function removeAssociatedPartners(Subsidiary $associatedPartner)
    {
        $this->associatedPartners->removeElement($associatedPartner);
        return $this;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "id" => $this->id,
            "serialId" => $this->serialId,
            "buildDate" => $this->getBuildDateString(),
            "lastMovementDate" => $this->lastMovementDate ? $this->lastMovementDate->format("Y-m-d") : "",
            "buildLocation" => $this->buildLocation,
            "currentLocation" => $this->getFullAddress(),
            "currentLocationCountry" => $this->getCurrentLocationCountry(),
            "currentLocationState" => $this->getCurrentLocationState(),
            "currentLocationCity" => $this->getCurrentLocationCity(),
            "currentLocationAddress" => $this->getCurrentLocationAddress(),
            "currentLocationZip" => $this->getCurrentLocationZip(),
            "coordinatesLatitude" => $this->getCoordinatesLatitude(),
            "coordinatesLongitude" => $this->getCoordinatesLongitude(),
            "subsidiaryId" => $this->subsidiary ? $this->subsidiary->getId() : 0,
            "subsidiaryName" => $this->subsidiary ? $this->subsidiary->getName() : "",
            "currentHolderId" => $this->currentHolder ? $this->currentHolder->getId() : 0,
            "types" => $this->getTypesArray(),
            "status" => $this->getStatus(),
            "currentDistanceFormHQ" => $this->currentDistanceFormHQ,
            "currentContractType" => $this->getCurrentContractType(),
            "createdDateTime" => (
                $this->getCreatedDateTime()
                    ? $this->getCreatedDateTime()->format("Y-m-d H:i:s")
                    : ""
            ),
            "creatorId" => $this->getCreator() ? $this->getCreator()->getId() : 0,
            "creatorFullName" => $this->getCreator() ? $this->getCreator()->getFullName() : "",
        ];
    }


    /**
     * @return array
     */
    public function toInfoArray()
    {
        return [
            "id" => $this->id,
            "serialId" => $this->serialId,
            "buildDate" => $this->getBuildDateString(),
            "lastMovementDate" => $this->lastMovementDate ? $this->lastMovementDate->format("Y-m-d") : "",
            "buildLocation" => $this->buildLocation,
            "currentLocation" => $this->getFullAddress(),
            "currentLocationCountry" => $this->getCurrentLocationCountry(),
            "currentLocationState" => $this->getCurrentLocationState(),
            "currentLocationCity" => $this->getCurrentLocationCity(),
            "currentLocationAddress" => $this->getCurrentLocationAddress(),
            "currentLocationZip" => $this->getCurrentLocationZip(),
            "coordinatesLatitude" => $this->getCoordinatesLatitude(),
            "coordinatesLongitude" => $this->getCoordinatesLongitude(),
            "subsidiaryId" => $this->subsidiary ? $this->subsidiary->getId() : 0,
            "subsidiaryName" => $this->subsidiary ? $this->subsidiary->getName() : "",
            "currentHolderId" => $this->currentHolder ? $this->currentHolder->getId() : 0,
            "currentHolderCompanyName" => $this->currentHolder ? $this->currentHolder->getFullName() : "",
            "currentHolderCustomerId" => $this->currentHolder ? $this->currentHolder->getCustomerId() : "",
            "types" => $this->getTypesArray(),
            "status" => $this->getStatus(),
            "statusString" => $this->getStatusDescription(),
            "currentDistanceFormHQ" => $this->currentDistanceFormHQ,
            "currentContractType" => $this->getCurrentContractType(),
            "createdDateTime" => (
                $this->getCreatedDateTime()
                    ? $this->getCreatedDateTime()->format("Y-m-d H:i:s")
                    : ""
            ),
            "creatorId" => $this->getCreator() ? $this->getCreator()->getId() : 0,
            "creatorFullName" => $this->getCreator() ? $this->getCreator()->getFullName() : "",
        ];
    }

}
