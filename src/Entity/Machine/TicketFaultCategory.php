<?php

namespace App\Entity\Machine;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TicketFaultCategory
{
    const STATUS_UNAVAILABLE = 0;
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
    private $category = "";

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
     * @return TicketFaultCategory
     */
    public function setId(int $id): TicketFaultCategory
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return TicketFaultCategory
     */
    public function setCategory(string $category): TicketFaultCategory
    {
        $this->category = $category;
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
     * @return TicketFaultCategory
     */
    public function setStatus(int $status): TicketFaultCategory
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
            "id" => $this->id,
            "category" => $this->category,
            "status" => $this->status,
        ];
    }
}
