<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/19/2018
 * Time: 10:57 PM
 */

namespace App\Forms\Machine;

use App\Entity\Machine\Ticket;
use App\Entity\Machine\TicketResponse;
use App\Entity\User\User;
use App\Service\SpecialAccessProviders\TicketAccessProvider;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TicketResponseForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                "responseType",
                HiddenType::class
            )
            ->add(
                "message",
                HiddenType::class,
                [
                    "empty_data" => "",
                    "required" => false
                ]
            )
            ->add(
                "assignedTo",
                EntityType::class,
                [
                    "label" => "Assign To",
                    "class" => User::class,
                    "choice_label" => "fullName",
                    "required" => false
                ]
            )
            ->add(
                "filesListJson",
                HiddenType::class,
                [
                    "empty_data" => "[]",
                ]
            )
            ->add(
                "problemsAndSolutionsListJson",
                HiddenType::class,
                [
                    "empty_data" => "[]",
                ]
            )
            ->add(
                "installationChecklistJson",
                HiddenType::class,
                [
                    "empty_data" => "[]",
                ]
            )
            ->add(
                "serviceChecklistJson",
                HiddenType::class,
                [
                    "empty_data" => "[]",
                ]
            )
            ->add(
                "machineInfoJson",
                HiddenType::class,
                [
                    "empty_data" => "[]",
                ]
            )
            ->add(
                "travelTableJson",
                HiddenType::class,
                [
                    "empty_data" => "[]",
                ]
            )
            ->add(
                "travelLabourCharged",
                HiddenType::class,
                [
                    "empty_data" => "",
                ]
            )


            ->add(
                "status",
                HiddenType::class
            )
            ->add(
                "priority",
                HiddenType::class
            )
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TicketResponse::class,
        ]);
    }



}