<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\ConversationManager;

/**
 * Collection of all active conversations of bot with connected users
 */
class ConversationCollection
{
    /**
     * @var AbstractConversation[]
     */
    private $collectionByUserId;

    /**
     * @param int $userId
     * @param AbstractConversation $conversation
     */
    public function add(int $userId, AbstractConversation $conversation): void
    {
        $this->collectionByUserId[$userId] = $conversation;
    }

    /**
     * @param int $userId
     *
     * @return AbstractConversation
     */
    public function getByUserId(int $userId): ?AbstractConversation
    {
        return isset($this->collectionByUserId[$userId]) ? $this->collectionByUserId[$userId] : null;
    }

    /**
     * @param int $userId
     */
    public function remove(int $userId): void
    {
        unset($this->collectionByUserId[$userId]);
    }
}