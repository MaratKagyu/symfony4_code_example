<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Customer;

use App\Entity\Customer\Customer;
use App\Entity\User\User;
use App\Forms\User\CustomerContactForm;
use App\Repository\User\UserRepository;
use App\Service\Journal;
use App\Service\SpecialAccessProviders\CustomerAccessProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class ContactsJsonController extends Controller
{

    /**
     * @param CustomerAccessProvider $accessProvider
     * @Route("/json/get-all-customers-contacts", name="getAllCustomersContactsJson")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAllCustomersContactsJson(CustomerAccessProvider $accessProvider)
    {
        $accessLevel = $accessProvider->getAccessLevelTo(CustomerAccessProvider::SECTION_CODE);

        /* @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        $customersDataArray = array_map(
            function (User $user){

                $infoArray = $user->toFullInfoArray();

                $infoArray['links'] = [
                    "customerContacts" => $this->generateUrl(
                        "customersContactsPage",
                        [ "customerId" => $user->getCustomer()->getId() ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ];
                return $infoArray;
            },
            array_values(array_filter(
                $userRepo->loadBundledContacts(),
                function (User $user) use ($accessLevel, $accessProvider) {
                    // Drop users whose customers aren't associated with authenticated user
                    if ($accessLevel < CustomerAccessProvider::ACL_FULL) {
                        return $accessProvider->userIsAssociatedWithTheCustomer($user->getCustomer());
                    }

                    return true;
                }
            ))

        );

        return $this->json($customersDataArray);
    }

    /**
     * @param Customer $customer
     * @param CustomerAccessProvider $accessProvider
     * @Route(
     *     "/json/get-customers-contacts/{id}",
     *     name="getCustomerContactsJson",
     *     requirements={"id": "\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCustomerContactsJson(Customer $customer, CustomerAccessProvider $accessProvider)
    {
        $accessProvider->readAccessRequired($customer);

        $canEditCustomer = $accessProvider->isWritable($customer);

        /* @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        $customersDataArray = array_map(
            function (User $user) use ($canEditCustomer, $customer){
                $infoArray = $user->toFullInfoArray();
                $actions = [];

                if ($canEditCustomer) {
                    $actions['Edit'] = $this->generateUrl(
                        "editCustomersContactPage",
                        [
                            "customerId" => $customer->getId(),
                            "contactId" => $user->getId(),
                        ]
                    );
                }

                $infoArray['actions'] = $actions;

                return $infoArray;
            },
            $userRepo->findBy([ "customer" => $customer ])

        );

        return $this->json($customersDataArray);
    }

}