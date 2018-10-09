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
use App\Entity\User\User;
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


class ServiceContractEversignForm extends AbstractType
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
                "contact",
                EntityType::class,
                [
                    "label" => "Contact",
                    "class" => User::class,
                    "choice_label" => "fullName",
                    "query_builder" => function (EntityRepository $er) {
                        return $er->createQueryBuilder('user')
                            ->where("user.customer IS NOT NULL")
                            ->where("user.email IS NOT NULL")
                            ->orderBy('user.firstName', 'ASC')
                            ->addOrderBy('user.lastName', 'ASC');
                    }
                ]
            )
            ->add(
                "customerAddress",
                TextType::class,
                [
                    "label" => "Customer Address",
                    "empty_data" => ""
                ]
            )
            ->add(
                "contactEmail",
                TextType::class,
                [
                    "label" => "Contact Email",
                    "empty_data" => ""
                ]
            )
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