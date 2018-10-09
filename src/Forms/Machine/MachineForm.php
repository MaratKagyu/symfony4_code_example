<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/19/2018
 * Time: 10:57 PM
 */

namespace App\Forms\Machine;

use App\Entity\Machine\Machine;
use App\Entity\Machine\MachineBuildLocation;
use App\Entity\Machine\MachineType;
use App\Entity\Subsidiary;
use App\Service\SpecialAccessProviders\MachineAccessProvider;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;


class MachineForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MachineAccessProvider $accessProvider */
        $accessProvider = $options['access_provider'];
        $accessLevel = $accessProvider->getAccessToTheSection();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $options['entity_manager'];

        /* @var MachineBuildLocation[] $locationList */
        $locationList = $em->getRepository(MachineBuildLocation::class)->findBy([], ["location" => "ASC"]);
        $availableLocations = [];
        foreach ($locationList as $location) {
            $availableLocations[$location->getLocation()] = $location->getLocation();
        }

        $builder
            ->add(
                "serialId",
                TextType::class,
                ["label" => "Machine Serial", "empty_data" => ""]
            )
            ->add(
                "buildDateString",
                TextType::class,
                [
                    "label" => "Build date",
                    "empty_data" => "",
                    "attr" => [
                        "class" => "datepicker"
                    ]
                ]
            )
            ->add(
                "buildLocation",
                ChoiceType::class,
                [
                    "label" => "Build Location",
                    "choices" => $availableLocations
                ]
            )
            ->add(
                "types",
                EntityType::class,
                [
                    "label" => "Type",
                    "class" => MachineType::class,
                    "choice_label" => "typeName",
                    "multiple" => true,
                    "expanded" => true,
                    'query_builder' => function (EntityRepository $er){
                        return $er->createQueryBuilder('mt')
                            ->where("mt.status = 1")
                            ->orderBy('mt.typeName', 'ASC')
                            ->setParameters([]);
                    },
                ]
            )
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
                "status",
                ChoiceType::class,
                [
                    "label" => "Status",
                    "choices" => array_flip(Machine::$statusesList),
                ]
            )

        ;


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /* @var Machine $machine */
            $machine = $event->getData();
            $machineForm = $event->getForm();

            if ($machine->getId()) {
                $selectedIds = array_map(
                    function ($typeArray) {
                        return $typeArray["id"];
                    },
                    $machine->getTypesArray()
                );


                $machineForm->add(
                    "types",
                    EntityType::class,
                    [
                        "label" => "Type",
                        "class" => MachineType::class,
                        "choice_label" => "typeName",
                        "multiple" => true,
                        "expanded" => true,
                        'query_builder' => function (EntityRepository $er) use ($selectedIds){
                            return $er->createQueryBuilder('mt')
                                ->where("mt.status = 1")
                                ->orWhere("mt.id IN (:selectedOption)")
                                ->orderBy('mt.typeName', 'ASC')
                                ->setParameters([
                                    "selectedOption" => $selectedIds
                                ]);
                        },
                    ]
                );
            }
        });
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Machine::class,
        ]);

        $resolver->setRequired('entity_manager');
        $resolver->setRequired('access_provider');
    }



}