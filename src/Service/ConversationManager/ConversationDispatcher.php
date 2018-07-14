<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\ConversationManager;

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
    public function __construct(
        array $mapConversationClassToInitialMessagePattern
    ) {
        $this->conversationToMessageMap = $mapConversationClassToInitialMessagePattern;
    }

    /**
     * @param string $initialConversationMessage
     *
     * @return AbstractConversation|null
     */
    public function dispatchConversation(string $initialConversationMessage): ?AbstractConversation
    {
        $conversation = null;

        // build new conversation relatively to initial message
        foreach ($this->conversationToMessageMap as $conversationClassName => $conversationInitialMessagePattern) {
            if ($conversationInitialMessagePattern === $initialConversationMessage) {
                // direct match
                $conversation = new $conversationClassName();
            } else if (preg_match($conversationInitialMessagePattern, $initialConversationMessage)) {
                // regex pattern match
                $conversation = new $conversationClassName();
            }
        }

        return $conversation;
    }
}