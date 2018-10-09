<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/19/2018
 * Time: 10:57 PM
 */

namespace App\Forms\Machine;

use App\Entity\Machine\Machine;
use App\Entity\Machine\MachineMovement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;



class MoveMachineInForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        // Dynamic change params info
        // https://symfony.com/doc/current/form/dynamic_form_modification.html

        $builder

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
                "machineStatus",
                ChoiceType::class,
                [
                    "label" => "Machine new status",
                    "choices" => array_flip(Machine::$statusesList),
                ]
            )
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MachineMovement::class,
        ]);
    }



}