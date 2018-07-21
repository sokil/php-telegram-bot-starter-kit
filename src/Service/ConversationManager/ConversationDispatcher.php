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

use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;
use Symfony\Component\DependencyInjection\ContainerInterface as ConversationLocator;
use Sokil\TelegramBot\Service\ConversationManager\ConversationCollection\ConversationCollectionInterface;
use Symfony\Component\Workflow\Exception\InvalidArgumentException as WorkflowInvalidArgumentException;
use Symfony\Component\Workflow\Registry as WorkflowRegistry;

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
     * @var ConversationCollectionInterface
     */
    private $conversationCollection;

    /**
     * @var ConversationLocator
     */
    private $conversationLocator;

    /**
     * @var WorkflowRegistry
     */
    private $workflowRegistry;

    /**
     * @param array[] $conversationDefinitions See format in "Config/conversations.yml"
     * @param ConversationCollectionInterface $conversationCollection
     * @param ConversationLocator $conversationLocator
     * @param WorkflowRegistry $workflowRegistry
     */
    public function __construct(
        array $conversationDefinitions,
        ConversationCollectionInterface $conversationCollection,
        ConversationLocator $conversationLocator,
        WorkflowRegistry $workflowRegistry
    ) {
        $this->conversationDefinitions = $conversationDefinitions;
        $this->conversationCollection = $conversationCollection;
        $this->conversationLocator = $conversationLocator;
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @param Update $update
     *
     * @return AbstractConversation|null
     *
     * @throws \Exception
     */
    public function dispatchConversation(Update $update): ?AbstractConversation
    {
        // get intent from update
        // currently handled only textual messages
        $intentMessage = $update->getMessage()->getText();
        if ($intentMessage === null) {
            return null;
        }

        // get command from intent
        if (preg_match('#^/([a-zA-Z0-9_]{0,32})#', $intentMessage, $matched)) {
            // Telegram bot command accepted
            // @link https://core.telegram.org/bots#commands
            $command = $matched[1];
        } else {
            $command = $this->processNaturalLanguageIntent($intentMessage);
        }

        // build new conversation relatively to command
        $conversation = null;
        if ($command !== null) {
            foreach ($this->conversationDefinitions as $conversationServiceId => $conversationMetadata) {
                // try to match
                $isMatched = false;
                if (isset($conversationMetadata['command'])) {
                    // direct match
                    if ($conversationMetadata['command'] === $command) {
                        $isMatched = true;
                    }
                } else if (isset($conversationMetadata['commandRegex'])) {
                    // regex pattern match
                    if (preg_match($conversationMetadata['commandRegex'], $command)) {
                        $isMatched = true;
                    }
                } else {
                    throw new \Exception('Conversation must be configured in Config/conversations.yml with parameter "command" or "commandRegex"');
                }

                // if matched - build conversation
                if ($isMatched) {
                    /** @var AbstractConversation $conversation */
                    $conversation = $this->conversationLocator->get($conversationServiceId);
                    break;
                }
            }
        }

        // add conversation to collection of active conversations
        $userId = $update->getMessage()->getFrom()->getId();
        if ($conversation !== null) {
            $this->conversationCollection->add($userId, $conversation);
        } else {
            // message is not intent, maybe it is part of previous not finished conversation
            $conversation = $this->conversationCollection->getByUserId($userId);
        }

        // apply update to conversation
        if ($conversation !== null) {
            // apply update for conversation
            $nextState = $conversation->apply($update);

            // get workflow for conversation
            try {
                $workflow = $this->workflowRegistry->get($conversation);
            } catch (WorkflowInvalidArgumentException $e) {
                $workflow = null;
            }

            // apply workflow logic if present, or finish command execution
            if ($workflow !== null) {
                // apply new state for conversation
                if (!empty($nextState) && $workflow->can($conversation, $nextState)) {
                    $workflow->apply($conversation, $nextState);
                }

                // if conversation finished remove it from collection
                if (count($workflow->getEnabledTransitions($conversation)) === 0) {
                    $this->conversationCollection->remove($userId);
                }
            } else {
                // finish command execution
                $this->conversationCollection->remove($userId);
            }
        }



        return $conversation;
    }

    /**
     * Extract command from intent
     *
     * @todo: currently not implemented
     *
     * @param string $intentMessage
     *
     * @return null|string
     */
    private function processNaturalLanguageIntent(string $intentMessage) : ?string
    {
        return null;
    }

}