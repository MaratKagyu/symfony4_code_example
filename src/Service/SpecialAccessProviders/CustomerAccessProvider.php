<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 6/10/2018
 * Time: 2:30 AM
 */

namespace App\Service\SpecialAccessProviders;

use App\Entity\Customer\Customer;
use App\Entity\Subsidiary;
use App\Service\AccessProvider;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CustomerAccessProvider extends AccessProvider
{
    const SECTION_CODE = 'customers';

    const ACL_DENIED = 0;
    const ACL_READONLY = 4;
    const ACL_SUBSIDIARY_FULL = 9;
    const ACL_FULL = 10;

    /**
     * @return int
     */
    public function getAccessToTheSection(): int
    {
        return $this->getAccessLevelTo(self::SECTION_CODE);
    }

    /**
     * @param int $minLevel
     * @return bool
     */
    public function hasAccessToTheSection(int $minLevel = self::ACL_READONLY): bool
    {
        return $this->hasAccessTo(self::SECTION_CODE, $minLevel);
    }

    /**
     * @param int $minLevel
     * @return int
     * @throws AccessDeniedException
     */
    public function requiresAccessToTheSection(int $minLevel = self::ACL_READONLY): int
    {
        return $this->requiresAccessTo(self::SECTION_CODE, $minLevel);
    }


    /**
     * @param Customer $customer
     * @return bool
     */
    public function userIsAssociatedWithTheCustomer(Customer $customer): bool
    {
        $accessLevel = $this->getAccessToTheSection();
        if ($accessLevel === self::ACL_FULL) {
            return true;
        }

        $authUser = $this->getUser();

        // Check if the customer is associated with the same subsidiary
        foreach ($authUser->getSubsidiaryListByType(Subsidiary::TYPE_SUBSIDIARY) as $subsidiary) {
            if ($subsidiary->getId() === $customer->getSubsidiary()->getId()) {
                return true;
            }
        }

        // Check if the customer is associated via partners -> machines -> customers
        foreach ($authUser->getPartnersList() as $partner) {
            foreach ($partner->getAssociatedMachines() as $machine) {
                if (! $machine->getCurrentHolder()) {
                    continue;
                }

                if ($machine->getCurrentHolder()->getId() === $customer->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    public function isReadable(Customer $customer): bool
    {
        switch ($this->getAccessToTheSection()) {
            case self::ACL_FULL:
                return true;

            case self::ACL_READONLY:
            case self::ACL_SUBSIDIARY_FULL:
            return $this->userIsAssociatedWithTheCustomer($customer);

            case self::ACL_DENIED:
            default:
                return false;
        }
    }

    /**
     * @param Customer $customer
     * @throws AccessDeniedException
     */
    public function readAccessRequired(Customer $customer)
    {
        if (! $this->isReadable($customer)) {
            throw new AccessDeniedException("You don't have access to this customer.");
        }
    }

    /**
     * @param Customer $customer
     * @return bool
     */
    public function isWritable(Customer $customer): bool
    {
        switch ($this->getAccessToTheSection()) {
            case self::ACL_FULL:
                return true;

            case self::ACL_SUBSIDIARY_FULL:
                return $this->userIsAssociatedWithTheCustomer($customer);

            case self::ACL_DENIED:
            case self::ACL_READONLY:
            default:
                return false;
        }
    }

    /**
     * @param Customer $customer
     * @throws AccessDeniedException
     */
    public function writeAccessRequired(Customer $customer)
    {
        if (! $this->isWritable($customer)) {
            throw new AccessDeniedException("You don't have write access to this customer.");
        }
    }

    /**
     * @return bool
     */
    public function canAddNewCustomers(): bool
    {
        switch ($this->getAccessToTheSection()) {
            case self::ACL_FULL:
            case self::ACL_SUBSIDIARY_FULL:
                return true;

            case self::ACL_DENIED:
            case self::ACL_READONLY:
            default:
                return false;
        }
    }

    /**
     * @throws AccessDeniedException
     */
    public function createAccessRequired()
    {
        if (! $this->canAddNewCustomers()) {
            throw new AccessDeniedException("You can't add new customers.");
        }
    }

}

