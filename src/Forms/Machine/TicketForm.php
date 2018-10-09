<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/19/2018
 * Time: 10:57 PM
 */

namespace App\Forms\Machine;

use App\Entity\Machine\Machine;
use App\Entity\Machine\Ticket;
use App\Entity\Machine\TicketRepairReason;
use App\Entity\User\User;
use App\Entity\User\UserGroup;
use App\Repository\User\UserRepository;
use App\Service\AccessProvider;
use App\Service\SpecialAccessProviders\TicketAccessProvider;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TicketForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $options['entity_manager'];

        /* @var User $authUser*/
        $authUser = $options['authorizedUser'];
        $subsidiaryIdsList = $authUser->getSubsidiaryIdsList();
        $subsidiaryIdsList[] = 0;
        $subsidiaryIdsString = implode(", ", $subsidiaryIdsList);

        $groupList = array_filter(
            $em->getRepository(UserGroup::class)->findAll(),
            function (UserGroup $group) {
                return (
                    $group->getAccessLevelTo(TicketAccessProvider::SECTION_CODE)
                    >= TicketAccessProvider::ACL_SERVICE_ENGINEER
                );
            }
        );


        // Repair Reason List
        $repairReasons = [ "-- please select --" => "" ];

        /* @var TicketRepairReason[] $repairReasonList */
        $repairReasonList = $em
            ->getRepository(TicketRepairReason::class)
            ->findBy(["status" => TicketRepairReason::STATUS_AVAILABLE], ["reason" => "ASC"]);

        foreach ($repairReasonList as $reason) {
            $repairReasons[$reason->getReason()] = $reason->getReason();
        }



        $builder
            ->add(
                "machine",
                EntityType::class,
                [
                    "label" => "Technegas Machine Serial No",
                    "class" => Machine::class,
                    "choice_label" => "serialId",
                    // "placeholder" => "Please Select",
                    "attr" => [
                        "class" => "select-with-search",
                    ],
                    'query_builder' => function (EntityRepository $er) use ($subsidiaryIdsString){
                        return $er->createQueryBuilder('machine')
                            ->where("machine.currentHolder IS NOT NULL")
                            ->andWhere("machine.subsidiary IN ({$subsidiaryIdsString})")
                            ->orderBy('machine.serialId', 'ASC');
                    },
                    // "required" => false,
                ]
            )
            ->add(
                "customerContact",
                EntityType::class,
                [
                    "label" => "Customer Contact",
                    "class" => User::class,
                    "choice_label" => "fullName",
                    "attr" => [
                        // "class" => "select-with-search",
                    ],
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
                    "attr" => [
                        // "class" => "select-with-search",
                    ],
                    'query_builder' => function (UserRepository $er) use ($groupList) {
                        return $er->getUsersInGroupsQueryBuilder($groupList);
                    },
                    "required" => false
                ]
            )
            ->add(
                "preferredDateTime",
                DateTimeType::class,
                [
                    "label" => "Preferred date/time",
                    "date_widget" => "single_text",
                    "date_format" => "dd/MM/yyyy",
                    "minutes" => [ 0, 10, 20, 30, 40, 50 ],
                    "attr" => [

                    ],

                ]
            )
            ->add(
                "serviceType",
                ChoiceType::class,
                [
                    "label" => "Service Type",
                    "choices" => array_flip(Ticket::$serviceTypes),
                ]
            )
            ->add(
                "repairReason",
                ChoiceType::class,
                [
                    "label" => "Repair Reason",
                    "choices" => $repairReasons,
                    "required" => false,
                    "empty_data" => "",
                ]
            )
            ->add(
                "description",
                TextareaType::class,
                [
                    "label" => "Description",
                    "empty_data" => "",
                    "required" => true
                ]
            )
            ->add(
                "priority",
                ChoiceType::class,
                [
                    "label" => "Priority",
                    "choices" => array_flip(Ticket::$priorities),
                ]
            )
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /* @var Ticket $ticket */
            $ticket = $event->getData();
            $ticketForm = $event->getForm();

            if ($ticket->getId()) {


                $ticketForm
                    ->add(
                        "machine",
                        EntityType::class,
                        [
                            "label" => "Technegas Machine Serial No",
                            "class" => Machine::class,
                            "choice_label" => "serialId",
                            "disabled" => true,
                            "attr" => [
                                "class" => "select-with-search",
                            ]
                        ]
                    )
                    ->add(
                        "description",
                        TextareaType::class,
                        [
                            "label" => "Description",
                            "empty_data" => "",
                            "required" => true,
                            "disabled" => true,
                        ]
                    )
                    ->add(
                        "priority",
                        ChoiceType::class,
                        [
                            "label" => "Priority",
                            "choices" => array_flip(Ticket::$priorities),
                            "disabled" => true,
                        ]
                    )
                    ->add(
                        "assignedTo",
                        EntityType::class,
                        [
                            "label" => "Assign To",
                            "class" => User::class,
                            "choice_label" => "fullName",
                            "disabled" => true
                        ]
                    )
                ;
            }
        });
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);

        $resolver->setRequired('entity_manager');
        $resolver->setRequired('authorizedUser');
    }



}