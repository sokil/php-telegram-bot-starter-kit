<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\ConversationManager;

/**
 * Detect conversation type by initial chat message
 */
class ConversationDispatcher
{
    /**
     * Map  class name of conversation to initial message pattern
     *
     * @var array
     */
    private $conversationToMessageMap;

    /**
     * @param string[] $mapConversationClassToInitialMessagePattern
     */
    public function __construct(array $mapConversationClassToInitialMessagePattern)
    {
        $this->conversationToMessageMap = $mapConversationClassToInitialMessagePattern;
    }

    /**
     * @param string $message
     *
     * @return ConversationInterface|null
     */
    public function dispatch(string $message): ?ConversationInterface
    {
        $conversation = null;

        foreach ($this->conversationToMessageMap as $conversationClassName => $conversationInitialMessagePattern) {
            if ($conversationInitialMessagePattern === $message) {
                $conversation = new $conversationClassName();
            } else if (preg_match($conversationInitialMessagePattern, $message)) {
                $conversation = new $conversationClassName();
            }
        }

        return $conversation;
    }
}