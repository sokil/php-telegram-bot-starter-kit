<?php
declare(strict_types=1);

/**
 * This file is part of the PHP Telegram Starter Kit.
 *
 * (c) Dmytro Sokil <dmytro.sokil@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sokil\TelegramBot\Conversation;

use Sokil\TelegramBot\Service\ConversationManager\AbstractConversation;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;

class WhoAmIConversation extends AbstractConversation
{
    /**
     * @param Update $update
     *
     * @return string|null Next state
     */
    public function apply(Update $update): ?string
    {
        $chatId = $update->getMessage()->getChat()->getId();

        $userId = $update->getMessage()->getFrom()->getId();
        $firstName = $update->getMessage()->getFrom()->getFirstName();
        $lastName = $update->getMessage()->getFrom()->getLastName();
        $userName = $update->getMessage()->getFrom()->getUserName();

        $message = sprintf(
            "User Id: %s\nLast name :%s\nFirst name: %s\nUsername: %s",
            $userId,
            $lastName,
            $firstName,
            $userName
        );

        $this->telegramBotClient->sendMessage(
            (string)$chatId,
            $message
        );

        return null;
    }
}