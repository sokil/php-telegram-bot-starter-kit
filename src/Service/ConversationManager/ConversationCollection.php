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
     * @param AbstractConversation $conversation
     */
    public function add(AbstractConversation $conversation): void
    {
        $this->collectionByUserId[$conversation->getWithUserId()] = $conversation;
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
     * @param AbstractConversation $conversation
     */
    public function remove(AbstractConversation $conversation): void
    {
        unset($this->collectionByUserId[$conversation->getWithUserId()]);
    }
}