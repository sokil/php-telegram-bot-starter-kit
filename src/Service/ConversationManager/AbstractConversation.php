<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\ConversationManager;

use Symfony\Component\Workflow\Workflow;

/**
 * State of conversation with user
 */
class AbstractConversation
{
    /**
     * Unique identifier of user this bot talks to.
     *
     * @var int
     */
    private $withUserId;

    /**
     * Null when no state required (just answer to question and stop conversation
     *
     * @var Workflow|null
     */
    private $workflow;

    /**
     * @param int $withUserId
     * @param Workflow|null $workflow
     */
    public function __construct(int $withUserId, ?Workflow $workflow)
    {
        $this->withUserId = $withUserId;
        $this->workflow = $workflow;
    }

    /**
     * @return int
     */
    public function getWithUserId(): int
    {
        return $this->withUserId;
    }
}