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
use App\Service\Journal;
use App\Service\SpecialAccessProviders\CustomerAccessProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;



class ContactsController extends Controller
{

    /**
     * @param CustomerAccessProvider $accessProvider
     * @Route("/all-contacts", name="allContactsPage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allContactsPage(CustomerAccessProvider $accessProvider)
    {
        $accessProvider->requiresAccessToTheSection();

        return $this->render(
            "customers/all-contacts.html.twig",
            [
                "isWritable" => ($accessProvider->canAddNewCustomers())
            ]
        );
    }

    /**
     * @param Customer $customer
     * @param CustomerAccessProvider $accessProvider
     * @Route(
     *     "/customers/contacts/{customerId}",
     *     defaults={"customerId": 0},
     *     name="customersContactsPage",
     *     requirements={
     *         "customerId": "\d+"
     *     }
     * )
     * @ParamConverter("customer", class="App\Entity\Customer\Customer", options={"id" = "customerId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function customersContactsPage(
        Customer $customer,
        CustomerAccessProvider $accessProvider
    ){
        $accessProvider->readAccessRequired($customer);

        return $this->render(
            "customers/contacts.html.twig",
            [
                "customer" => $customer,
                "isWritable" => $accessProvider->isWritable($customer)
            ]
        );
    }

    /**
     * @param Customer $customer
     * @param int $contactId
     * @param Request $request
     * @param UserPasswordEncoderInterface $pwdEncoder
     * @param Journal $journal
     * @param CustomerAccessProvider $accessProvider
     * @Route(
     *     "/customers/contacts/edit/{customerId}/{contactId}",
     *     name="editCustomersContactPage",
     *     defaults={ "contactId": 0},
     *     requirements={
     *         "customerId": "\d+",
     *         "contactId": "\d+"
     *     }
     * )
     * @ParamConverter("customer", class="App\Entity\Customer\Customer", options={"id" = "customerId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editCustomersContactPage(
        Customer $customer,
        int $contactId,
        Request $request,
        UserPasswordEncoderInterface $pwdEncoder,
        Journal $journal,
        CustomerAccessProvider $accessProvider
    ){
        $accessProvider->writeAccessRequired($customer);

        $em = $this->getDoctrine()->getManager();

        $layoutMode = $request->query->get("layoutMode", "regular");

        // Loading contact data
        if ($contactId) {
            // If it's an existing record
            $contact = $customer->getContactById($contactId);

            if (! $contact) {
                throw $this->createNotFoundException("Contact not found");
            }

            $previousState = $contact->toArray();

        } else {
            // if it's a new record
            $contact = new User();
            $contact
                ->setCustomer($customer)
                // ->setSubsidiary($customer->getSubsidiary())
                ->setStatus(User::STATUS_ACTIVE)
                ->setLang("en");

            $previousState = [];
        }


        $contactForm = $this->createForm(CustomerContactForm::class, $contact);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            if (! $contactId) {
                $em->persist($contact);
            }


            // If primary roles were selected, then we make sure other users don't have such primary roles
            if ($contact->getPrimaryContactRoles()->count()) {
                /* @var User[] $siblingsContacts */
                $siblingsContacts = $em->getRepository(User::class)->findBy(["customer" => $customer]);
                foreach ($siblingsContacts as $sibling) {
                    if ($sibling->getId() == $contactId) continue;

                    foreach ($contact->getPrimaryContactRoles() as $role) {
                        $sibling->removePrimaryContactRole($role);
                    }
                }
            }


            $newPassword = $contactForm->get("password")->getData();
            if ($newPassword) {
                // If the password has to be updated, then we encode it
                $contact->setEncodedPassword($pwdEncoder->encodePassword($contact, $newPassword));
            }

            // Reset last updated time for the customer record
            $customer->updateLastUpdatedDateTime();
            $contact->updateLastUpdatedDateTime();

            $em->flush();

            // Register the event in the journal
            $journal->registerEvent(
                Journal::ENTITY_TYPE_USER,
                $contact->getId(),
                $contactId ? Journal::ACTION_EDIT : Journal::ACTION_ADD,
                Journal::getArrayChange($previousState, $contact->toArray())
            );

            switch ($layoutMode) {
                case 'naked':
                    $slashedFullName = addslashes($contact->getFullName());

                    // In naked mode we call parent script with user credentials
                    return new Response(
                        "<!DOCTYPE html>
                        <html>
                        <head>
                            <script>
                                if (parent.onContactSave) {
                                    parent.onContactSave(" . $contact->getId() . ", '" . $slashedFullName . "');
                                }
                            </script>
                        </head>
                        <body></body>
                        </html>
                        "
                    );

                default:
                    return $this->redirectToRoute('customersContactsPage', [ "customerId" => $customer->getId()]);
            }
        }


        switch ($layoutMode) {
            case 'naked':
                $templatePath = "customers/contacts/contact-edit.naked.html.twig";
                break;


            default:
                $templatePath = "customers/contacts/contact-edit.regular.html.twig";
                break;
        }


        return $this->render(
            $templatePath,
            [
                "customer" => $customer,
                "contact" => $contact,
                "contactForm" => $contactForm->createView(),
                "layoutMode" => $layoutMode
            ]
        );
    }

}