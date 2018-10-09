<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 4/27/2018
 * Time: 2:30 AM
 */

namespace App\Service\SpecialAccessProviders;

use App\Entity\Machine\Ticket;
use App\Service\AccessProvider;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TicketAccessProvider extends AccessProvider
{
    const SECTION_CODE = 'servicing';

    const ACL_DENIED = 0;
    const ACL_SERVICE_ENGINEER = 5;
    const ACL_ACCOUNTS = 6;
    const ACL_SERVICE_MANAGER = 7;
    const ACL_FULL = 10;


    /**
     * @param Ticket $ticket
     * @return bool
     */
    public function userIsAssociatedWithTheTicket(Ticket $ticket): bool
    {
        // Check if user's subsidiary matches with users subsidiaries
        if (in_array($ticket->getSubsidiary()->getId(), $this->getUser()->getSubsidiaryIdsList())) {
            return true;
        }
        return false;
    }


    /**
     * @return bool
     */
    public function canAddNewTickets(): bool
    {
        // If the user isn't associated with any of subsidiaries, then he cannot create a new new ticket (obviously)
        if (! $this->getUser()->getSubsidiaryList()->count()) {
            return false;
        }

        return !! count(array_intersect(
            $this->getServicingRoles(),
            [ self::ACL_SERVICE_ENGINEER, self::ACL_SERVICE_MANAGER, self::ACL_FULL ]
        ));
    }


    /**
     * @return int[]
     */
    public function getServicingRoles(): array
    {
        return $this->getAccessLevelList(self::SECTION_CODE);
    }


    /**
     * @return bool
     */
    public function hasAccessToServicingSection(): bool
    {
        return !! count($this->getServicingRoles());
    }

    /**
     * @throws AccessDeniedException
     */
    public function accessToServicingSectionRequired()
    {
        if (! $this->hasAccessToServicingSection()) {
            throw new AccessDeniedException("You don't have access to the Servicing Section.");
        }
    }


    /**
     * @param Ticket $ticket
     * @return bool
     */
    public function isViewable(Ticket $ticket): bool
    {
        $accessLevels = $this->getServicingRoles();

        // If the user has full access, then the ticket is always writable
        if (! count($accessLevels)) {
            return false;
        }

        return $this->userIsAssociatedWithTheTicket($ticket);
    }


    /**
     * @param Ticket $ticket
     * @throws AccessDeniedException
     */
    public function readAccessRequired(Ticket $ticket)
    {
        if (! $this->isViewable($ticket)) {
            throw new AccessDeniedException("You don't have access to this ticket.");
        }
    }

    /**
     * @param Ticket $ticket
     * @throws AccessDeniedException
     */
    public function writeAccessRequired(Ticket $ticket)
    {
        if (! $this->isWritable($ticket)) {
            throw new AccessDeniedException("You can't edit this ticket.");
        }
    }


    /**
     * @param Ticket $ticket
     * @return bool
     */
    public function isWritable(Ticket $ticket): bool
    {
        if (! $this->userIsAssociatedWithTheTicket($ticket)) {
            return false;
        }

        $accessLevels = $this->getServicingRoles();

        // If user has full access, then the ticket is always writable
        if (in_array(self::ACL_FULL, $accessLevels)) {
            return true;
        }


        // We consider the record isn't writable by default
        $readonly = true;

        // Service Engineer
        if (in_array(self::ACL_SERVICE_ENGINEER, $accessLevels)) {
            // For next status list ticket is editable by an engineer
            if (in_array(
                $ticket->getStatus(),
                [
                    Ticket::STATUS_OPEN,
                    Ticket::STATUS_ASSIGNED,
                    Ticket::STATUS_IN_PROGRESS,
                ]
            )) {
                $readonly = false;
            }
        }

        // Accounts
        if (in_array(self::ACL_ACCOUNTS, $accessLevels)) {
            // For next status list ticket is editable by an account
            if (in_array(
                $ticket->getStatus(),
                [
                    Ticket::STATUS_SERVICE_COMPLETE,
                    Ticket::STATUS_TO_INVOICE,
                ]
            )) {
                $readonly = false;
            }
        }

        // Service Manager
        if (in_array(self::ACL_SERVICE_MANAGER, $accessLevels)) {
            // For next status list ticket is editable by a manager
            if (in_array(
                $ticket->getStatus(),
                [
                    Ticket::STATUS_OPEN,
                    Ticket::STATUS_ASSIGNED,
                    Ticket::STATUS_IN_PROGRESS,
                    Ticket::STATUS_SERVICE_COMPLETE,
                    Ticket::STATUS_TO_INVOICE,
                ]
            )) {
                $readonly = false;
            }
        }

        return !$readonly;
    }


    /**
     * @param Ticket $ticket
     * @return bool
     */
    public function isCompletable(Ticket $ticket): bool
    {
        if (! $this->isWritable($ticket)) return false;

        // Cannot complete a service, which is completed already
        if ($ticket->getStatus() >= Ticket::STATUS_SERVICE_COMPLETE) {
            return false;
        }

        $accessLevels = $this->getServicingRoles();

        // If user has full access, then the ticket is always writable
        if (in_array(self::ACL_FULL, $accessLevels)) {
            return true;
        }


        // Service Engineer
        if (in_array(self::ACL_SERVICE_ENGINEER, $accessLevels)) {
            return true;
        }

        // Accounts
        if (in_array(self::ACL_ACCOUNTS, $accessLevels)) {
            return false;
        }

        // Service Manager
        if (in_array(self::ACL_SERVICE_MANAGER, $accessLevels)) {
            return true;
        }

        return false;
    }

    /**
     * @param Ticket $ticket
     */
    public function accessToCompleteRequired(Ticket $ticket)
    {
        if (! $this->isCompletable($ticket)) {
            throw new AccessDeniedException("You can't complete this ticket.");
        }
    }
}

