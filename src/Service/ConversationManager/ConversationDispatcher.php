<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\ConversationManager;

/**
 * Detect conversation type by initial chat message
 */
class ConversationDispatcher
{
    /**
     * @param string $message
     *
     * @return AbstractConversation
     */
    public function dispatch(string $message): AbstractConversation
    {

    }
}