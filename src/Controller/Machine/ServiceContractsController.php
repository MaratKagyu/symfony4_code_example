<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Machine;

use App\Entity\Machine\Machine;
use App\Entity\Machine\MachineServiceContract;
use App\Forms\Machine\ServiceContractEversignForm;
use App\Forms\Machine\ServiceContractForm;
use App\Service\Eversign;
use App\Service\FileUploader;
use App\Repository\Machine\MachineServiceContractRepository;
use App\Service\Journal;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\SpecialAccessProviders\ServiceContractsAccessProvider as ContractsAccessProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class ServiceContractsController extends Controller
{

    /**
     * Have to use this command as an common route, because Eversign refuses working in command-line mode
     * @param Eversign $eversign
     * @param FileUploader $fileUploader
     * @Route("/eversign-api/recheck-documents", name="recheckEversignDocuments")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function recheckEversignDocuments(Eversign $eversign, FileUploader $fileUploader)
    {
        $em = $this->getDoctrine()->getManager();


        /* @var MachineServiceContract[] $pendingContracts */
        $pendingContracts = array_filter(
            $em
                ->getRepository(MachineServiceContract::class)
                ->findBy(
                    [ "status" => [ MachineServiceContract::STATUS_AWAITING_SIGNATURE ] ]
                ),
            function (MachineServiceContract $contract) {
                return !! $contract->getEversignDocumentHash();
            }
        );


        // Loading each Eversign document
        foreach ($pendingContracts as $contract) {
            $document = $eversign->loadDocument($contract->getEversignDocumentHash());

            // If the document doesn't exist
            if (! $document) {
                $contract->setStatus(MachineServiceContract::STATUS_VOIDED);
                continue;
            }

            /* @var \Eversign\Signer $customerSignData */
            $customerSignData = $document->getSigners()[0];
            /* @var \Eversign\Signer $subsidiarySignData */
            $subsidiarySignData = $document->getSigners()[1];

            if ($customerSignData->getDeclined() || $subsidiarySignData->getDeclined()) {
                // If one of the parties declined the contracts, then we consider the contract is canceled
                $contract->setStatus(MachineServiceContract::STATUS_CANCELED);

            } elseif ($document->getIsCompleted()) {


                $signedDate = new \DateTime();


                $contract->setStatus(MachineServiceContract::STATUS_ACTIVE);

                $signedDate->setTimestamp($customerSignData->getSigned_timestamp());
                $endDate = new \DateTime();
                $endDate->setTimestamp($customerSignData->getSigned_timestamp());

                $contract
                    ->setStartDate($signedDate)
                    ->setEndDate($endDate->modify("+1 year"));


                // Download a contract
                $projectRoot = $fileUploader->getProjectRootPath();
                $localDir = $fileUploader->getUploadLocation(
                    FileUploader::ENTITY_TYPE_MACHINE_SERVICE_CONTRACT,
                    $contract->getMachine()->getId()
                );

                $fullPath = $projectRoot . $localDir;
                if (! file_exists($fullPath)) mkdir($fullPath, 0755, true);

                $fileName = $fileUploader->getUniqueFileName(
                    $fullPath,
                    "contract_" . $contract->getId() . ".pdf"
                );

                $eversign->downloadFinalDocument($document, $fullPath . $fileName);

                $contract->setFilesList([$localDir . $fileName]);

            } elseif ($document->getIsCanceled() || $document->getIsDeleted() || $document->getIsTrashed()) {
                $contract->setStatus(MachineServiceContract::STATUS_CANCELED);
            }
        }


        $em->flush();
        return $this->json([ "message" => "ok" ]);
    }



    /**
     * @param Request $request
     * @param ContractsAccessProvider $accessProvider
     * @Route(
     *     "/json/machine-service-contract-list",
     *     name="jsonGetMachineServiceContractsList"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jsonGetMachineServiceContractsList(Request $request, ContractsAccessProvider $accessProvider)
    {

        $accessLevel = $accessProvider->getAccessToTheSection();

        /* @var MachineServiceContractRepository $contractRepo */
        $contractRepo = $this->getDoctrine()->getRepository(MachineServiceContract::class);

        // Check if we need to filter by a specific customer
        $customerId = (int)$request->get("customerId", 0);

        // Check if we need to filter by a specific machine
        $machineId = (int)$request->get("machineId", 0);

        if ($accessLevel >= ContractsAccessProvider::ACL_FULL) {
            $contractsList = $contractRepo->findAll();
        } else {
            $contractsList = $contractRepo->loadUserAssocBundledContracts($accessProvider->getUser());
        }

        // Filter records
        $contractsList = array_values(array_filter(
            $contractsList,
            function (MachineServiceContract $contract) use ($customerId, $machineId) {
                if ($customerId && $contract->getCustomer() && ($contract->getCustomer()->getId() != $customerId)) {
                    return false;
                }

                if ($machineId && $contract->getMachine() && ($contract->getMachine()->getId() != $machineId)) {
                    return false;
                }

                return true;
            }
        ));

        return $this->json(array_map(
            function (MachineServiceContract $contract) use ($accessLevel){
                $infoArray = $contract->toInfoArray();

                $newFileList = [];
                foreach ($infoArray['fileList'] as $fileName) {
                    $newFileList[$fileName] = $this->generateUrl(
                        "downloadServiceContractAction",
                        [
                            "contractId" => $contract->getId(),
                            "fileName" => $fileName
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                }

                $infoArray['fileList'] = $newFileList;

                $infoArray['actions'] = [
                    "View" => $this->generateUrl(
                        "serviceContractViewPage",
                        [ "id" => $contract->getId() ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ];

                return $infoArray;
            },
            $contractsList
        ));
    }


    /**
     * @param ContractsAccessProvider $accessProvider
     * @Route("/service-contracts", name="serviceContractsPage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serviceContractsPage(ContractsAccessProvider $accessProvider)
    {
        $accessProvider->requiresAccessToTheSection();

        return $this->render(
            "machines/service-contracts-list.html.twig",
            [

                "isWritable" => ($accessProvider->canAddNewContracts())
            ]
        );
    }


    /**
     * @param MachineServiceContract $contract
     * @param ContractsAccessProvider $accessProvider
     * @Route(
     *     "/service-contract/view/{id}",
     *     name="serviceContractViewPage",
     *     requirements={"id"="\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serviceContractViewPage(
        MachineServiceContract $contract,
        ContractsAccessProvider $accessProvider
    ){
        $accessProvider->readAccessRequired($contract);

        return $this->render(
            "machines/service-contract-view.html.twig",
            [
                "contract" => $contract
            ]
        );
    }


    /**
     * @param int $contractId
     * @param Request $request
     * @param Journal $journal
     * @param ContractsAccessProvider $accessProvider
     * @param FileUploader $fileUploader
     * @Route("/service-contract/edit/{contractId}", name="serviceContractEditPage", requirements={"contractId"="\d+"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serviceContractEditPage(
        int $contractId,
        Request $request,
        Journal $journal,
        ContractsAccessProvider $accessProvider,
        FileUploader $fileUploader
    ){
        $em = $this->getDoctrine()->getManager();

        /* @var MachineServiceContractRepository $contractsRepo*/
        $contractsRepo = $this->getDoctrine()->getRepository(MachineServiceContract::class);


        /* @var MachineServiceContract $contract */
        if ($contractId) {
            $contract = $contractsRepo->find($contractId);

            if (! $contract) {
                throw $this->createNotFoundException("Contract not found");
            }

            // Check if user can edit the contract
            $accessProvider->writeAccessRequired($contract);

            $previousState = $contract->toArray();

        } else {
            // Check if user can add new contracts
            $accessProvider->canAddNewContracts();

            $contract = new MachineServiceContract();

            $machineId = $request->query->getInt("machineId", 0);
            if ($machineId) {
                /* @var \App\Entity\Machine\Machine $machine */
                $machine = $this->getDoctrine()->getRepository(Machine::class)->find($machineId);
                $contract->setMachine($machine);
            }

            $previousState = [];
        }



        $contractForm = $this->createForm(
            ServiceContractForm::class,
            $contract,
            // This form requires Entity Manager
            [ 'entity_manager' => $em ]
        );

        $contractForm->handleRequest($request);

        // If the form is submitted
        if ($contractForm->isSubmitted() && $contractForm->isValid()) {

            $errorOccurred = false;

            // If no errors appeared
            if (! $errorOccurred) {


                // If the record is new
                if (! $contract->getId()) {

                    // The customer is the one, associated with the machine
                    if ($contract->getMachine()) {
                        if ($contract->getMachine()->getCurrentHolder()) {
                            $contract->setCustomer($contract->getMachine()->getCurrentHolder());
                        }

                        $contract->setSubsidiary($contract->getMachine()->getSubsidiary());
                    }

                    $contract
                        ->setStatus(MachineServiceContract::STATUS_ACTIVE)
                        ->setCreator($accessProvider->getUser())
                    ;

                    // if the record doesn't exist, we add him to the repo
                    $this->getDoctrine()->getManager()->persist($contract);
                }

                // Recheck write access in order to avoid situations, when user creates a record he has no access to
                $accessProvider->writeAccessRequired($contract);

                $this->getDoctrine()->getManager()->flush();


                //
                $journal->registerEvent(
                    Journal::ENTITY_TYPE_MACHINE_SERVICE_CONTRACT,
                    $contract->getId(),
                    $contractId ? Journal::ACTION_EDIT : Journal::ACTION_ADD,
                    Journal::getArrayChange($previousState, $contract->toArray())
                );



                if (! $contractId) {
                    // If the contract is newly created, move files from temporary folder
                    foreach ($contract->getFilesList() as $file) {
                        $fileUploader->renameFile(
                            $fileUploader->getUploadLocation(
                                FileUploader::ENTITY_TYPE_MACHINE_SERVICE_CONTRACT,
                                0
                            ) . $file,
                            $fileUploader->getUploadLocation(
                                FileUploader::ENTITY_TYPE_MACHINE_SERVICE_CONTRACT,
                                $contract->getId()
                            ) . $file
                        );
                    }
                }


                return $this->redirectToRoute("serviceContractsPage");
            }
        }


        return $this->render(
            "machines/service-contract-edit.html.twig",
            [
                "contractForm" => $contractForm->createView(),
                "contract" => $contract
            ]
        );
    }


    /**
     * @param int $contractId
     * @param Request $request
     * @param Journal $journal
     * @param Eversign $eversign
     * @param ContractsAccessProvider $accessProvider
     * @Route(
     *     "/service-contract/eversign/{contractId}",
     *     name="serviceContractEversignEditPage",
     *     requirements={"contractId"="\d+"}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function serviceContractEversignEditPage(
        int $contractId,
        Request $request,
        Journal $journal,
        Eversign $eversign,
        ContractsAccessProvider $accessProvider
    ){
        $em = $this->getDoctrine()->getManager();

        /* @var MachineServiceContractRepository $contractsRepo*/
        $contractsRepo = $this->getDoctrine()->getRepository(MachineServiceContract::class);


        /* @var MachineServiceContract $contract */
        if ($contractId) {
            $contract = $contractsRepo->find($contractId);

            if (! $contract) {
                throw $this->createNotFoundException("Contract not found");
            }

            // Check if user can edit the contract
            $accessProvider->writeAccessRequired($contract);

            $previousState = $contract->toArray();

        } else {
            // Check if user can add new contracts
            $accessProvider->canAddNewContracts();

            $contract = new MachineServiceContract();

            $machineId = $request->query->getInt("machineId", 0);
            if ($machineId) {
                /* @var \App\Entity\Machine\Machine $machine */
                $machine = $this->getDoctrine()->getRepository(Machine::class)->find($machineId);
                $contract->setMachine($machine);
            }

            $previousState = [];
        }

        //
        $contract->addFlag(MachineServiceContract::FLAG_IS_EVERSIGN_CONTRACT);

        $contractForm = $this->createForm(
            ServiceContractEversignForm::class,
            $contract,
            // This form requires Entity Manager
            [ 'entity_manager' => $em ]
        );

        $contractForm->handleRequest($request);

        // If the form is submitted
        if ($contractForm->isSubmitted() && $contractForm->isValid()) {

            $errorOccurred = false;

            // If no errors appeared
            if (! $errorOccurred) {

                if (! $contract->getId()) {
                    // The Customer is the one, associated with the machine
                    if ($contract->getMachine()) {
                        if ($contract->getMachine()->getCurrentHolder()) {
                            $contract->setCustomer($contract->getMachine()->getCurrentHolder());
                        }

                        $contract->setSubsidiary($contract->getMachine()->getSubsidiary());
                    }

                    $eversignHash = $eversign->createDocument(
                        $contract,
                        $contract->getContact(),
                        $contract->getMachine()->getSubsidiary()
                    );

                    $contract
                        ->setCreator($accessProvider->getUser())
                        ->setEversignDocumentHash($eversignHash)
                        ->setStatus(MachineServiceContract::STATUS_AWAITING_SIGNATURE);

                    // if the record doesn't exist, we add him to the repo
                    $this->getDoctrine()->getManager()->persist($contract);
                }

                // Recheck write access in order to avoid situations, when user creates a record he has no access to
                $accessProvider->writeAccessRequired($contract);

                $this->getDoctrine()->getManager()->flush();


                //
                $journal->registerEvent(
                    Journal::ENTITY_TYPE_MACHINE_SERVICE_CONTRACT,
                    $contract->getId(),
                    $contractId ? Journal::ACTION_EDIT : Journal::ACTION_ADD,
                    Journal::getArrayChange($previousState, $contract->toArray())
                );


                return $this->redirectToRoute("serviceContractsPage");
            }
        }


        return $this->render(
            "machines/service-contract-eversign.html.twig",
            [
                "contractForm" => $contractForm->createView(),
                "contract" => $contract
            ]
        );
    }



    /**
     * @param MachineServiceContract $contract
     * @param string $fileName
     * @param FileUploader $fileUploader
     * @param ContractsAccessProvider $accessProvider
     * @Route(
     *     "/machines/download-service-contract/{contractId}/{fileName}",
     *     name="downloadServiceContractAction",
     *     requirements={"contractId"="\d+"}
     * )
     * @ParamConverter("contract", class="\App\Entity\Machine\MachineServiceContract", options={"id" = "contractId"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadServiceContractAction(
        MachineServiceContract $contract,
        string $fileName,
        FileUploader $fileUploader,
        ContractsAccessProvider $accessProvider
    ){
        $accessProvider->readAccessRequired($contract);

        $fileFullPath = (
            $fileUploader->getProjectRootPath() .
            MachineServiceContract::getFilesLocationById($contract->getId()) .
            preg_replace('#\.{2,}#isu', "", $fileName)
        );

        if (! file_exists($fileFullPath)) {
            throw $this->createNotFoundException("File not found");
        }

        return $this->file($fileFullPath);
    }

}