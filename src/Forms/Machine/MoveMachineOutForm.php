<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/19/2018
 * Time: 10:57 PM
 */

namespace App\Forms\Machine;

use App\Entity\Customer\Customer;
use App\Entity\Machine\Machine;
use App\Entity\Machine\MachineMovement;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class MoveMachineOutForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // Dynamic change params info
        // https://symfony.com/doc/current/form/dynamic_form_modification.html

        $builder
            ->add(
                "customer",
                EntityType::class,
                [
                    "label" => "Customer",
                    "class" => Customer::class,
                    "choice_label" => "fullName",
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('customer')
                            ->orderBy('customer.companyName', 'ASC');
                    },
                    "attr" => [
                        "class" => "select-with-search",
                    ]
                ]
            )
            ->add(
                "contractType",
                ChoiceType::class,
                [
                    "label" => "Contract Type",
                    "choices" => [
                        "Purchase" => "Purchase",
                        "Loan" => "Loan",
                        "Rent" => "Rent"
                    ]
                ]
            )
            ->add(
                "movementDateString",
                TextType::class,
                [
                    "label" => "Movement Date",
                    "empty_data" => "",
                    "attr" => [
                        "class" => "datepicker"
                    ]
                ]
            )
            ->add(
                "locationAddress",
                TextType::class,
                [
                    "label" => "Address",
                    "empty_data" => ""
                ]
            )
            ->add(
                "locationCountry",
                TextType::class,
                [
                    "label" => "Country",
                    "empty_data" => ""
                ]
            )
            ->add(
                "locationState",
                TextType::class,
                [
                    "label" => "State",
                    "empty_data" => ""
                ]
            )
            ->add(
                "locationCity",
                TextType::class,
                [
                    "label" => "City",
                    "empty_data" => ""
                ]
            )
            ->add(
                "locationZip",
                TextType::class,
                [
                    "label" => "Zip",
                    "empty_data" => ""
                ]
            )
            ->add(
                "distance",
                IntegerType::class,
                [
                    "label" => "Distance from HQ(km)",
                    "empty_data" => 0,
                    "attr" => [
                        "max" => 99999,
                        "min" => 0
                    ]
                ]
            )
            ->add(
                "machineStatus",
                ChoiceType::class,
                [
                    "label" => "Machine new status",
                    "choices" => array_flip(Machine::$statusesList),
                ]
            )
            ->add(
                "filesListJson",
                HiddenType::class,
                [
                    "empty_data" => "",
                    "required" => false
                ]
            )
        ;


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /* @var MachineMovement $movement */
            $movement = $event->getData();
            $movementForm = $event->getForm();


            $subsidiary = null;
            if ($movement->getMachine()) {
                if ($movement->getMachine()->getSubsidiary()) {
                    $subsidiary = $movement->getMachine()->getSubsidiary();
                }
            }

            if ($subsidiary) {
                $movementForm->add(
                    "customer",
                    EntityType::class,
                    [
                        "label" => "Customer",
                        "class" => Customer::class,
                        "choice_label" => "fullName",
                        'query_builder' => function (EntityRepository $er) use ($subsidiary){
                            return $er->createQueryBuilder('customer')
                                ->andWhere("customer.subsidiary = :subsidiary")
                                ->orderBy('customer.customerId', 'ASC')
                                ->addOrderBy('customer.companyName', 'ASC')
                                ->setParameters([
                                    "subsidiary" => $subsidiary
                                ]);
                        },
                        "attr" => [
                            "class" => "select-with-search",
                        ]
                    ]
                );
            }
        });
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MachineMovement::class,
        ]);
    }



}