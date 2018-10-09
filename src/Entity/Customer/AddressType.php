<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AddressType
{
    const STATUS_ARCHIVED = 0;
    const STATUS_AVAILABLE = 1;

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id = 0;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $typeName = "";


    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_AVAILABLE;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AddressType
     */
    public function setId(int $id): AddressType
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->typeName;
    }

    /**
     * @param string $typeName
     * @return AddressType
     */
    public function setTypeName(string $typeName): AddressType
    {
        $this->typeName = $typeName;
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
     * @return AddressType
     */
    public function setStatus(int $status): AddressType
    {
        $this->status = $status;
        return $this;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            "id" => $this->getId(),
            "typeName" => $this->getTypeName(),
            "status" => $this->getStatus(),
        ];
    }
}
