<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 3/18/2018
 * Time: 12:46 PM
 */

namespace App\Service;


use App\Entity\User\User;
use App\Service\SpecialAccessProviders\CustomerAccessProvider;
use App\Service\SpecialAccessProviders\MachineAccessProvider;
use App\Service\SpecialAccessProviders\MarketingAccessProvider;
use App\Service\SpecialAccessProviders\ServiceContractsAccessProvider;
use App\Service\SpecialAccessProviders\TicketAccessProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessProvider
{
    /*
     * WARNING: It's not recommended to use ACCESS_ and SECTION_ constants anymore.
     * It's better to use unique constants for each AccessProvider child class separately
     * See \App\Service\SpecialAccessProviders\TicketAccessProvider for example
     */
    /* @deprecated */
    const ACCESS_DENIED = 0;
    /* @deprecated */
    const ACCESS_RO_FOR_CUSTOMER = 2;
    /* @deprecated */
    const ACCESS_RO_FOR_SUBSIDIARY = 3;
    /* @deprecated */
    const ACCESS_DISTRIBUTOR_READONLY = 3;
    /* @deprecated */
    const ACCESS_READONLY = 4;
    /* @deprecated */
    const ACCESS_WRITE = 7;
    /* @deprecated */
    const ACCESS_DISTRIBUTOR = 8;
    /* @deprecated */
    const ACCESS_SUBSIDIARY_FULL = 9;
    /* @deprecated */
    const ACCESS_FULL = 10;

    /* @deprecated */
    const SECTION_USERS = "users-section";
    /* @deprecated */
    const SECTION_USER_GROUPS = "user-groups-section";
    /* @deprecated */
    const SECTION_SUBSIDIARIES = "subsidiaries-section";
    /* @deprecated */
    const SECTION_SYSTEM_SETTINGS = "system-settings";
    /* @deprecated */
    const SECTION_PRODUCTS = "products";
    /* @deprecated */
    const SECTION_ORDERS = "orders";
    /* @deprecated */
    const SECTION_MACHINE_BOM = "machine-bom";
    /* @deprecated */
    const SECTION_MARKETING = "marketing";



    /**
     * @var null|TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * JournalService constructor.
     * @param null|TokenStorageInterface $tokenStorage
     */
    public function __construct(?TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * @return User|null
     */
    public function getUser()
    {
        if ($this->tokenStorage) {
            if ($this->tokenStorage->getToken()) {
                return $this->tokenStorage->getToken()->getUser();
            }
        }

        return null;
    }

    /**
     * Returns section codes and available options
     * @return array
     */
    public function getAccessSettingsItems(): array
    {
        $itemsList = [
            [
                "code" => self::SECTION_USER_GROUPS,
                "name" => "User Groups",
                "values" => [
                    [
                        "value" => self::ACCESS_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => self::ACCESS_FULL,
                        "name" => "Full access"
                    ]
                ]
            ],
            [
                "code" => self::SECTION_USERS,
                "name" => "Users",
                "values" => [
                    [
                        "value" => self::ACCESS_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => self::ACCESS_SUBSIDIARY_FULL,
                        "name" => "Full for subsidiary related records"
                    ],
                    [
                        "value" => self::ACCESS_FULL,
                        "name" => "Full access"
                    ]
                ]
            ],
            [
                "code" => self::SECTION_SUBSIDIARIES,
                "name" => "CYC Subsidiaries",
                "values" => [
                    [
                        "value" => self::ACCESS_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => self::ACCESS_FULL,
                        "name" => "Full access"
                    ],
                ]
            ],
            [
                "code" => self::SECTION_PRODUCTS,
                "name" => "Products",
                "values" => [
                    [
                        "value" => self::ACCESS_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => self::ACCESS_FULL,
                        "name" => "Full access"
                    ]
                ]
            ],
            [
                "code" => self::SECTION_SYSTEM_SETTINGS,
                "name" => "System settings",
                "values" => [
                    [
                        "value" => self::ACCESS_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => self::ACCESS_FULL,
                        "name" => "Full access"
                    ]
                ]
            ],
            [
                "code" => self::SECTION_ORDERS,
                "name" => "Orders",
                "values" => [
                    [
                        "value" => self::ACCESS_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => self::ACCESS_READONLY,
                        "name" => "Readonly for subsidiary related records"
                    ],
                    [
                        "value" => self::ACCESS_DISTRIBUTOR,
                        "name" => "Customer/Distributor"
                    ],
                    [
                        "value" => self::ACCESS_SUBSIDIARY_FULL,
                        "name" => "Full for subsidiary related records"
                    ],
                    [
                        "value" => self::ACCESS_FULL,
                        "name" => "Full access"
                    ]
                ]
            ],
            [
                "code" => TicketAccessProvider::SECTION_CODE,
                "name" => "Servicing",
                "values" => [
                    [
                        "value" => TicketAccessProvider::ACL_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => TicketAccessProvider::ACL_SERVICE_ENGINEER,
                        "name" => "Service Engineer"
                    ],
                    [
                        "value" => TicketAccessProvider::ACL_ACCOUNTS,
                        "name" => "Accounts"
                    ],
                    [
                        "value" => TicketAccessProvider::ACL_SERVICE_MANAGER,
                        "name" => "Service Manager"
                    ],
                    [
                        "value" => TicketAccessProvider::ACL_FULL,
                        "name" => "Full Access"
                    ]
                ]
            ],
            [
                "code" => MachineAccessProvider::SECTION_CODE,
                "name" => "Machines",
                "values" => [
                    [
                        "value" => MachineAccessProvider::ACL_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => MachineAccessProvider::ACL_READONLY,
                        "name" => "Readonly (for subsidiary/partner related records)"
                    ],
                    [
                        "value" => MachineAccessProvider::ACL_SUBSIDIARY_FULL,
                        "name" => "Full (for subsidiary related records)"
                    ],
                    [
                        "value" => MachineAccessProvider::ACL_FULL,
                        "name" => "Full access"
                    ]
                ]
            ],
            [
                "code" => CustomerAccessProvider::SECTION_CODE,
                "name" => "Customers",
                "values" => [
                    [
                        "value" => CustomerAccessProvider::ACL_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => CustomerAccessProvider::ACL_READONLY,
                        "name" => "Readonly (for subsidiary/partner related records)"
                    ],
                    [
                        "value" => CustomerAccessProvider::ACL_SUBSIDIARY_FULL,
                        "name" => "Full (for subsidiary related records)"
                    ],
                    [
                        "value" => CustomerAccessProvider::ACL_FULL,
                        "name" => "Full access"
                    ]
                ]
            ],
            [
                "code" => ServiceContractsAccessProvider::SECTION_CODE,
                "name" => "Service Contracts",
                "values" => [
                    [
                        "value" => ServiceContractsAccessProvider::ACL_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => ServiceContractsAccessProvider::ACL_READONLY,
                        "name" => "Readonly (for subsidiary/partner related records)"
                    ],
                    [
                        "value" => ServiceContractsAccessProvider::ACL_SUBSIDIARY_FULL,
                        "name" => "Full (for subsidiary related records)"
                    ],
                    [
                        "value" => ServiceContractsAccessProvider::ACL_FULL,
                        "name" => "Full access"
                    ]
                ]
            ],
            [
                "code" => self::SECTION_MACHINE_BOM,
                "name" => "Machine BOM",
                "values" => [
                    [
                        "value" => self::ACCESS_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => self::ACCESS_DISTRIBUTOR_READONLY,
                        "name" => "Readonly (Distributor)"
                    ],
                    [
                        "value" => self::ACCESS_READONLY,
                        "name" => "Readonly"
                    ],
                    [
                        "value" => self::ACCESS_FULL,
                        "name" => "Full access"
                    ]
                ]
            ],
            [
                "code" => self::SECTION_MARKETING,
                "name" => "Marketing",
                "values" => [
                    [
                        "value" => MarketingAccessProvider::MARKETING_ACCESS_DENIED,
                        "name" => "No access"
                    ],
                    [
                        "value" => MarketingAccessProvider::MARKETING_ACCESS_DISTRIBUTOR,
                        "name" => "Distributor"
                    ],
                    [
                        "value" => MarketingAccessProvider::MARKETING_ACCESS_MANAGER,
                        "name" => "Marketing Manager"
                    ]
                ]
            ],

        ];

        // Sort items by name
        usort(
            $itemsList,
            function ($i1, $i2) {
                return $i1['name'] > $i2['name'] ? 1 : -1;
            }
        );

        return $itemsList;
    }


    /**
     * Returns the highest access level value to the section
     * @param string $sectionCode
     * @return int
     */
    public function getAccessLevelTo($sectionCode): int
    {
        $user = $this->getUser();
        if (! $user) return 0;

        $maxValue = 0;

        foreach ($user->getGroupList() as $group) {
            // skip inactive groups permissions
            if (! $group->getStatus()) continue;

            $groupAccessValue = $group->getAccessLevelTo($sectionCode);
            if ($groupAccessValue > $maxValue) $maxValue = $groupAccessValue;
        }

        return $maxValue;
    }

    /**
     * If the user belong to multiple groups, which give give different type of access to the same section,
     * this method returns the list of assigned values
     *
     * @param string $sectionCode
     * @return int[]
     */
    public function getAccessLevelList($sectionCode): array
    {
        $user = $this->getUser();
        if (! $user) return [];

        $result = [];

        foreach ($user->getGroupList() as $group) {
            // skip inactive groups permissions
            if (! $group->getStatus()) continue;

            $accessLevel = $group->getAccessLevelTo($sectionCode);
            if ($accessLevel) {
                $result[] = $accessLevel;
            }

        }

        return $result;
    }


    /**
     * @param string $sectionCode
     * @param int $minLevel
     * @return bool
     */
    public function hasAccessTo($sectionCode, $minLevel = 1): bool
    {
        $level = $this->getAccessLevelTo($sectionCode);
        return $level >= $minLevel;
    }


    /**
     * @param $sectionCode
     * @param int|string $minLevel
     * @return int
     * @throws AccessDeniedException
     */
    public function requiresAccessTo($sectionCode, $minLevel = 1)
    {
        if (! $this->hasAccessTo($sectionCode, $minLevel)) {
            throw new AccessDeniedException("Access denied");
        }

        return $this->getAccessLevelTo($sectionCode);
    }


    /**
     * @return bool
     */
    public function adminMenuIsVisible(): bool
    {
        $accessItems = [
            self::SECTION_SUBSIDIARIES,
            self::SECTION_PRODUCTS,
            self::SECTION_USERS,
            self::SECTION_USER_GROUPS,
            self::SECTION_SYSTEM_SETTINGS,
            self::SECTION_MACHINE_BOM,
        ];

        foreach ($accessItems as $item) {
            if ($this->getAccessLevelTo($item)) return true;
        }

        return false;
    }
}