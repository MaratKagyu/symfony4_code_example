<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Customer;

use App\Entity\Customer\{Customer, CustomerBonafide, CustomerFile};
use App\Entity\Order;
use App\Repository\Customer\CustomerRepository;
use App\Service\SpecialAccessProviders\CustomerAccessProvider;
use App\Service\SpecialAccessProviders\OrdersAccessProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class CustomersJsonController extends Controller
{
    /**
     * @param int $id
     * @param CustomerAccessProvider $accessProvider
     * @Route(
     *     "/json/get-sub-customers-stats/{id}",
     *     name="getSubCustomersStatsJson",
     *     requirements={"id": "\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSubCustomersStatsJson(int $id, CustomerAccessProvider $accessProvider)
    {
        /*
         * TODO: SECURITY!! Prevent accessing records the user shouldn't have access to!
         */
        $accessProvider->requiresAccessTo(CustomerAccessProvider::SECTION_CODE);

        /* @var CustomerRepository $customerRepo */
        $customerRepo = $this->getDoctrine()->getRepository(Customer::class);

        return $this->json($customerRepo->loadSubCustomersStats($id));

    }


    /**
     * @param Customer $customer
     * @param CustomerAccessProvider $accessProvider
     * @Route(
     *     "/json/get-customer-files/{id}",
     *     name="getCustomerFilesJson",
     *     requirements={"id": "\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCustomerFilesJson(Customer $customer, CustomerAccessProvider $accessProvider)
    {
        /*
         * TODO: SECURITY!! Prevent accessing records the user shouldn't have access to!
         */
        $accessLevel = $accessProvider->getAccessLevelTo(CustomerAccessProvider::SECTION_CODE);

        $customerFilesRepo = $this->getDoctrine()->getRepository(CustomerFile::class);

        $customersDataArray = array_map(
            function (CustomerFile $file) use ($accessLevel, $customer){
                $infoArray = $file->toInfoArray();
                $actions = [];

                $actions['Download'] = $this->generateUrl(
                    "downloadCustomerFileAction",
                    [
                        "customerId" => $customer->getId(),
                        "fileName" => $file->getFileName(),
                    ]
                );

                $infoArray['actions'] = $actions;

                return $infoArray;
            },
            $customerFilesRepo->findBy([ "customer" => $customer ])

        );

        return $this->json($customersDataArray);
    }


    /**
     * @param Customer $customer
     * @param CustomerAccessProvider $accessProvider
     * @Route(
     *     "/json/get-customer-bonafides/{id}",
     *     name="getCustomerBonafidesJson",
     *     requirements={"id": "\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCustomerBonafidesJson(Customer $customer, CustomerAccessProvider $accessProvider)
    {
        /*
         * TODO: SECURITY!! Prevent accessing records the user shouldn't have access to!
         */
        $accessLevel = $accessProvider->getAccessLevelTo(CustomerAccessProvider::SECTION_CODE);

        $bonafidesRepo = $this->getDoctrine()->getRepository(CustomerBonafide::class);

        $customersDataArray = array_map(
            function (CustomerBonafide $bonafide) use ($accessLevel, $customer){
                $infoArray = $bonafide->toInfoArray();
                $actions = [];

                $actions['Download'] = $this->generateUrl(
                    "downloadCustomerFileAction",
                    [
                        "customerId" => $customer->getId(),
                        "fileName" => $bonafide->getFileName(),
                    ]
                );

                $infoArray['actions'] = $actions;

                return $infoArray;
            },
            $bonafidesRepo->findBy([ "customer" => $customer ])

        );

        return $this->json($customersDataArray);
    }


    /**
     * @param CustomerAccessProvider $accessProvider
     * @Route("/json/get-customers", name="getCustomersJson")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCustomersJson(CustomerAccessProvider $accessProvider)
    {
        $accessLevel = $accessProvider->requiresAccessToTheSection();

        /* @var CustomerRepository $customerRepo */
        $customerRepo = $this->getDoctrine()->getRepository(Customer::class);

        if ($accessLevel >= CustomerAccessProvider::ACL_FULL) {
            $customerList = $customerRepo->findAll();
        } else {
            $customerList = $customerRepo->loadUserAssocBundledCustomers($accessProvider->getUser());
        }


        $customersDataArray = array_map(
            function (Customer $customer) use ($accessLevel){
                $infoArray = $customer->toCustomerPlainArray();

                $actions = [];

                if ($accessLevel) {
                    $actions['View'] = $this->generateUrl(
                        "customerViewPage",
                        [ "customerId" => $customer->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }

                if ($accessLevel >= CustomerAccessProvider::ACL_SUBSIDIARY_FULL) {
                    $actions['Edit'] = $this->generateUrl(
                        "customerEditPage",
                        [ "customerId" => $customer->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }

                $infoArray['actions'] = $actions;

                return $infoArray;
            },
            $customerList
        );

        return $this->json($customersDataArray);
    }


    /**
     * @param int $customerId
     * @Route(
     *     "/json/get-customers/{customerId}",
     *     name="ajaxGetCustomersByIdAction",
     *     requirements={"customerId"="\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ajaxGetCustomersByIdAction(int $customerId)
    {
        /*
         * TODO: SECURITY!! Prevent accessing records the user shouldn't have access to!
         */

        /* @var Customer $customer */
        $customer = $this
            ->getDoctrine()
            ->getRepository(Customer::class)
            ->find($customerId);

        if (! $customer) {
            return $this->json([
                "status" => "error",
                "message" => "customer not found",
                "customerId" => $customerId
            ], 404);
        }


        return $this->json($customer->toCustomerDetailedInfoArray());
    }


    /**
     * @param Customer $customer
     * @param OrdersAccessProvider $accessProvider
     * @Route(
     *     "/json/get-customer-orders/{id}",
     *     name="getCustomerOrdersJson"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCustomerOrdersJson(
        Customer $customer,
        OrdersAccessProvider $accessProvider
    ){
        $accessProvider->requiresAccessTo(CustomerAccessProvider::SECTION_CODE);

        $result = array_map(
            function (Order $order) use ($accessProvider){
                $orderInfo = $order->toOrderInfoArray();

                $actions = [];

                if ($accessProvider->canWriteToOrder($order)) {
                    $actions["Edit"] = $this->generateUrl(
                        "orderEditPage",
                        [ "orderId" => $order->getId() ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }

                $orderInfo["actions"] = $actions;

                return $orderInfo;
            },
            $this->getDoctrine()
                ->getRepository(Order::class)
                ->findBy([
                   "customer" => $customer
                ])
        );

        return $this->json($result);
    }
}
