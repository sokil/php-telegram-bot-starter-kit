<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Conversation;

use Sokil\TelegramBot\Service\ConversationManager\AbstractConversation;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;

/**
 * Show help menu
 */
class HelpConversation extends AbstractConversation
{
    /**
     * @param Update $update
     *
     * @return string
     */
    public function apply(Update $update): string
    {

    }
}