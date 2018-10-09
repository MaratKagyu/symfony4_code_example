<?php

namespace App\Entity\Customer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CustomerAddress
{
    const ADDRESS_TYPE_BILLING = 'Billing';
    const ADDRESS_TYPE_SHIPPING = 'Shipping';

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id = 0;

    /**
     * @var Customer|null
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="addressList")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $department = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $attentionTo = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $company = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $address = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $city = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $state = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $zip = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $country = "";

    /**
     * @var ArrayCollection|AddressType[]
     * @ORM\ManyToMany(targetEntity="AddressType")
     * @ORM\JoinTable(name="customer_address_types",
     *      joinColumns={@ORM\JoinColumn(name="address_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="address_type_id", referencedColumnName="id")}
     * )
     */
    private $addressTypes;

    /**
     * @var ArrayCollection|AddressType[]
     * @ORM\ManyToMany(targetEntity="AddressType")
     * @ORM\JoinTable(name="customer_default_address_types",
     *      joinColumns={@ORM\JoinColumn(name="address_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="address_type_id", referencedColumnName="id")}
     * )
     */
    private $defaultForAddressTypes;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $coordinatesJson = "";


    public function __construct()
    {
        $this->addressTypes = new ArrayCollection();
        $this->defaultForAddressTypes = new ArrayCollection();
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
     * @return CustomerAddress
     */
    public function setId(int $id): CustomerAddress
    {
        $this->id = $id;
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
     * @return CustomerAddress
     */
    public function setCustomer(?Customer $customer): CustomerAddress
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return string
     */
    public function getDepartment(): string
    {
        return $this->department;
    }

    /**
     * @param string $department
     * @return CustomerAddress
     */
    public function setDepartment(string $department): CustomerAddress
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return string
     */
    public function getAttentionTo(): string
    {
        return $this->attentionTo;
    }

    /**
     * @param string $attentionTo
     * @return CustomerAddress
     */
    public function setAttentionTo(string $attentionTo): CustomerAddress
    {
        $this->attentionTo = $attentionTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     * @return CustomerAddress
     */
    public function setCompany(string $company): CustomerAddress
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return CustomerAddress
     */
    public function setAddress(string $address): CustomerAddress
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return CustomerAddress
     */
    public function setCity(string $city): CustomerAddress
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return CustomerAddress
     */
    public function setState(string $state): CustomerAddress
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @param string $zip
     * @return CustomerAddress
     */
    public function setZip(string $zip): CustomerAddress
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return CustomerAddress
     */
    public function setCountry(string $country): CustomerAddress
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return AddressType[]|ArrayCollection
     */
    public function getAddressTypes()
    {
        return $this->addressTypes;
    }


    /**
     * @return string
     */
    public function getAddressTypesString()
    {
        if (! $this->addressTypes->count()) return "-";

        $addressTypes = array_map(
            function ($addressType) {
                /* @var AddressType $addressType */
                return $addressType->getTypeName();
            },
            $this->addressTypes->toArray()
        );

        sort($addressTypes);
        return implode(", ", $addressTypes);
    }


    /**
     * @param AddressType[]|ArrayCollection $addressTypes
     * @return CustomerAddress
     */
    public function setAddressTypes(ArrayCollection $addressTypes)
    {
        $this->addressTypes = $addressTypes;
        return $this;
    }

    /**
     * @param AddressType $addressType
     * @return CustomerAddress
     */
    public function addAddressType(AddressType $addressType)
    {
        $this->addressTypes->add($addressType);
        return $this;
    }


    /**
     * @param string $addressTypeString
     * @return bool
     */
    public function isAddressType($addressTypeString): bool
    {
        $addressTypeString = mb_strtolower($addressTypeString);

        foreach ($this->addressTypes as $addressType) {
            if (mb_strtolower($addressType->getTypeName()) == $addressTypeString) return true;
        }

        return false;
    }



    /**
     * @return AddressType[]|ArrayCollection
     */
    public function getDefaultForAddressTypes()
    {
        return $this->defaultForAddressTypes;
    }


    /**
     * @param AddressType[]|ArrayCollection $defaultForAddressTypes
     * @return CustomerAddress
     */
    public function setDefaultForAddressTypes(ArrayCollection $defaultForAddressTypes)
    {
        $this->defaultForAddressTypes = $defaultForAddressTypes;
        return $this;
    }


    /**
     * @param AddressType $addressTypes
     * @return CustomerAddress
     */
    public function addDefaultForAddressTypes(AddressType $addressTypes)
    {
        $this->defaultForAddressTypes->add($addressTypes);
        return $this;
    }


    /**
     * @param AddressType $addressTypes
     * @return CustomerAddress
     */
    public function removeDefaultForAddressTypes(AddressType $addressTypes)
    {
        $this->defaultForAddressTypes->removeElement($addressTypes);
        return $this;
    }

    /**
     * @param string $addressTypeString
     * @return bool
     */
    public function isDefaultAddressForType($addressTypeString): bool
    {
        $addressTypeString = mb_strtolower($addressTypeString);

        foreach ($this->defaultForAddressTypes as $addressType) {
            if (mb_strtolower($addressType->getTypeName()) == $addressTypeString) return true;
        }

        return false;
    }


    /**
     * @return string
     * @deprecated
     */
    public function getAddressString(): string
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
                    $this->getAddress(),
                    $this->getCity(),
                    $this->getState(),
                    $this->getZip(),
                    $this->getCountry()
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
    public function getCoordinatesJson(): string
    {
        return $this->coordinatesJson;
    }

    /**
     * @param string $coordinatesJson
     * @return CustomerAddress
     */
    public function setCoordinatesJson(string $coordinatesJson): CustomerAddress
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
     * @return CustomerAddress
     */
    public function setCoordinatesLongitude(float $value): CustomerAddress
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
     * @return CustomerAddress
     */
    public function setCoordinatesLatitude(float $value): CustomerAddress
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
        $addressTypes = array_map(
            function ($addressType) {
                /* @var AddressType $addressType */
                return $addressType->getTypeName();
            },
            $this->addressTypes->toArray()
        );

        sort($addressTypes);

        $defaultForAddressTypes = array_map(
            function ($addressType) {
                /* @var AddressType $addressType */
                return $addressType->getTypeName();
            },
            $this->defaultForAddressTypes->toArray()
        );

        sort($defaultForAddressTypes);

        return [
            "id" => $this->id,
            "customerId" => $this->customer ? $this->customer->getId() : 0,
            "attentionTo" => $this->attentionTo,
            "company" => $this->company,
            "state" => $this->state,
            "address" => $this->address,
            "addressString" => $this->getFullAddress(),
            "city" => $this->city,
            "zip" => $this->zip,
            "country" => $this->country,
            "addressTypes" => $addressTypes,
            "defaultForAddressTypes" => $defaultForAddressTypes,
            "coordinatesLatitude" => $this->getCoordinatesLatitude(),
            "coordinatesLongitude" => $this->getCoordinatesLongitude(),
        ];
    }




}
