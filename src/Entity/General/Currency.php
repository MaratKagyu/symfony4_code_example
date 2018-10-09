<?php

namespace App\Entity\General;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @UniqueEntity("code")
 */
class Currency
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
     * @var string - example: USD
     * @ORM\Column(type="text")
     */
    private $code = "";

    /**
     * @var string - example: "$"
     * @ORM\Column(type="text")
     */
    private $symbol = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $decimalPoint = ".";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $thousandsSeparator = ",";


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
     * @return Currency
     */
    public function setId(int $id): Currency
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Currency
     */
    public function setCode(string $code): Currency
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     * @return Currency
     */
    public function setSymbol(string $symbol): Currency
    {
        $this->symbol = $symbol;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Currency
     */
    public function setDescription(string $description): Currency
    {
        $this->description = $description;
        return $this;
    }


    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->getCode() . " - " . $this->getDescription();
    }

    /**
     * @return string
     */
    public function getDecimalPoint(): string
    {
        return $this->decimalPoint;
    }

    /**
     * @param string $decimalPoint
     * @return Currency
     */
    public function setDecimalPoint(string $decimalPoint): Currency
    {
        $this->decimalPoint = $decimalPoint;
        return $this;
    }

    /**
     * @return string
     */
    public function getThousandsSeparator(): string
    {
        return $this->thousandsSeparator;
    }

    /**
     * @param string $thousandsSeparator
     * @return Currency
     */
    public function setThousandsSeparator(string $thousandsSeparator): Currency
    {
        $this->thousandsSeparator = $thousandsSeparator;
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
     * @return string
     */
    public function getStatusDescription(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE: return "Active";
            case self::STATUS_INACTIVE: return "Inactive";
            default:
                return "n/a";
        }
    }

    /**
     * @param int $status
     * @return Currency
     */
    public function setStatus(int $status): Currency
    {
        $this->status = $status;
        return $this;
    }


    /**
     * @param number $number
     * @return string
     */
    public function format($number): string
    {
        if ( $number >= 0 ) {
            return (
                $this->getSymbol() .
                number_format($number, 0, $this->getDecimalPoint(), $this->getThousandsSeparator())
            );
        } else {
            return (
                "-" . $this->getSymbol() .
                number_format(abs($number), 0, $this->getDecimalPoint(), $this->getThousandsSeparator())
            );
        }

    }


    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id" => $this->getId(),
            "code" => $this->getCode(),
            "symbol" => $this->getSymbol(),
            "description" => $this->getDescription(),
            "decimalPoint" => $this->getDecimalPoint(),
            "thousandsSeparator" => $this->getThousandsSeparator(),
            "status" => $this->getStatus(),
        ];
    }
}
