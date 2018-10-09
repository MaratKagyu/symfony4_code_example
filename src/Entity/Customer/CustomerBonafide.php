<?php

namespace App\Entity\Customer;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CustomerBonafide
{

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id = 0;

    /**
     * @var Customer|null
     * @ORM\ManyToOne(targetEntity="Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $fileName = "";

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $name = "";

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User")
     * @ORM\JoinColumn(name="uploaded_by_uid", referencedColumnName="id")
     */
    private $uploadedBy;


    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime")
     */
    private $uploadDateTime;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="date")
     */
    private $expiryDate;


    public function __construct()
    {
        $this->uploadDateTime = new \DateTime();
        $this->expiryDate = new \DateTime();
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
     * @return CustomerBonafide
     */
    public function setId(int $id): CustomerBonafide
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
     * @return CustomerBonafide
     */
    public function setCustomer(?Customer $customer): CustomerBonafide
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return preg_replace('#^.*/([^/]+)$#isu', "$1", $this->fileName);
    }

    /**
     * @param string $fileName
     * @return CustomerBonafide
     */
    public function setFileName(string $fileName): CustomerBonafide
    {
        $this->fileName = preg_replace('#^.*/([^/]+)$#isu', "$1", $fileName);
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
     * @return CustomerBonafide
     */
    public function setName(string $name): CustomerBonafide
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUploadedBy(): ?User
    {
        return $this->uploadedBy;
    }

    /**
     * @param User|null $uploadedBy
     * @return CustomerBonafide
     */
    public function setUploadedBy(?User $uploadedBy): CustomerBonafide
    {
        $this->uploadedBy = $uploadedBy;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUploadDateTime(): ?\DateTime
    {
        return $this->uploadDateTime;
    }

    /**
     * @param \DateTime|null $uploadDateTime
     * @return CustomerBonafide
     */
    public function setUploadDateTime(?\DateTime $uploadDateTime): CustomerBonafide
    {
        $this->uploadDateTime = $uploadDateTime;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiryDate(): ?\DateTime
    {
        return $this->expiryDate;
    }

    /**
     * @param \DateTime|null $expiryDate
     * @return CustomerBonafide
     */
    public function setExpiryDate(?\DateTime $expiryDate): CustomerBonafide
    {
        $this->expiryDate = $expiryDate;
        return $this;
    }


    /**
     * @param string $expiryDateString
     * @return CustomerBonafide
     */
    public function setExpiryDateString($expiryDateString): CustomerBonafide
    {
        $this->expiryDate = \DateTime::createFromFormat("d/m/Y", $expiryDateString);
        return $this;
    }




    /**
     * @param string $locationDir
     * @return string
     */
    public function getFileSizeText($locationDir)
    {
        $fullFilePath = $locationDir . $this->getFileName();

        if (! file_exists($fullFilePath)) return "-";

        $size = filesize($fullFilePath);

        if ($size > 1000000) {
            $sizeString = (round($size / 100000) / 10) . " MB";
        } else if ($size > 1000) {
            $sizeString = (round($size / 100) / 10) . " KB";
        } else {
            $sizeString = $size . " B";
        }

        return $sizeString;
    }


    /**
     * @return string
     */
    public function getFileExt()
    {
        $fileNameArray = explode('.', $this->fileName);
        return array_pop($fileNameArray);
    }



    /**
     * @return array
     */
    public function toInfoArray()
    {
        return [
            "id" => $this->getId(),
            "customerId" => $this->getCustomer() ? $this->getCustomer()->getId() : 0,
            "fileName" => $this->getFileName(),
            "name" => $this->getName(),
            "uploadedById" => $this->getUploadedBy() ? $this->getUploadedBy()->getId() : 0,
            "uploadedByFullName" => $this->getUploadedBy() ? $this->getUploadedBy()->getFullName() : "",
            "uploadDateTime" => (
                $this->getUploadDateTime() ? $this->getUploadDateTime()->format("Y-m-d") : ""
            ),
            "expiryDate" => (
                $this->getExpiryDate() ? $this->getExpiryDate()->format("Y-m-d") : ""
            ),
        ];
    }
}
