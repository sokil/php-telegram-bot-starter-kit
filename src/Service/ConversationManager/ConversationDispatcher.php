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
    private $conversationClassNames;

    /**
     * @param string[] $conversationClassNames
     */
    public function __construct(
        array $conversationClassNames
    ) {
        $this->conversationClassNames = $conversationClassNames;
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
        foreach ($this->conversationClassNames as $conversationClassName => $conversationMetadata) {
            $conversationInitialMessagePattern = $conversationMetadata['pattern'];
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