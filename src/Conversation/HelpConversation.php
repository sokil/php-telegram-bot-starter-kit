<?php
declare(strict_types=1);

namespace Sokikl\TelegramBot\Conversation;

use Sokil\TelegramBot\ConversationManager\AbstractConversation;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;

/**
 * Show help menu
 */
class HelpConversation extends AbstractConversation
{
    /**
     * Populated with workflow
     *
     * @var string
     */
    private $state;

    /**
     * @param Update $update
     */
    public function apply(Update $update): void
    {

    }
}