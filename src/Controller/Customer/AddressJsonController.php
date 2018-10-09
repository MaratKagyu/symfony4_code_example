<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Customer;

use App\Entity\Customer\Customer;

use App\Entity\Customer\CustomerAddress;
use App\Forms\Customer\CustomerAddressForm;
use App\Service\GoogleMaps;
use App\Service\SpecialAccessProviders\CustomerAccessProvider;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class AddressJsonController extends Controller
{
    /**
     * @param Customer $customer
     * @param CustomerAccessProvider $accessProvider
     * @Route(
     *     "/json/get-customer-addresses/{id}",
     *     name="getCustomerAddressesJson",
     *     requirements={"id": "\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCustomerAddressesJson(Customer $customer, CustomerAccessProvider $accessProvider)
    {
        $accessProvider->readAccessRequired($customer);

        $addressesRepo = $this->getDoctrine()->getRepository(CustomerAddress::class);

        $customersDataArray = array_map(
            function (CustomerAddress $address) use ($accessProvider, $customer){
                $infoArray = $address->toArray();
                $actions = [];

                if ($accessProvider->isWritable($customer)) {
                    $actions['Edit'] = $this->generateUrl(
                        "editCustomersAddressPage",
                        [
                            "customerId" => $customer->getId(),
                            "addressId" => $address->getId(),
                        ]
                    );
                }

                $infoArray['actions'] = $actions;

                return $infoArray;
            },
            $addressesRepo->findBy([ "customer" => $customer ])

        );

        return $this->json($customersDataArray);
    }

}