<?php

namespace App\Repository\Machine;

use App\Entity\Machine\MachineServiceContract;
use App\Entity\Subsidiary;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class MachineServiceContractRepository
 * @package App\Repository\Machine
 * @method MachineServiceContract|null find($id, $lockMode = null, $lockVersion = null)
 * @method MachineServiceContract|null findOneBy(array $criteria, array $orderBy = null)
 * @method MachineServiceContract[]    findAll()
 * @method MachineServiceContract[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MachineServiceContractRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MachineServiceContract::class);
    }

    /**
     * @return MachineServiceContract[]
     */
    public function loadActiveContracts()
    {
        return $this->getEntityManager()
            ->createQuery(
                "
              SELECT " . "contract
              FROM \\App\\Entity\\Machine\\MachineServiceContract contract              
              WHERE 
                  contract.endDate > :endDate
                  AND contract.status IN (:statuses)
              "
            )
            ->setParameters([
                "endDate" => new \DateTime(),
                "statuses" => [
                    MachineServiceContract::STATUS_ACTIVE
                ]
            ])
            ->getResult();
    }

    /**
     * @param User $user
     * @return MachineServiceContract[]
     */
    function loadUserAssocBundledContracts(User $user): array
    {
        $whereString = "";

        $parameters = [];

        // Load associated subsidiaries
        $subsidiaryIds = [];
        foreach ($user->getSubsidiaryListByType(Subsidiary::TYPE_SUBSIDIARY) as $subsidiary) {
            $subsidiaryIds[] = $subsidiary->getId();
        }

        // Load associated customers and machines (directly linked and via partners->machines->customers)
        $customerIds = [];
        $machineIds = [];
        if ($user->getCustomer()) {
            $customerIds[] = $user->getCustomer()->getId();
        }

        foreach ($user->getPartnersList() as $partner) {
            foreach ($partner->getAssociatedMachines() as $machine) {
                $machineIds[] = $machine->getId();
                if ($machine->getCurrentHolder()) {
                    $customerIds[] = $machine->getCurrentHolder()->getId();
                }
            }
        }

        // If the user isn't associated with any subsidiary and customer, then we return an empty list
        if (! (count($customerIds) || count($subsidiaryIds) || count($machineIds))) {
            return [];
        }


        // Prepare Query
        if (count($subsidiaryIds)) {
            if ($whereString) $whereString .= " AND ";

            $whereString .= "subsidiary.id IN (:subsidiaryIds)";
            $parameters['subsidiaryIds'] = $subsidiaryIds;
        }

        if (count($customerIds)) {
            if ($whereString) $whereString .= " OR ";

            $whereString .= "customer.id IN (:customerIds)";
            $parameters['customerIds'] = $customerIds;
        }

        if (count($machineIds)) {
            if ($whereString) $whereString .= " OR ";

            $whereString .= "machine.id IN (:machineIds)";
            $parameters['machineIds'] = $machineIds;
        }

        if ($whereString) {
            $whereString = "WHERE " . $whereString;
        }

        return $this->getEntityManager()
            ->createQuery(
                "
                SELECT " . "
                    contract, subsidiary, machine, customer
                FROM \\App\\Entity\\Machine\\MachineServiceContract contract
                LEFT JOIN contract.subsidiary subsidiary
                LEFT JOIN contract.machine machine
                LEFT JOIN contract.customer customer
                {$whereString}
                ORDER BY contract.contractNumber
                "
            )
            ->setParameters($parameters)->getResult()
        ;
    }
}
