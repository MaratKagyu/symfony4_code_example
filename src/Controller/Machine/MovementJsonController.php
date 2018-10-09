<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 2/17/2018
 * Time: 7:44 AM
 */

namespace App\Controller\Machine;

use App\Entity\Machine\Machine;
use App\Entity\Machine\MachineMovement;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Service\SpecialAccessProviders\MachineAccessProvider;

class MovementJsonController extends Controller
{
    /**
     * @param Machine $machine,
     * @param MachineAccessProvider $accessProvider
     * @Route("/json/movement-list/{id}", name="jsonGetMovementList", requirements={"id"="\d+"})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function jsonGetMovementList(
        Machine $machine,
        MachineAccessProvider $accessProvider
    ){
        $accessProvider->readAccessRequired($machine);

        $movementListArray = array_map(
            function (MachineMovement $movement) {
                return $movement->toArray();
            },
            $this
                ->getDoctrine()
                ->getRepository(MachineMovement::class)
                ->findBy(["machine" => $machine], ["movementDate" => "DESC"])
        );


        return $this->json($movementListArray);
    }
}