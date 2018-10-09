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

class MarketingAccessProvider extends AccessProvider
{
    const MARKETING_ACCESS_DENIED = 0;
    const MARKETING_ACCESS_DISTRIBUTOR = 5;
    const MARKETING_ACCESS_MANAGER = 9;

    /**
     * @return bool
     */
    public function hasReadAccess(): bool
    {
        $accessLevel = $this->getAccessLevelTo(self::SECTION_MARKETING);

        switch ($accessLevel) {
            case MarketingAccessProvider::MARKETING_ACCESS_MANAGER:
            case MarketingAccessProvider::MARKETING_ACCESS_DISTRIBUTOR:
                return true;

            case MarketingAccessProvider::MARKETING_ACCESS_DENIED:
            default: return false;
        }
    }


    /**
     *
     */
    public function readAccessRequired()
    {
        if (! $this->hasReadAccess()) {
            throw new AccessDeniedException("Access denied");
        }
    }

    /**
     * @return bool
     */
    public function hasWriteAccess(): bool
    {
        $accessLevel = $this->getAccessLevelTo(self::SECTION_MARKETING);

        switch ($accessLevel) {
            case MarketingAccessProvider::MARKETING_ACCESS_MANAGER:
                return true;

            case MarketingAccessProvider::MARKETING_ACCESS_DISTRIBUTOR:
            case MarketingAccessProvider::MARKETING_ACCESS_DENIED:
            default: return false;
        }
    }


    /**
     *
     */
    public function writeAccessRequired()
    {
        if (! $this->hasWriteAccess()) {
            throw new AccessDeniedException("Access denied");
        }
    }
}