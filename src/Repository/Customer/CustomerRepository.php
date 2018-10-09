<?php

namespace App\Repository\Customer;

use App\Entity\Customer\Customer;
use App\Entity\Subsidiary;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


/**
 * Class CustomerRepository
 * @package App\Repository\Customer
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends ServiceEntityRepository
{
    /**
     * @var string[]
     */
    private static $_generatedCustomerIds = [];

    /**
     * CustomerRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * @param string $customerId
     * @param int $excludeUserId
     * @return null|Customer
     */
    public function findByCustomerId($customerId, $excludeUserId = 0)
    {
        $customerId = str_replace(['%'], "", $customerId);

        $query = $this->getEntityManager()->createQuery(
            "
            SELECT " . "appCustomer FROM App\\Entity\\Customer\\Customer appCustomer
            WHERE (appCustomer.customerId = :customerId)
            AND (appCustomer.id != :excludeUserId)
            "
        )->setParameters([
            "customerId" => $customerId,
            "excludeUserId" =>  (int)$excludeUserId
        ])->getResult();

        if (! $query) return null;

        return $query[0];
    }


    /**
     * @param string $searchFilter
     * @param int $status (if == '-1', it means to skip status parameter check)
     * @return int
     * @throws
     */
    public function getFilteredCustomersCount($searchFilter, $status)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select("count(customer.id)");
        $query->from("App\\Entity\\Customer\\Customer", "customer");

        $queryParameters = [];

        if ($searchFilter) {
            $queryParameters["searchFilter"] = "%" . $searchFilter . "%";
            $query->where(
                "
                customer.companyName LIKE :searchFilter
                OR customer.customerId LIKE :searchFilter
                OR customer.vatNumber LIKE :searchFilter
                "
            );
        }

        if ($status >= 0){
            $queryParameters["status"] = $status;
            $query->andWhere("customer.status = :status");
        }


        return (int)$query->getQuery()->setParameters($queryParameters)->getSingleScalarResult();
    }



    /**
     * @param string $searchFilter
     * @param int $status (if == '-1', it means to skip status parameter check)
     * @param int $pageNumber
     * @param int $numberOnPage
     * @param string $sortOrder - example:
     *     "-name" == "name DESC",
     *     "+description" = "description ASC",
     *     "price" = "price ASC",
     * @return Customer[]
     */
    public function getFilteredCustomersList(
        $searchFilter,
        $status,
        $pageNumber,
        $numberOnPage,
        $sortOrder = "+companyName"
    ){
        $queryParameters = [];
        $whereSection = "";

        if ($searchFilter) {
            $queryParameters["searchFilter"] = "%" . $searchFilter . "%";

            $whereSection .= (
                "(
                customer.companyName LIKE :searchFilter
                OR customer.customerId LIKE :searchFilter
                OR customer.vatNumber LIKE :searchFilter
                )"
            );
        }

        if ($status >= 0){
            if ($whereSection) $whereSection .= " AND ";

            $queryParameters["status"] = $status;
            $whereSection .= " user.status = :status ";
        }




        // Order
        // $orderSection = "";
        preg_match('#^([-+]?)(.*)$#isu', $sortOrder, $orderPregResult);


        $orderDirection = ($orderPregResult[1] == '-') ? "DESC" : "ASC";
        $orderColumn = $orderPregResult[2];

        switch ($orderColumn) {
            case "id":
            case "customerId":
            case "status":
            case "lastUpdatedDateTime":
                $orderSection = "customer.{$orderColumn} {$orderDirection}";
                break;

            case "subsidiary":
                $orderSection = "subsidiary.name {$orderDirection}";
                break;

            case "companyName":
            default:
                $orderSection = "customer.companyName {$orderDirection}";
        }


        // Preparing and running the query
        $result = $this->getEntityManager()
            ->createQuery(
                "SELECT " . "customer, subsidiary FROM App\\Entity\\Customer\\Customer customer
                LEFT JOIN customer.subsidiary subsidiary
                " . ($whereSection ? "WHERE {$whereSection}" : "")  . "
                ORDER BY {$orderSection}
                "
            )
            ->setMaxResults($numberOnPage)
            ->setFirstResult(($pageNumber - 1) * $numberOnPage)

            ->setParameters($queryParameters)->getResult();



        return $result;


    }


    /**
     * @param $customerId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function loadSubCustomersStats($customerId)
    {
        /*
         * Company name
         * Company code
         *
         * Orders
         * Machines
         * Contracts
         * Tickets
         */
        $getStatsQuery = $this->getEntityManager()->getConnection()->prepare(
            "
            SELECT 
                customer.id,
                customer.company_name as companyName,
                customer.customer_id as customerId,
                COALESCE(orders.order_count, 0) as orderCount,
                COALESCE(machines.machine_count, 0) as machineCount,
                COALESCE(contracts.contract_count, 0) as contractCount,
                COALESCE(tickets.ticket_count, 0) as ticketCount
            
            FROM customer 
            LEFT JOIN 
                (SELECT orders.customer_id, COUNT(*) as order_count FROM orders GROUP BY orders.customer_id) 
                as orders ON orders.customer_id=customer.id
                
            LEFT JOIN 
                (SELECT machine.current_holder_id as customer_id, COUNT(*) as machine_count FROM machine GROUP BY customer_id) 
                as machines ON machines.customer_id=customer.id
                
            LEFT JOIN 
                (SELECT contracts.customer_id, COUNT(*) as contract_count FROM machine_service_contract as contracts GROUP BY customer_id) 
                as contracts ON contracts.customer_id=customer.id
                
            LEFT JOIN 
                (SELECT ticket.customer_id, COUNT(*) as ticket_count FROM ticket GROUP BY customer_id) 
                as tickets ON tickets.customer_id=customer.id
                
            WHERE customer.parent_id=:id
            ORDER BY companyName
            "
        );

        $getStatsQuery->execute(['id' => (int)$customerId]);

        return $getStatsQuery->fetchAll();
    }


    /**
     * @param Subsidiary $subsidiary
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getNewCustomerId(Subsidiary $subsidiary): string
    {
        $maxCustomerQuery = $this->getEntityManager()
            ->getConnection()
            ->prepare(
                "
                SELECT 
                    customer_id 
                FROM customer 
                WHERE subsidiary_id=:subsidiary_id
                ORDER BY customer_id 
                "
            );

        $maxCustomerQuery->execute(['subsidiary_id' => $subsidiary->getId()]);
        $recordList = $maxCustomerQuery->fetchAll();

        $maxValue = 0;

        foreach ($recordList as $record) {
            $customerId = $record['customer_id'] ?? '';
            if (preg_match('#\d+#isu', $customerId,$pregResult)) {
                $value = (int)$pregResult[0];
                if ($value > $maxValue) {
                    $maxValue = $value;
                }
            }
        }

        foreach (self::$_generatedCustomerIds as $customerId) {
            if (preg_match('#\d+#isu', $customerId,$pregResult)) {
                $value = (int)$pregResult[0];
                if ($value > $maxValue) {
                    $maxValue = $value;
                }
            }
        }

        $newCustomerId = $subsidiary->getShortCode() . sprintf('%05d', $maxValue + 1);
        self::$_generatedCustomerIds[] = $newCustomerId;

        return $newCustomerId;
    }

    /**
     * @param User $user
     * @return Customer[]
     */
    function loadUserAssocBundledCustomers(User $user): array
    {
        $whereString = "";

        $parameters = [];

        // Load associated subsidiaries
        $subsidiaryIds = [];
        foreach ($user->getSubsidiaryListByType(Subsidiary::TYPE_SUBSIDIARY) as $subsidiary) {
            $subsidiaryIds[] = $subsidiary->getId();
        }

        // Load associated customers (directly linked and via partners->machines->customers)
        $customerIds = [];
        if ($user->getCustomer()) {
            $customerIds[] = $user->getCustomer()->getId();
        }

        foreach ($user->getPartnersList() as $partner) {
            foreach ($partner->getAssociatedMachines() as $machine) {
                if ($machine->getCurrentHolder()) {
                    $customerIds[] = $machine->getCurrentHolder()->getId();
                }
            }
        }

        // If the user isn't associated with any subsidiary and customer, then we return an empty list
        if (! (count($customerIds) || count($subsidiaryIds))) {
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

        if ($whereString) {
            $whereString = "WHERE " . $whereString;
        }

        return $this->getEntityManager()
            ->createQuery(
                "
                SELECT " . "customer, subsidiary FROM \\App\\Entity\\Customer\\Customer customer
                LEFT JOIN customer.subsidiary subsidiary
                {$whereString}
                ORDER BY customer.customerId
                "
            )
            ->setParameters($parameters)->getResult()
        ;
    }
}

