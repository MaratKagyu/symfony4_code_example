<?php

namespace App\Repository\Machine;

use App\Entity\Machine\Machine;
use App\Entity\Subsidiary;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Machine|null find($id, $lockMode = null, $lockVersion = null)
 * @method Machine|null findOneBy(array $criteria, array $orderBy = null)
 * @method Machine[]    findAll()
 * @method Machine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MachineRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Machine::class);
    }

    /**
     * @param User $user
     * @return Machine[]
     */
    function loadUserAssocBundledMachines(User $user): array
    {
        $whereString = "";

        $parameters = [];

        // Load associated subsidiaries
        $subsidiaryIds = [];
        foreach ($user->getSubsidiaryListByType(Subsidiary::TYPE_SUBSIDIARY) as $subsidiary) {
            $subsidiaryIds[] = $subsidiary->getId();
        }

        // Load machines, associated with the partners
        $machineIds = [];
        foreach ($user->getPartnersList() as $partner) {
            foreach ($partner->getAssociatedMachines() as $machine) {
                $machineIds[] = $machine->getId();
            }
        }

        // Load associated customers
        $customerIds = [];
        if ($user->getCustomer()) {
            $customerIds[] = $user->getCustomer()->getId();
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
                SELECT " . "machine, customer, subsidiary, machineType FROM \\App\\Entity\\Machine\\Machine machine
                LEFT JOIN machine.currentHolder customer
                LEFT JOIN machine.subsidiary subsidiary
                LEFT JOIN machine.types machineType
                {$whereString}
                ORDER BY machine.serialId
                "
            )
            ->setParameters($parameters)->getResult()
        ;
    }
}
