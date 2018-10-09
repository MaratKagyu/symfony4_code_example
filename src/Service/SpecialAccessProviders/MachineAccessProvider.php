<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 6/10/2018
 * Time: 2:30 AM
 */

namespace App\Service\SpecialAccessProviders;

use App\Entity\Machine\Machine;
use App\Entity\Subsidiary;
use App\Service\AccessProvider;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MachineAccessProvider extends AccessProvider
{
    const SECTION_CODE = 'machines';

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
     * @param Machine $machine
     * @return bool
     */
    public function userIsAssociatedWithTheMachine(Machine $machine): bool
    {
        $accessLevel = $this->getAccessToTheSection();
        if ($accessLevel === self::ACL_FULL) {
            return true;
        }

        $authUser = $this->getUser();

        // Check if the user is associated with the same subsidiary
        foreach ($authUser->getSubsidiaryListByType(Subsidiary::TYPE_SUBSIDIARY) as $subsidiary) {
            if ($subsidiary->getId() === $machine->getSubsidiary()->getId()) {
                return true;
            }
        }

        // Check if the user is associated with the same partner
        foreach ($authUser->getPartnersList() as $partner) {
            foreach ($partner->getAssociatedMachines() as $partnerMachine) {
                if ($partnerMachine->getId() === $machine->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Machine $machine
     * @return bool
     */
    public function isReadable(Machine $machine): bool
    {
        switch ($this->getAccessToTheSection()) {
            case self::ACL_FULL:
                return true;

            case self::ACL_READONLY:
            case self::ACL_SUBSIDIARY_FULL:
            return $this->userIsAssociatedWithTheMachine($machine);

            case self::ACL_DENIED:
            default:
                return false;
        }
    }

    /**
     * @param Machine $machine
     * @throws AccessDeniedException
     */
    public function readAccessRequired(Machine $machine)
    {
        if (! $this->isReadable($machine)) {
            throw new AccessDeniedException("You don't have access to this machine.");
        }
    }

    /**
     * @param Machine $machine
     * @return bool
     */
    public function isWritable(Machine $machine): bool
    {
        switch ($this->getAccessToTheSection()) {
            case self::ACL_FULL:
                return true;

            case self::ACL_SUBSIDIARY_FULL:
                return $this->userIsAssociatedWithTheMachine($machine);

            case self::ACL_DENIED:
            case self::ACL_READONLY:
            default:
                return false;
        }
    }

    /**
     * @param Machine $machine
     * @throws AccessDeniedException
     */
    public function writeAccessRequired(Machine $machine)
    {
        if (! $this->isWritable($machine)) {
            throw new AccessDeniedException("You don't have write access to this machine.");
        }
    }

    /**
     * @return bool
     */
    public function canAddNewMachines(): bool
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
        if (! $this->canAddNewMachines()) {
            throw new AccessDeniedException("You can't add new machines.");
        }
    }

}

