<?php

namespace App\Entity\Machine;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MachineBuildLocation
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
    private $location = "";


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
     * @return MachineBuildLocation
     */
    public function setId(int $id): MachineBuildLocation
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @param string $location
     * @return MachineBuildLocation
     */
    public function setLocation(string $location): MachineBuildLocation
    {
        $this->location = $location;
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
     * @return MachineBuildLocation
     */
    public function setStatus(int $status): MachineBuildLocation
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
            "location" => $this->location,
            "status" => $this->status,
        ];
    }
}
