<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\ConversationManager;

use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;

/**
 * State of conversation with user
 */
interface ConversationInterface
{
    public function apply(Update $update): void;
}