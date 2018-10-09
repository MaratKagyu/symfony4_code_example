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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class AddressController extends Controller
{

    /**
     * @param Customer $customer
     * @param CustomerAccessProvider $accessProvider
     * @Route(
     *     "/customers/address/{customerId}",
     *     defaults={"customerId": 0},
     *     name="customersAddressPage",
     *     requirements={
     *         "customerId": "\d+"
     *     }
     * )
     * @ParamConverter("customer", class="App\Entity\Customer\Customer", options={"id" = "customerId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function customersAddressPage(Customer $customer, CustomerAccessProvider $accessProvider)
    {
        $accessProvider->readAccessRequired($customer);

        return $this->render(
            "customers/address.html.twig",
            [
                "customer" => $customer,
                "isWritable" => $accessProvider->isWritable($customer)
            ]
        );
    }



    /**
     * @param Customer $customer
     * @param int $addressId
     * @param Request $request
     * @param CustomerAccessProvider $accessProvider
     * @param GoogleMaps $googleMaps
     * @Route(
     *     "/customers/address/edit/{customerId}/{addressId}",
     *     name="editCustomersAddressPage",
     *     defaults={ "addressId": 0},
     *     requirements={
     *         "customerId": "\d+",
     *         "addressId": "\d+"
     *     }
     * )
     * @ParamConverter("customer", class="App\Entity\Customer\Customer", options={"id" = "customerId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editCustomersAddressPage(
        Customer $customer,
        int $addressId,
        Request $request,
        CustomerAccessProvider $accessProvider,
        GoogleMaps $googleMaps
    ){
        $accessProvider->writeAccessRequired($customer);

        $layoutMode = $request->query->get("layoutMode", "regular");

        $em = $this->getDoctrine()->getManager();

        // Loading address data
        if ($addressId) {
            $address = $customer->getAddressById($addressId);

            if (! $address) {
                throw $this->createNotFoundException("Address not found");
            }

        } else {
            $address = new CustomerAddress();
            $address->setCustomer($customer);
            $address->setCompany($customer->getCompanyName());

            $em->persist($address);
        }

        $addressForm = $this->createForm(CustomerAddressForm::class, $address);
        $addressForm->handleRequest($request);

        $errorText = '';

        if ($addressForm->isSubmitted() && $addressForm->isValid()) {
            $geoResults = $googleMaps->getGeoData($address->getAddressString());

            if (! $geoResults) {
                $errorText = "Couldn't recognize the given address location";
            } else {
                // Set coordinates
                $address
                    ->setCoordinatesLatitude($geoResults->getLatitude())
                    ->setCoordinatesLongitude($geoResults->getLongitude())
                ;

                // Set address data for fields which weren't filled
                if (! $address->getState()) {
                    $address->setState($geoResults->getState());
                }

                if (! $address->getZip()) {
                    $address->setZip($geoResults->getZipCode());
                }

                if (! $address->getCity()) {
                    $address->setCity($geoResults->getState());
                }
            }

            if (! $errorText) {
                // If the address was selected as default for specific address types, then we need to make sure,
                // that it's the only one default address for specified types
                if ($address->getDefaultForAddressTypes()->count()) {
                    /* @var CustomerAddress[] $siblingAddressList */
                    $siblingAddressList = $em
                        ->getRepository(CustomerAddress::class)
                        ->findBy(["customer" => $customer]);

                    foreach ($siblingAddressList as $siblingAddress) {
                        if ($siblingAddress->getId() == $addressId) continue;

                        foreach ($address->getDefaultForAddressTypes() as $addressType) {
                            $siblingAddress->removeDefaultForAddressTypes($addressType);
                        }
                    }
                }

                // Reset last updated time for the customer record
                $customer->updateLastUpdatedDateTime();

                $em->flush();

                switch ($layoutMode) {
                    case 'naked':
                        $addressData = json_encode($address->toArray());

                        // In naked mode we call parent script with user credentials
                        return new Response(
                            "<!DOCTYPE html>
                            <html>
                            <head>
                                <script >
                                    if (parent.onAddressSave) {
                                        parent.onAddressSave({$addressData})
                                    }
                                </script>
                            </head>
                            <body></body>
                            </html>
                            "
                        );

                    default:
                        return $this->redirectToRoute('customersAddressPage', [ "customerId" => $customer->getId()]);
                }
            }
        }

        switch ($layoutMode) {
            case 'naked':
                $templatePath = "customers/address/address-edit.naked.html.twig";
                break;

            default:
                $templatePath = "customers/address/address-edit.regular.html.twig";
                break;
        }

        return $this->render(
            $templatePath,
            [
                "customer" => $customer,
                "address" => $address,
                "addressForm" => $addressForm->createView(),
                "layoutMode" => $layoutMode,
                "errorText" => $errorText,
            ]
        );
    }

    /**
     * @param Customer $customer
     * @param int $addressId
     * @param CustomerAccessProvider $accessProvider
     * @Route(
     *     "/customers/address/delete/{customerId}/{addressId}",
     *     name="deleteCustomersAddressPage",
     *     defaults={ "addressId": 0},
     *     requirements={
     *         "customerId": "\d+",
     *         "addressId": "\d+"
     *     }
     * )
     * @ParamConverter("customer", class="App\Entity\Customer\Customer", options={"id" = "customerId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCustomersAddressPage(
        Customer $customer,
        int $addressId,
        CustomerAccessProvider $accessProvider
    ){
        $accessProvider->writeAccessRequired($customer);

        $em = $this->getDoctrine()->getManager();

        $address = $customer->getAddressById($addressId);

        if (! $address) {
            throw $this->createNotFoundException("Address not found");
        }

        // Reset last updated time for the customer record
        $customer->updateLastUpdatedDateTime();

        $em->remove($address);
        $em->flush();

        return $this->redirectToRoute('customersAddressPage', [ "customerId" => $customer->getId() ]);

    }
}