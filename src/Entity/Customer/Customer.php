<?php

namespace App\Entity\Customer;

use App\Entity\General\PricingTier;
use App\Entity\Subsidiary;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Customer\CustomerRepository")
 * @UniqueEntity("customerId")
 */
class Customer
{

    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id = 0;

    /**
     * @var Subsidiary|null
     * @ORM\ManyToOne(targetEntity="App\Entity\Subsidiary")
     * @ORM\JoinColumn(name="subsidiary_id", referencedColumnName="id")
     */
    private $subsidiary;

    /**
     * @var Customer|null
     * @ORM\ManyToOne(targetEntity="Customer")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $companyName = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $customerId = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $vatNumber = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $phoneNumber = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $faxNumber = "";

    /**
     * @var PricingTier|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\General\PricingTier")
     * @ORM\JoinColumn(name="pricing_tier_id", referencedColumnName="id")
     */
    private $pricingTier;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $lastUpdatedDateTime;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_ACTIVE;

    /**
     * @var CustomerAddress[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="CustomerAddress", mappedBy="customer")
     */
    private $addressList;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="\App\Entity\User\User", mappedBy="customer")
     */
    private $contactsList;

    /**
     * Customer constructor.
     */
    public function __construct()
    {
        $this->addressList = new ArrayCollection();
        $this->contactsList = new ArrayCollection();
        $this->lastUpdatedDateTime = new \DateTime;
    }


    /**
     * @param int $customerId
     * @return string
     */
    public static function getFilesLocationByCustomerId(int $customerId): string
    {
        $idString = sprintf('%03d', $customerId);

        return (
            "private/upload/customer_files/" .
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
     * @return Customer
     */
    public function setId(int $id): Customer
    {
        $this->id = $id;
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
     * @return Customer
     */
    public function setSubsidiary(?Subsidiary $subsidiary): Customer
    {
        $this->subsidiary = $subsidiary;
        return $this;
    }

    /**
     * @return Customer|null
     */
    public function getParent(): ?Customer
    {
        return $this->parent;
    }

    /**
     * @param Customer|null $parent
     * @return Customer
     */
    public function setParent(?Customer $parent): Customer
    {
        $this->parent = $parent;
        return $this;
    }



    /**
     * @return string
     */
    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     * @return Customer
     */
    public function setCompanyName(string $companyName): Customer
    {
        $this->companyName = $companyName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     * @return Customer
     */
    public function setCustomerId(string $customerId): Customer
    {
        $this->customerId = $customerId;
        return $this;
    }


    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->customerId . " - " . $this->getCompanyName();
    }


    /**
     * @return string
     */
    public function getVatNumber(): string
    {
        return $this->vatNumber;
    }

    /**
     * @param string $vatNumber
     * @return Customer
     */
    public function setVatNumber(string $vatNumber): Customer
    {
        $this->vatNumber = $vatNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return Customer
     */
    public function setPhoneNumber(string $phoneNumber): Customer
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getFaxNumber(): string
    {
        return $this->faxNumber;
    }

    /**
     * @param string $faxNumber
     * @return Customer
     */
    public function setFaxNumber(string $faxNumber): Customer
    {
        $this->faxNumber = $faxNumber;
        return $this;
    }

    /**
     * @return PricingTier|null
     */
    public function getPricingTier(): ?PricingTier
    {
        return $this->pricingTier;
    }

    /**
     * @param PricingTier|null $pricingTier
     * @return Customer
     */
    public function setPricingTier(?PricingTier $pricingTier): Customer
    {
        $this->pricingTier = $pricingTier;
        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getLastUpdatedDateTime(): \DateTime
    {
        return $this->lastUpdatedDateTime;
    }

    /**
     * @param \DateTime $lastUpdatedDateTime
     * @return Customer
     */
    public function setLastUpdatedDateTime(\DateTime $lastUpdatedDateTime): Customer
    {
        $this->lastUpdatedDateTime = $lastUpdatedDateTime;
        return $this;
    }

    /**
     * @return Customer
     */
    public function updateLastUpdatedDateTime(): Customer
    {
        $this->lastUpdatedDateTime = new \DateTime();
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
     * @return Customer
     */
    public function setStatus(int $status): Customer
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatusDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE: return "Active";
            case self::STATUS_DISABLED: return "Disabled";
        }

        return "n/a";
    }


    /**
     * @return CustomerAddress[]|ArrayCollection
     */
    public function getAddressList()
    {
        return $this->addressList;
    }


    /**
     * @return CustomerAddress[]
     */
    public function getSortedAddressList()
    {
        $addressList = $this->addressList->toArray();
        if (! count($addressList)) return [];

        usort(
            $addressList,
            function (CustomerAddress $a1, CustomerAddress $a2) {
                $dtCount1 = $a1->getDefaultForAddressTypes()->count();
                $dtCount2 = $a2->getDefaultForAddressTypes()->count();

                $tCount1 = $a1->getAddressTypes()->count();
                $tCount2 = $a2->getAddressTypes()->count();

                if ($dtCount1 > $dtCount2) return -1;
                if ($dtCount1 < $dtCount2) return 1;

                if ($tCount1 > $tCount2) return -1;
                if ($tCount1 < $tCount2) return 1;

                return 0;
            }
        );

        return $addressList;

    }



    /**
     * @param int $addressId
     * @return CustomerAddress|null
     */
    public function getAddressById($addressId): ?CustomerAddress
    {
        foreach ($this->addressList->toArray() as $address) {
            /* @var CustomerAddress $address*/
            if ($address->getId() == $addressId) return $address;
        }

        return null;
    }

    /**
     * @param string $preferredType
     * @return CustomerAddress|null
     */
    public function getAddressTypeRecord($preferredType): ?CustomerAddress
    {
        $addressList = $this->getAddressList()->toArray();

        if (! count($addressList)) return null;

        usort(
            $addressList,
            function (CustomerAddress $a1, CustomerAddress $a2) use ($preferredType){
                if ($a1->isDefaultAddressForType($preferredType)) return -1;
                if ($a2->isDefaultAddressForType($preferredType)) return 1;

                if ($a1->isAddressType($preferredType) && $a2->isAddressType($preferredType)) return -0;
                if ($a1->isAddressType($preferredType)) return -1;
                if ($a2->isAddressType($preferredType)) return 1;

                return ($a1->getId() > $a2->getId() ? -1 : 1);
            }
        );

        return array_shift($addressList);
    }


    /**
     * @param CustomerAddress[]|ArrayCollection $addressList
     * @return Customer
     */
    public function setAddressList($addressList): Customer
    {
        $this->addressList = $addressList;
        return $this;
    }


    /**
     * @param CustomerAddress $address
     * @return Customer
     */
    public function addAddress($address): Customer
    {
        $this->addressList->add($address);
        return $this;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getContactsList()
    {
        return $this->contactsList;
    }

    /**
     * @return User[]
     */
    public function getSortedContactsList()
    {
        $rolesList = $this->contactsList->toArray();

        if (! count($rolesList)) return [];

        usort(
            $rolesList,
            function (User $c1, User $c2) {
                $prCount1 = $c1->getPrimaryContactRoles()->count();
                $prCount2 = $c2->getPrimaryContactRoles()->count();

                $rCount1 = $c1->getContactRoles()->count();
                $rCount2 = $c2->getContactRoles()->count();

                if ($prCount1 > $prCount2) return -1;
                if ($prCount1 < $prCount2) return 1;

                if ($rCount1 > $rCount2) return -1;
                if ($rCount1 < $rCount2) return 1;

                return 0;
            }
        );


        return $rolesList;
    }

    /**
     * @param User[]|ArrayCollection $contactsList
     * @return Customer
     */
    public function setContactsList($contactsList):Customer
    {
        $this->contactsList = $contactsList;
        return $this;
    }

    /**
     * @param User $contact
     * @return Customer
     */
    public function addContact($contact):Customer
    {
        $this->contactsList->add($contact);
        return $this;
    }


    /**
     * @param int $userId
     * @return User|null
     */
    public function getContactById($userId): ?User
    {
        foreach ($this->contactsList->toArray() as $contact) {
            /* @var User $contact*/
            if ($contact->getId() == $userId) return $contact;
        }

        return null;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "subsidiaryId" => $this->subsidiary ? $this->subsidiary->getId() : 0,
            "parentId" => $this->parent ? $this->parent->getId() : 0,
            "customerId" => $this->customerId,
            "companyName" => $this->companyName,
            "vatNumber" => $this->vatNumber,
            "phoneNumber" => $this->phoneNumber,
            "faxNumber" => $this->faxNumber,
            "pricingTierName" => $this->pricingTier ? $this->pricingTier->getName() : "",
            "pricingTierId" => $this->pricingTier ? $this->pricingTier->getId() : 0,
            "status" => $this->status,
            // "createdDateTime" => $this->lastUpdatedDateTime ? $this->lastUpdatedDateTime->format("d/m/Y") : "",
        ];
    }

    /**
     * @return array
     */
    public function toCustomerDetailedInfoArray(): array
    {
        $contactsListArray = [];
        foreach ($this->contactsList as $contact) {
            if (! $contact->isEnabled()) continue;
            $contactsListArray[] = $contact->toContactInfoArray();
        }


        $dataArray = [
            "id" => $this->id,
            "subsidiaryId" => $this->subsidiary ? $this->subsidiary->getId() : 0,
            "subsidiaryName" => $this->subsidiary ? $this->subsidiary->getName() : "",
            "parentId" => $this->getParent() ? $this->getParent()->getId() : 0,
            "parentName" => $this->getParent() ? $this->getParent()->getCompanyName() : "",
            "parentFullName" => $this->getParent() ? $this->getParent()->getFullName() : "",
            "customerId" => $this->customerId,
            "companyName" => $this->companyName,
            "fullName" => $this->getFullName(),
            "vatNumber" => $this->vatNumber,
            "phoneNumber" => $this->phoneNumber,
            "faxNumber" => $this->faxNumber,
            "pricingTierName" => $this->pricingTier ? $this->pricingTier->getName() : "",
            "pricingTierId" => $this->pricingTier ? $this->pricingTier->getId() : 0,
            "status" => $this->getStatus(),

            "contactsList" => $contactsListArray,

            "addressList" => array_map(
                function (CustomerAddress $address) {
                    return $address->toArray();
                },
                $this->getAddressList()->toArray()
            ),

            "lastUpdatedDateTime" => (
                $this->lastUpdatedDateTime
                    ? $this->lastUpdatedDateTime->format("Y-m-d")
                    : ""
            )
        ];

        return $dataArray;
    }


    /**
     * @return array
     */
    public function toCustomerPlainArray(): array
    {
        $dataArray = [
            "id" => $this->id,
            "subsidiaryId" => $this->subsidiary ? $this->subsidiary->getId() : 0,
            "subsidiaryName" => $this->subsidiary ? $this->subsidiary->getName() : "",
            "parentId" => $this->parent ? $this->parent->getId() : 0,
            "customerId" => $this->customerId,
            "companyName" => $this->companyName,
            "fullName" => $this->getFullName(),
            "vatNumber" => $this->vatNumber,
            "phoneNumber" => $this->phoneNumber,
            "faxNumber" => $this->faxNumber,
            "pricingTierName" => $this->pricingTier ? $this->pricingTier->getName() : "",
            "pricingTierId" => $this->pricingTier ? $this->pricingTier->getId() : 0,
            "status" => $this->getStatus(),

            "lastUpdatedDateTime" => (
                $this->lastUpdatedDateTime
                    ? $this->lastUpdatedDateTime->format("Y-m-d")
                    : ""
            )
        ];

        return $dataArray;
    }


    /**
     * Implemented in order to allow array_unique function to work with arrays of customers
     * @return string
     */
    public function __toString(): string
    {
        return $this->getId() . '';
    }
}
