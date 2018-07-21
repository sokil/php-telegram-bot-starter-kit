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

namespace Sokil\TelegramBot\Service\ConversationManager\ConversationCollection;

use Sokil\TelegramBot\Service\ConversationManager\AbstractConversation;

/**
 * Collection of all active conversations of bot with connected users
 */
interface ConversationCollectionInterface
{
    /**
     * @param int $userId
     * @param AbstractConversation $conversation
     */
    public function add(int $userId, AbstractConversation $conversation): void;

    /**
     * @param int $userId
     *
     * @return AbstractConversation
     */
    public function getByUserId(int $userId): ?AbstractConversation;

    /**
     * @param int $userId
     */
    public function remove(int $userId): void;
}