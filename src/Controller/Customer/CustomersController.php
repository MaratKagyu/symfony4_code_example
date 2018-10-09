<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Customer;

use App\Entity\Customer\{Customer, CustomerBonafide, CustomerFile};
use App\Forms\Customer\CustomerForm;
use App\Repository\Customer\CustomerRepository;
use App\Service\AccessProvider;
use App\Service\FileUploader;
use App\Service\Journal;
use App\Service\SpecialAccessProviders\CustomerAccessProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class CustomersController extends Controller
{
    /**
     * @param CustomerAccessProvider $accessProvider
     * @Route("/customers", name="customersPage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function customersPage(CustomerAccessProvider $accessProvider)
    {
        $accessLevel = $accessProvider->requiresAccessToTheSection();

        return $this->render(
            "customers/customers-list.html.twig",
            [
                "isWritable" => ($accessLevel >= CustomerAccessProvider::ACL_SUBSIDIARY_FULL)
            ]
        );
    }


    /**
     * @param int $customerId
     * @param Request $request
     * @param Journal $journal
     * @param CustomerAccessProvider $accessProvider
     *
     * @Route("/customers/edit/{customerId}", name="customerEditPage", requirements={"customerId"="\d+"})
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function customerEditPage(
        int $customerId,
        Request $request,
        Journal $journal,
        CustomerAccessProvider $accessProvider
    ){
        $em = $this->getDoctrine()->getManager();

        /* @var CustomerRepository $customerRepo */
        $customerRepo = $em->getRepository(Customer::class);

        /* @var Customer $customer */
        if ($customerId) {
            $customer = $customerRepo->find($customerId);
            if (! $customer) {
                throw $this->createNotFoundException("Customer not found");
            }

            // Check if the user has access to the customer
            $accessProvider->writeAccessRequired($customer);

            $previousState = $customer->toArray();

        } else {
            // Check if the user can add new customers
            $accessProvider->canAddNewCustomers();

            $customer = new Customer();
            $em->persist($customer);

            $previousState = [];
        }

        $customerForm = $this->createForm(
            CustomerForm::class,
            $customer,
            ["access_provider" => $accessProvider]
        );
        $customerForm->handleRequest($request);

        // If the form is submitted and valid
        if ($customerForm->isSubmitted() && $customerForm->isValid()) {
            $errorOccurred = false;

            // Check if a parent was selected
            $hasParent = !! $request->get("customer_has_parent");
            if (! $hasParent) {
                $customer->setParent(null);
            }

            // If no errors appeared
            if (! $errorOccurred) {

                if (! $customer->getId()) {
                    // if the users doesn't exist, generate new ID
                    $customer->setCustomerId($customerRepo->getNewCustomerId($customer->getSubsidiary()));
                }

                // Reset last updated time for the customer record
                $customer->updateLastUpdatedDateTime();

                // Check if the user has access to the customer (after is updated it
                // in order to avoid situations when user creates a customer he cannot access)
                $accessProvider->writeAccessRequired($customer);

                $this->getDoctrine()->getManager()->flush();

                // Register
                $journal->registerEvent(
                    Journal::ENTITY_TYPE_CUSTOMER,
                    $customer->getId(),
                    $customerId ? Journal::ACTION_EDIT : Journal::ACTION_ADD,
                    Journal::getArrayChange($previousState, $customer->toArray())
                );

                return $this->redirectToRoute("customerViewPage", ["customerId" => $customer->getId()]);
            }
        }


        $allCustomersList = $customerRepo->findBy(["status" => Customer::STATUS_ACTIVE], ["companyName" => "ASC"]);


        return $this->render(
            "customers/customer-edit.html.twig",
            [
                "customerForm" => $customerForm->createView(),
                "customer" => $customer,
                "allCustomersList" => $allCustomersList
            ]
        );
    }




    /**
     * @param Customer $customer
     * @param CustomerAccessProvider $accessProvider
     *
     * @Route("/customers/view/{customerId}", name="customerViewPage", requirements={"customerId"="\d+"})
     * @ParamConverter("customer", class="App\Entity\Customer\Customer", options={"id" = "customerId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function customerViewPage(
        Customer $customer,
        CustomerAccessProvider $accessProvider
    ){
        $accessProvider->readAccessRequired($customer);

        return $this->render(
            "customers/customer-view.html.twig",
            [
                "customer" => $customer,

                "isWritable" => $accessProvider->isWritable($customer),
                "canAddOrders" => (
                    $accessProvider->getAccessLevelTo(CustomerAccessProvider::SECTION_ORDERS)
                    >= AccessProvider::ACCESS_SUBSIDIARY_FULL
                )
            ]
        );
    }

    /**
     * @param Customer $customer
     * @param Request $request
     * @param CustomerAccessProvider $accessProvider
     *
     * @Route(
     *     "/customers/add-file/{customerId}",
     *     methods={"POST"},
     *     name="addCustomerFileAction",
     *     requirements={"customerId"="\d+"}
     * )
     * @ParamConverter("customer", class="App\Entity\Customer\Customer", options={"id" = "customerId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCustomerFileAction(
        Customer $customer,
        Request $request,
        CustomerAccessProvider $accessProvider
    ){
        $accessProvider->writeAccessRequired($customer);

        $em = $this->getDoctrine()->getManager();

        $fileName = $request->request->get("new-customer-file-name", "");
        $filePath = $request->request->get("new-customer-file-path", "");

        if ($fileName && $fileName) {
            $fileRecord = new CustomerFile();

            $fileRecord
                ->setCustomer($customer)
                ->setName($fileName)
                ->setFileName($filePath)
                ->setUploadedBy($accessProvider->getUser());

            $em->persist($fileRecord);
            $em->flush();
        }

        return $this->redirectToRoute('customerViewPage', [ "customerId" => $customer->getId() ]);

    }


    /**
     * @param Customer $customer
     * @param Request $request
     * @param CustomerAccessProvider $accessProvider
     *
     * @Route(
     *     "/customers/add-bonafide/{customerId}",
     *     methods={"POST"},
     *     name="addCustomerBonafideAction",
     *     requirements={"customerId"="\d+"}
     * )
     * @ParamConverter("customer", class="App\Entity\Customer\Customer", options={"id" = "customerId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addCustomerBonafideAction(
        Customer $customer,
        Request $request,
        CustomerAccessProvider $accessProvider
    ){

        $accessProvider->writeAccessRequired($customer);

        $em = $this->getDoctrine()->getManager();

        $fileName = $request->request->get("new-customer-bonafide-name", "");
        $filePath = $request->request->get("new-customer-bonafide-path", "");
        $dateString = $request->request->get("new-customer-bonafide-date", "");

        if ($fileName && $fileName) {
            $bonafideRecord = new CustomerBonafide();

            $bonafideRecord
                ->setCustomer($customer)
                ->setName($fileName)
                ->setFileName($filePath)
                ->setUploadedBy($accessProvider->getUser())
                ->setExpiryDateString($dateString);

            $em->persist($bonafideRecord);
            $em->flush();
        }

        return $this->redirectToRoute('customerViewPage', [ "customerId" => $customer->getId() ]);

    }



    /**
     * @param Customer $customer
     * @param string $fileName
     * @param FileUploader $fileUploader
     * @param CustomerAccessProvider $accessProvider
     *
     * @Route(
     *     "/customers/download-file/{customerId}/{fileName}",
     *     name="downloadCustomerFileAction",
     *     requirements={"customerId"="\d+"}
     * )
     * @ParamConverter("customer", class="App\Entity\Customer\Customer", options={"id" = "customerId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadCustomerFileAction(
        Customer $customer,
        string $fileName,
        FileUploader $fileUploader,
        CustomerAccessProvider $accessProvider
    ){
        $accessProvider->readAccessRequired($customer);

        $fileFullPath = (
            $fileUploader->getProjectRootPath() .
            Customer::getFilesLocationByCustomerId($customer->getId()) .
            preg_replace('#\.{2,}#isu', "", $fileName)
        );

        if (! file_exists($fileFullPath)) {
            throw $this->createNotFoundException("File not found");
        }

        return $this->file($fileFullPath);
    }
}
