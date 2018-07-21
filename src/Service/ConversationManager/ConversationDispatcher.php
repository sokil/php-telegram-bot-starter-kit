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

namespace Sokil\TelegramBot\Service\ConversationManager;

use Symfony\Component\DependencyInjection\ContainerInterface as ConversationLocator;

/**
 * Detect conversation type by initial chat message
 */
class ConversationDispatcher
{
    /**
     * Map service id of conversation to conversation parameters collection
     *
     * @var array
     */
    private $conversationDefinitions;

    /**
     * @var ConversationLocator
     */
    private $conversationLocator;

    /**
     * @param array[] $conversationDefinitions See format in "Config/conversations.yml"
     * @param ConversationLocator $conversationLocator
     */
    public function __construct(
        array $conversationDefinitions,
        ConversationLocator $conversationLocator
    ) {
        $this->conversationDefinitions = $conversationDefinitions;
        $this->conversationLocator = $conversationLocator;
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
        foreach ($this->conversationDefinitions as $conversationServiceId => $conversationMetadata) {
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
                $conversation = $this->conversationLocator->get($conversationServiceId);
                break;
            }
        }

        return $conversation;
    }
}