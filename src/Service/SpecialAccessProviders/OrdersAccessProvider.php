<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 3/19/2018
 * Time: 11:54 PM
 */

namespace App\Service\SpecialAccessProviders;


use App\Entity\Order;
use App\Service\AccessProvider;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class OrdersAccessProvider extends AccessProvider
{
    /**
     * @return bool
     */
    public function canCreateNewOrder(): bool
    {
        $accessLevel = $this->getAccessLevelTo(self::SECTION_ORDERS);

        switch ($accessLevel) {
            case AccessProvider::ACCESS_SUBSIDIARY_FULL:
            case AccessProvider::ACCESS_DISTRIBUTOR:
            case AccessProvider::ACCESS_FULL:
                return true;

            case AccessProvider::ACCESS_READONLY:
            default:
                return false;
        }
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function canReadOrder(Order $order): bool
    {
        $accessLevel = $this->getAccessLevelTo(self::SECTION_ORDERS);

        switch ($accessLevel) {
            case AccessProvider::ACCESS_FULL:
                return true;

            case AccessProvider::ACCESS_READONLY:
            case AccessProvider::ACCESS_SUBSIDIARY_FULL:
            case AccessProvider::ACCESS_DISTRIBUTOR:
                $authSubsidiariesIds = $this->getUser()->getSubsidiaryIdsList();

                return in_array($order->getId(), $authSubsidiariesIds);

            default:
                return false;
        }
    }


    /**
     * @param Order $order
     */
    public function readAccessRequired(Order $order)
    {
        if (! $this->canReadOrder($order)) {
            throw new AccessDeniedException("Access denied");
        }
    }


    /**
     * @param Order $order
     * @return bool
     */
    public function canWriteToOrder(Order $order): bool
    {
        $accessLevel = $this->getAccessLevelTo(self::SECTION_ORDERS);

        switch ($accessLevel) {
            case AccessProvider::ACCESS_FULL:
                return true;

            case AccessProvider::ACCESS_SUBSIDIARY_FULL:
            case AccessProvider::ACCESS_DISTRIBUTOR:
                $authSubsidiariesIds = $this->getUser()->getSubsidiaryIdsList();
                return in_array($order->getId(), $authSubsidiariesIds);

            case AccessProvider::ACCESS_READONLY:
            default:

                return false;
        }
    }


    /**
     * @param Order $order
     */
    public function writeAccessRequired(Order $order)
    {
        if (! $this->canWriteToOrder($order)) {
            throw new AccessDeniedException("Access denied");
        }
    }
}