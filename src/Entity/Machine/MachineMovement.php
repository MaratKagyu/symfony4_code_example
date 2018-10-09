<?php

namespace App\Entity\Machine;

use App\Entity\Customer\Customer;
use App\Entity\Subsidiary;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Machine\MachineMovementRepository")
 */
class MachineMovement
{
    const DIRECTION_IN = 0;
    const DIRECTION_OUT = 1;
    const DIRECTION_TO_ANOTHER_SUBSIDIARY = 2;

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id = 0;


    /**
     * @var Machine
     * @ORM\ManyToOne(targetEntity="Machine")
     * @ORM\JoinColumn(name="machine_id", referencedColumnName="id")
     */
    private $machine;

    /**
     * @var Subsidiary|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\Subsidiary")
     * @ORM\JoinColumn(name="subsidiary_id", referencedColumnName="id")
     */
    private $subsidiary;


    /**
     * @var int
     * @ORM\Column(type="bigint")
     */
    private $direction = self::DIRECTION_OUT;

    /**
     * @var int
     * @ORM\Column(type="bigint")
     */
    private $machineStatus = Machine::STATUS_ACTIVE;


    /**
     * @var Customer|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\Customer\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;


    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $contractType = "";


    /**
     * @var \DateTime
     * @ORM\Column(type="date")
     */
    private $movementDate;


    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $locationAddress = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $locationCountry = "";


    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $locationCity = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $locationState = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $locationZip = "";


    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $distance = 0;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $filesListJson = "[]";


    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="added_by_id", referencedColumnName="id")
     */
    private $addedBy;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $addedDateTime;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $reason = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $coordinatesJson = "";

    /**
     * MachineMovement constructor.
     */
    public function __construct()
    {
        $this->movementDate = new \DateTime;
        $this->addedDateTime = new \DateTime;
    }

    /**
     * @param int $machineId
     * @return string
     */
    public static function getFilesLocationByMachineId(int $machineId): string
    {
        $idString = sprintf('%03d', $machineId);

        return (
            "private/upload/machine_movement_contracts/" .
            substr($idString, 0, 1) . "/" .
            substr($idString, 0, 2)  . "/" .
            substr($idString, 0, 3) . "/"
        );
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
     * @return MachineMovement
     */
    public function setId(int $id): MachineMovement
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Machine
     */
    public function getMachine(): Machine
    {
        return $this->machine;
    }

    /**
     * @param Machine $machine
     * @return MachineMovement
     */
    public function setMachine(Machine $machine): MachineMovement
    {
        $this->machine = $machine;
        return $this;
    }

    /**
     * @return Subsidiary|null
     */
    public function getSubsidiary(): ?Subsidiary
    {
        return $this->subsidiary;
    }

    /**
     * @param Subsidiary|null $subsidiary
     * @return MachineMovement
     */
    public function setSubsidiary(?Subsidiary $subsidiary): MachineMovement
    {
        $this->subsidiary = $subsidiary;
        return $this;
    }



    /**
     * @return int
     */
    public function getDirection(): int
    {
        return $this->direction;
    }

    /**
     * @param int $direction
     * @return MachineMovement
     */
    public function setDirection(int $direction): MachineMovement
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * @return int
     */
    public function getMachineStatus(): int
    {
        return $this->machineStatus;
    }

    /**
     * @return string
     */
    public function getMachineStatusDescription(): string
    {
        if (isset(Machine::$statusesList[$this->machineStatus])) return Machine::$statusesList[$this->machineStatus];

        return "n/a";
    }

    /**
     * @param int $machineStatus
     * @return MachineMovement
     */
    public function setMachineStatus(int $machineStatus): MachineMovement
    {
        $this->machineStatus = $machineStatus;
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
     * @return MachineMovement
     */
    public function setCustomer(?Customer $customer): MachineMovement
    {
        $this->customer = $customer;
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
     * @return MachineMovement
     */
    public function setContractType(string $contractType): MachineMovement
    {
        $this->contractType = $contractType;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getMovementDate(): ?\DateTime
    {
        return $this->movementDate;
    }

    /**
     * @return string
     */
    public function getMovementDateString(): string
    {
        return $this->movementDate ? $this->movementDate->format("d/m/Y") : "";
    }

    /**
     * @param \DateTime $movementDate
     * @return MachineMovement
     */
    public function setMovementDate(\DateTime $movementDate): MachineMovement
    {
        $this->movementDate = $movementDate;
        return $this;
    }

    /**
     * @param string $movementDateString
     * @return MachineMovement
     */
    public function setMovementDateString(string $movementDateString): MachineMovement
    {
        $this->movementDate = \DateTime::createFromFormat("d/m/Y", $movementDateString);
        return $this;
    }


    /**
     * @return string
     */
    public function getLocationAddress(): string
    {
        return $this->locationAddress;
    }

    /**
     * @param string $locationAddress
     * @return MachineMovement
     */
    public function setLocationAddress(string $locationAddress): MachineMovement
    {
        $this->locationAddress = $locationAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationCountry(): string
    {
        return $this->locationCountry;
    }

    /**
     * @param string $locationCountry
     * @return MachineMovement
     */
    public function setLocationCountry(string $locationCountry): MachineMovement
    {
        $this->locationCountry = $locationCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationCity(): string
    {
        return $this->locationCity;
    }

    /**
     * @param string $locationCity
     * @return MachineMovement
     */
    public function setLocationCity(string $locationCity): MachineMovement
    {
        $this->locationCity = $locationCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationState(): string
    {
        return $this->locationState;
    }

    /**
     * @param string $locationState
     * @return MachineMovement
     */
    public function setLocationState(string $locationState): MachineMovement
    {
        $this->locationState = $locationState;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocationZip(): string
    {
        return $this->locationZip;
    }

    /**
     * @param string $locationZip
     * @return MachineMovement
     */
    public function setLocationZip(string $locationZip): MachineMovement
    {
        $this->locationZip = $locationZip;
        return $this;
    }

    /**
     * @return string
     * @deprecated User $this->getFullAddress instead
     */
    public function getLocationString(): string
    {
        return $this->getFullAddress();
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
                    $this->getLocationAddress(),
                    $this->getLocationCity(),
                    $this->getLocationState(),
                    $this->getLocationZip(),
                    $this->getLocationCountry()
                ],
                function ($itemString) {
                    return !! trim($itemString);
                }
            )
        );
    }


    /**
     * @return int
     */
    public function getDistance(): int
    {
        return $this->distance;
    }

    /**
     * @param int $distance
     * @return MachineMovement
     */
    public function setDistance(int $distance): MachineMovement
    {
        $this->distance = $distance;
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
     * @param string $filesListJson
     * @return MachineMovement
     */
    public function setFilesListJson(string $filesListJson): MachineMovement
    {
        // make sure we save only file names, not their paths
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
     * @return MachineMovement
     */
    public function setFilesList(array $filesList): MachineMovement
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
     * @return User|null
     */
    public function getAddedBy(): ?User
    {
        return $this->addedBy;
    }

    /**
     * @param User $addedBy
     * @return MachineMovement
     */
    public function setAddedBy(User $addedBy): MachineMovement
    {
        $this->addedBy = $addedBy;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAddedDateTime(): \DateTime
    {
        return $this->addedDateTime;
    }

    /**
     * @param \DateTime $addedDateTime
     * @return MachineMovement
     */
    public function setAddedDateTime(\DateTime $addedDateTime): MachineMovement
    {
        $this->addedDateTime = $addedDateTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     * @return MachineMovement
     */
    public function setReason(string $reason): MachineMovement
    {
        $this->reason = $reason;
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
     * @return MachineMovement
     */
    public function setCoordinatesJson(string $coordinatesJson): MachineMovement
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
     * @return MachineMovement
     */
    public function setCoordinatesLongitude(float $value): MachineMovement
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
     * @return MachineMovement
     */
    public function setCoordinatesLatitude(float $value): MachineMovement
    {
        $coordinates = @json_decode($this->getCoordinatesJson(), true) ?: ["lat" => 0, "lng" => 0];
        $coordinates['lat'] = $value;

        $this->setCoordinatesJson(json_encode($coordinates));
        return $this;
    }



    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "id" => $this->getId(),
            "machineId" => $this->machine ? $this->machine->getId() : 0,
            "machineSerialId" => $this->machine ? $this->machine->getSerialId() : "",
            "subsidiaryId" => $this->subsidiary ? $this->subsidiary->getId() : 0,
            "subsidiaryName" => $this->subsidiary ? $this->subsidiary->getName() : "",
            "customerId" => $this->customer ? $this->customer->getId() : 0,
            "customerFullName" => $this->customer ? $this->customer->getFullName() : 0,
            "direction" => $this->direction,
            "machineStatus" => $this->machineStatus,
            "contractType" => $this->contractType,
            "movementDate" => $this->getMovementDate() ? $this->getMovementDate()->format("Y-m-d") : "",
            "locationAddress" => $this->locationAddress,
            "locationCountry" => $this->locationCountry,
            "locationCity" => $this->locationCity,
            "locationZip" => $this->locationZip,
            "coordinatesLatitude" => $this->getCoordinatesLatitude(),
            "coordinatesLongitude" => $this->getCoordinatesLongitude(),
            "distance" => $this->distance,
            "filesList" => $this->getFilesList(),
            "addedById" => $this->getAddedBy() ? $this->getAddedBy()->getId() : 0,
            "addedByFullName" => $this->getAddedBy() ? $this->getAddedBy()->getFullName() : "",
            "addedDateTime" => $this->addedDateTime ? $this->addedDateTime->format("Y-m-d") : "",
            "reason" => $this->getReason(),
        ];
    }

}
