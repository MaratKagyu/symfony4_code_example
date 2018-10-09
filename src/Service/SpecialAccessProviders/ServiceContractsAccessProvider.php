<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 6/10/2018
 * Time: 2:30 AM
 */

namespace App\Service\SpecialAccessProviders;

use App\Entity\Machine\MachineServiceContract as Contract;
use App\Entity\Subsidiary;
use App\Service\AccessProvider;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ServiceContractsAccessProvider extends AccessProvider
{
    const SECTION_CODE = 'service-contracts';

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
     * @param Contract $contract
     * @return bool
     */
    public function userIsAssociatedWithTheContract(Contract $contract): bool
    {
        $accessLevel = $this->getAccessToTheSection();
        if ($accessLevel === self::ACL_FULL) {
            return true;
        }

        $authUser = $this->getUser();

        // Check if the contract is associated with the same subsidiary
        foreach ($authUser->getSubsidiaryListByType(Subsidiary::TYPE_SUBSIDIARY) as $subsidiary) {
            if ($subsidiary->getId() === $contract->getSubsidiary()->getId()) {
                return true;
            }
        }

        // Check if the contract is associated with the partner->machine
        foreach ($authUser->getPartnersList() as $partner) {
            foreach ($partner->getAssociatedMachines() as $machine) {
                if (! $contract->getMachine()) {
                    continue;
                }
                if ($machine->getId() === $contract->getMachine()->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Contract $contract
     * @return bool
     */
    public function isReadable(Contract $contract): bool
    {
        switch ($this->getAccessToTheSection()) {
            case self::ACL_FULL:
                return true;

            case self::ACL_READONLY:
            case self::ACL_SUBSIDIARY_FULL:
            return $this->userIsAssociatedWithTheContract($contract);

            case self::ACL_DENIED:
            default:
                return false;
        }
    }

    /**
     * @param Contract $contract
     * @throws AccessDeniedException
     */
    public function readAccessRequired(Contract $contract)
    {
        if (! $this->isReadable($contract)) {
            throw new AccessDeniedException("You don't have access to this contract.");
        }
    }

    /**
     * @param Contract $contract
     * @return bool
     */
    public function isWritable(Contract $contract): bool
    {
        switch ($this->getAccessToTheSection()) {
            case self::ACL_FULL:
                return true;

            case self::ACL_SUBSIDIARY_FULL:
                return $this->userIsAssociatedWithTheContract($contract);

            case self::ACL_DENIED:
            case self::ACL_READONLY:
            default:
                return false;
        }
    }

    /**
     * @param Contract $contract
     * @throws AccessDeniedException
     */
    public function writeAccessRequired(Contract $contract)
    {
        if (! $this->isWritable($contract)) {
            throw new AccessDeniedException("You don't have write access to this contract.");
        }
    }

    /**
     * @return bool
     */
    public function canAddNewContracts(): bool
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
        if (! $this->canAddNewContracts()) {
            throw new AccessDeniedException("You can't add new contracts.");
        }
    }

}

