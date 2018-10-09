<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/19/2018
 * Time: 10:57 PM
 */

namespace App\Forms\Machine;

use App\Entity\Machine\ContractType;
use App\Entity\Machine\Machine;
use App\Entity\Machine\MachineServiceContract;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;


class ServiceContractForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $options['entity_manager'];

        /* @var ContractType[] $contractTypes */
        $contractTypes = $em
            ->getRepository(ContractType::class)
            ->findBy(["status" => ContractType::STATUS_AVAILABLE], ["typeName" => "ASC"]);

        $availableContractTypes = [];
        foreach ($contractTypes as $contractType) {
            $availableContractTypes[$contractType->getTypeName()] = $contractType->getTypeName();
        }



        $builder
            ->add(
                "machine",
                EntityType::class,
                [
                    "label" => "Serving machine",
                    "class" => Machine::class,
                    "choice_label" => "serialId",
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('machine')
                            ->where("machine.currentHolder IS NOT NULL")
                            ->orderBy('machine.serialId', 'ASC');
                    },
                    "attr" => [
                        "class" => "select-with-search",
                    ]
                ]
            )
            ->add(
                "contractNumber",
                TextType::class,
                [
                    "label" => "Contract No.",
                    "empty_data" => ""
                ]
            )
            ->add(
                "contractType",
                ChoiceType::class,
                [
                    "label" => "Contract Type",
                    "choices" => $availableContractTypes,
                ]
            )
            ->add(
                "startDateString",
                TextType::class,
                [
                    "label" => "Start Date",
                    "empty_data" => "",
                    "attr" => [
                        "class" => "datepicker"
                    ]
                ]
            )
            ->add(
                "endDateString",
                TextType::class,
                [
                    "label" => "End Date",
                    "empty_data" => "",
                    "attr" => [
                        "class" => "datepicker"
                    ]
                ]
            )
            ->add(
                "filesListJson",
                HiddenType::class,
                [
                    "empty_data" => ""
                ]
            )
//            ->add(
//                "status",
//                ChoiceType::class,
//                [
//                    "label" => "Service Type",
//                    "choices" => array_flip(MachineServiceContract::$statusesList),
//                ]
//            )
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($em) {
            /* @var MachineServiceContract $contract */
            $contract = $event->getData();
            $contractForm = $event->getForm();



            if ($contract) {
                /* @var ContractType[] $contractTypes */
                $contractTypes = $em
                    ->getRepository(ContractType::class)
                    ->findBy(["status" => ContractType::STATUS_AVAILABLE], ["typeName" => "ASC"]);

                $availableContractTypes = [];
                if ($contract->getContractType()) {
                    $availableContractTypes[$contract->getContractType()] = $contract->getContractType();
                }


                foreach ($contractTypes as $contractType) {
                    $availableContractTypes[$contractType->getTypeName()] = $contractType->getTypeName();
                }


                $contractForm->add(
                    "contractType",
                    ChoiceType::class,
                    [
                        "label" => "Contract Type",
                        "choices" => $availableContractTypes,
                    ]
                );
            }
        });

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MachineServiceContract::class,
        ]);

        $resolver->setRequired('entity_manager');

    }



}