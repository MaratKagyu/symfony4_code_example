<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/25/2018
 * Time: 6:31 PM
 */

namespace App\Service;

use App\Entity\Customer\Customer;
use App\Entity\Machine\MachineMovement;
use App\Entity\Machine\MachineServiceContract;
use App\Entity\Machine\Ticket;
use App\Entity\Marketing\Material;
use App\Entity\Product;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Class FileUploader
 * @package App\Service
 */
class FileUploader
{
    const ENTITY_TYPE_MACHINE_MOVEMENT = 'machineMovement';
    const ENTITY_TYPE_MACHINE_SERVICE_CONTRACT = 'machine_service_contract';
    const ENTITY_TYPE_CUSTOMER_FILE = 'customerFile';
    const ENTITY_TYPE_TICKET_FILE = 'ticketFile';
    const ENTITY_TYPE_PRODUCT = 'product';
    const ENTITY_TYPE_MATERIAL = 'material';


    /**
     * @var string
     */
    private $projectRootPath = "";


    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->projectRootPath = dirname($kernel->getRootDir()) . "/";
    }

    /**
     * @return string
     */
    public function getProjectRootPath(): string
    {
        return $this->projectRootPath;
    }




    /**
     * @param string $path
     * @param string $originalFileName
     * @return string
     */
    public function getUniqueFileName($path, $originalFileName)
    {
        $counter = 0;

        $fNameArray = explode('.', $originalFileName);
        $fileExt = array_pop($fNameArray);
        $fileMainPart = implode(".", $fNameArray);

        do {
            $newFileName = $fileMainPart . ($counter ? "({$counter})" : "") . "." . $fileExt;
            $counter ++;
        } while (file_exists($path . $newFileName));


        return $newFileName;
    }



    /**
     * @param UploadedFile $file
     * @param string $entityType
     * @param int $entityId
     * @return string
     */
    public function uploadFile(UploadedFile $file, $entityType, $entityId)
    {
        $localDir = $this->getUploadLocation($entityType, $entityId);


        $locationPath = $this->projectRootPath . $localDir;
        if (! file_exists($locationPath)) mkdir($locationPath, 0755, true);

        $fileName = $this->getUniqueFileName($locationPath, $file->getClientOriginalName());

        return $localDir . $file->move($locationPath, $fileName)->getFilename();
    }


    /**
     * @param string $entityType
     * @param int $entityId
     * @return string
     */
    public function getUploadLocation($entityType, $entityId)
    {
        switch ($entityType){
            case self::ENTITY_TYPE_MACHINE_MOVEMENT:
                return MachineMovement::getFilesLocationByMachineId($entityId);

            case self::ENTITY_TYPE_MACHINE_SERVICE_CONTRACT:
                return MachineServiceContract::getFilesLocationById($entityId);

            case self::ENTITY_TYPE_CUSTOMER_FILE:
                return Customer::getFilesLocationByCustomerId($entityId);

            case self::ENTITY_TYPE_TICKET_FILE:
                return Ticket::getFilesLocationByTicketId($entityId);

            case self::ENTITY_TYPE_PRODUCT:
                return Product::getFilesLocationByProductId($entityId);

            case self::ENTITY_TYPE_MATERIAL:
                return Material::getFilesLocationByMaterialId($entityId);
        }

        throw new AccessDeniedException("Unsupported entity type");

    }


    /**
     * @param string $oldLocalPath - old local path
     * @param string $newLocalPath - new local path
     */
    public function renameFile($oldLocalPath, $newLocalPath)
    {
        $oldPath = $this->getProjectRootPath() . $oldLocalPath;
        $newPath = $this->getProjectRootPath() . $newLocalPath;

        $newPathDir = dirname($newPath);
        if (! file_exists($newPathDir)) {
            mkdir($newPathDir, 0755, true);
        }

        rename($oldPath, $newPath);
    }
}