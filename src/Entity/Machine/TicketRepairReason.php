<?php

namespace App\Entity\Machine;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class TicketRepairReason
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
    private $reason = "";

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
     * @return TicketRepairReason
     */
    public function setId(int $id): TicketRepairReason
    {
        $this->id = $id;
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
     * @return TicketRepairReason
     */
    public function setReason(string $reason): TicketRepairReason
    {
        $this->reason = $reason;
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
     * @return TicketRepairReason
     */
    public function setStatus(int $status): TicketRepairReason
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
            "reason" => $this->reason,
            "status" => $this->status,
        ];
    }
}
