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
use App\Entity\Subsidiary;
use App\Service\SpecialAccessProviders\MachineAccessProvider;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;



class MoveMachineSubsidiaryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MachineAccessProvider $accessProvider */
        $accessProvider = $options['access_provider'];
        $accessLevel = $accessProvider->getAccessToTheSection();

        $builder
            ->add(
                "subsidiary",
                EntityType::class,
                [
                    "label" => "CYC Subsidiary",
                    "class" => Subsidiary::class,
                    "choice_label" => "name",
                    "query_builder" => function (EntityRepository $er) use ($accessLevel, $accessProvider) {
                        switch ($accessLevel) {
                            case MachineAccessProvider::ACL_FULL:
                                return $er->createQueryBuilder('subsidiary')
                                    ->where("subsidiary.type = " . Subsidiary::TYPE_SUBSIDIARY)
                                    ->orderBy('subsidiary.name', 'ASC');

                            case MachineAccessProvider::ACL_SUBSIDIARY_FULL:
                            default:
                                return $er->createQueryBuilder('subsidiary')
                                    ->where("subsidiary.id IN (:idList)")
                                    ->andWhere("subsidiary.type = " . Subsidiary::TYPE_SUBSIDIARY)
                                    ->setParameter("idList", $accessProvider->getUser()->getSubsidiaryIdsList())
                                    ->orderBy('subsidiary.name', 'ASC');
                        }
                    },
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
                "machineStatus",
                ChoiceType::class,
                [
                    "label" => "Machine new status",
                    "choices" => array_flip(Machine::$statusesList),
                ]
            )
            ->add(
                "distance",
                IntegerType::class,
                [
                    "label" => "Distance",
                    "empty_data" => 0,
                    "attr" => [
                        "max" => 99999,
                        "min" => 0
                    ]
                ]
            )
            ->add(
                "reason",
                TextType::class,
                [
                    "label" => "Reason",
                    "empty_data" => "",
                ]
            )
        ;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MachineMovement::class,
        ]);

        $resolver->setRequired('access_provider');

    }



}