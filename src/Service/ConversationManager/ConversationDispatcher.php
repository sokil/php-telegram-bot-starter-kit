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
     * @param array[] $conversationDefinitions See format in "Config/conversations.yml"
     * @param TelegramBotClientInterface $telegramBotClient
     */
    public function __construct(
        array $conversationDefinitions,
        TelegramBotClientInterface $telegramBotClient
    ) {
        $this->conversationClassNames = $conversationDefinitions;
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
            // try to match
            $isMatched = false;
            if (isset($conversationMetadata['command']) && $conversationMetadata['command'] === $initialConversationMessage) {
                // direct match
                $isMatched = true;
            } else if (isset($conversationMetadata['regex']) && preg_match($conversationMetadata['regex'], $initialConversationMessage)) {
                // regex pattern match
                $isMatched = true;
            }

            // if matched - build conversation
            if ($isMatched) {
                $conversation = new $conversationClassName($this->telegramBotClient);
                break;
            }
        }

        return $conversation;
    }
}