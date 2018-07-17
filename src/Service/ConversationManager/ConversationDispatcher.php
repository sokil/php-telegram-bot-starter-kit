<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\ConversationManager;

use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;

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
     * @var TelegramBotClientInterface
     */
    private $telegramBotClient;

    /**
     * @param string[] $conversationClassNames
     */
    public function __construct(
        array $conversationClassNames,
        TelegramBotClientInterface $telegramBotClient
    ) {
        $this->conversationClassNames = $conversationClassNames;
        $this->telegramBotClient = $telegramBotClient;
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

            // try to match
            $isMatched = false;
            if ($conversationInitialMessagePattern === $initialConversationMessage) {
                // direct match
                $isMatched = true;
            } else if (preg_match($conversationInitialMessagePattern, $initialConversationMessage)) {
                // regex pattern match
                $isMatched = true;
            }

            // if matched - build conversation
            $conversation = new $conversationClassName($this->telegramBotClient);

        }

        return $conversation;
    }
}