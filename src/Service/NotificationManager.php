<?php
/**
 * Created by PhpStorm.
 * User: MaratMS
 * Date: 5/3/2018
 * Time: 12:29 AM
 */

namespace App\Service;

use App\Entity\Machine\Ticket;
use App\Entity\User\User;
use App\Entity\User\UserGroup;
use App\Repository\User\UserRepository;
use App\Service\SpecialAccessProviders\TicketAccessProvider;
use Doctrine\DBAL\DBALException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;

class NotificationManager
{
    /**
     * @var string
     */
    private $senderEmail = "";

    /**
     * @var string
     */
    private $senderName = "";

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * NotificationManager constructor.
     * @param ContainerInterface $container
     * @param \Swift_Mailer $mailer
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ContainerInterface $container,
        \Swift_Mailer $mailer,
        EntityManagerInterface $entityManager
    ){
        $this->senderEmail = $container->getParameter("app.mailer.senderEmail");
        $this->senderName = $container->getParameter("app.mailer.senderName");
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }


    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private static function getArrayChange($array1, $array2)
    {
        $result = [];

        foreach ($array1 as $key => $value) {
            if (isset($array2[$key])) {
                if ($array2[$key] != $value) {
                    $result[$key] = $array2[$key];
                }
            } else {
                $result[$key] = null;
            }

        }

        foreach ($array2 as $key => $value) {
            if (! isset($array1[$key])) {
                $result[$key] = $value;
            }

        }

        return $result;
    }


    /**
     * Detects which data was changed and sends notifications to Engineers or Accounts (if needed)
     *
     * @param array $ticketPreviousState
     * @param Ticket $ticket
     * @throws DBALException
     */
    public function processTicketNotifications($ticketPreviousState, $ticket)
    {
        $changes = self::getArrayChange($ticketPreviousState, $ticket->toArray());

        // 1. ENGINEER NOTIFICATION
        // If the ticket is assigned to a new user
        if (isset($changes['assignedToId']) && $ticket->getAssignedTo() && $ticket->getAssignedTo()->getEmail()) {

            $message = (new \Swift_Message('Ticket assigned #' . $ticket->getTicketId()))

                ->setTo($ticket->getAssignedTo()->getEmail())
                ->setFrom([$this->senderEmail => $this->senderName])
                ->setBody(
                    "You've been assigned to ticket #{$ticket->getTicketId()}.\n" .
                    "This message was auto generated by the Cyclomedica Order Portal.",
                    'text/plain'
                )
                ->addPart(
                    "<p>You've been assigned to ticket #{$ticket->getTicketId()}.</p>" .
                    "<p>This message was auto generated by the Cyclomedica Order Portal.</p>",
                    'text/html'
                )
            ;

            $this->mailer->send($message);
        }

        // 2. Accounts users notification
        // If the ticket is set as complete, we send notifications to Accounts users
        if (isset($changes['status']) && ($ticket->getStatus() == Ticket::STATUS_SERVICE_COMPLETE)) {
            // Load Accounts Users
            $groupList = array_filter(
                $this->entityManager->getRepository(UserGroup::class)->findAll(),
                function (UserGroup $group) {
                    return in_array(
                        $group->getAccessLevelTo(TicketAccessProvider::SECTION_CODE),
                        [ TicketAccessProvider::ACL_ACCOUNTS, TicketAccessProvider::ACL_SERVICE_MANAGER ]
                    );
                }
            );

            /* @var UserRepository $userRepo*/
            $userRepo = $this->entityManager->getRepository(User::class);

            // Extract users' emails
            $mailingList = array_filter(
                array_map(
                    function (User $user) { return $user->getEmail(); },
                    $userRepo->findUsersInGroups($groupList)
                ),
                function ($email) { return !! $email; }
            );

            if (count($mailingList)) {
                $message = (new \Swift_Message('Ticket #' . $ticket->getTicketId() . " complete"))

                    ->setTo($mailingList)
                    ->setFrom([$this->senderEmail => $this->senderName])
                    ->setBody(
                        "Ticket #{$ticket->getTicketId()} complete.\n" .
                        "This message was auto generated by the Cyclomedica Order Portal.",
                        'text/plain'
                    )
                    ->addPart(
                        "<p>Ticket #{$ticket->getTicketId()} complete.</p>" .
                        "<p>This message was auto generated by the Cyclomedica Order Portal.</p>",
                        'text/html'
                    )
                ;

                $this->mailer->send($message);
            }
        }

        // 3. Customer notification
        // TODO: Customer notification
    }



}