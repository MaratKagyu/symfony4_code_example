<?php

namespace App\Entity\General;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PricingTier
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

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
    private $name = "";

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status = self::STATUS_ACTIVE;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return PricingTier
     */
    public function setId(int $id): PricingTier
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PricingTier
     */
    public function setName(string $name): PricingTier
    {
        $this->name = $name;
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
     * @return PricingTier
     */
    public function setStatus(int $status): PricingTier
    {
        $this->status = $status;
        return $this;
    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id" => $this->getId(),
            "name" => $this->getName(),
            "status" => $this->getStatus(),
        ];
    }

}
