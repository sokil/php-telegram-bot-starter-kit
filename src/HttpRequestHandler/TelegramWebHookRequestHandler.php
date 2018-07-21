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

namespace Sokil\TelegramBot\HttpRequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\Http\Response;
use Sokil\TelegramBot\Service\ConversationManager\ConversationCollection\ConversationCollectionInterface;
use Sokil\TelegramBot\Service\ConversationManager\ConversationDispatcher;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;
use Sokil\TelegramBot\Service\ConversationManager\ConversationCollection\InMemoryConversationCollection;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry as WorkflowRegistry;

/**
 * Handler for requests from Telegram
 */
class TelegramWebHookRequestHandler implements RequestHandlerInterface
{
    /**
     * @var TelegramBotClientInterface
     */
    private $telegramBotClient;

    /**
     * @var ConversationCollectionInterface
     */
    private $conversationCollection;

    /**
     * @var ConversationDispatcher
     */
    private $conversationDispatcher;

    /**
     * @var WorkflowRegistry
     */
    private $workflowRegistry;

    /**
     * @var LoggerInterfaces
     */
    private $logger;

    /**
     * @param TelegramBotClientInterface $telegramBotClient
     * @param ConversationDispatcher $conversationDispatcher
     * @param ConversationCollectionInterface $conversationCollection
     * @param WorkflowRegistry $workflowRegistry
     */
    public function __construct(
        TelegramBotClientInterface $telegramBotClient,
        ConversationDispatcher $conversationDispatcher,
        ConversationCollectionInterface $conversationCollection,
        WorkflowRegistry $workflowRegistry,
        LoggerInterface $logger
    ) {
        $this->telegramBotClient = $telegramBotClient;
        $this->conversationDispatcher = $conversationDispatcher;
        $this->conversationCollection = $conversationCollection;
        $this->workflowRegistry = $workflowRegistry;
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestBody = $request->getBody()->getContents();

        // debug
        $this->logger->debug(
            '[TelegramWebHookRequestHandler] Request accepted',
            [
                'request' => $requestBody,
            ]
        );


        // handle request
        try {
            // parse JSON
            $updateData = json_decode($requestBody, true);
            if (empty($updateData)) {
                throw new \RuntimeException('Invalid JSON');
            }

            // build update from request
            $update = $this->telegramBotClient->buildWebHookUpdateFromRequest($updateData);

            $userId = $update->getMessage()->getFrom()->getId();
            $messageText = $update->getMessage()->getText();

            // handle only textual messages
            if ($messageText !== null) {
                // try to get already started conversation
                $conversation = $this->conversationCollection->getByUserId($userId);

                // no conversation found, try to init new conversation if detected initial message
                if ($conversation === null) {
                    $conversation = $this->conversationDispatcher->dispatchConversation($messageText);
                    if ($conversation !== null) {
                        $this->conversationCollection->add($userId, $conversation);
                    }
                }

                // route request to related command handler
                if ($conversation !== null) {
                    // apply update for conversation
                    $nextState = $conversation->apply($update);

                    // get workflow for conversation
                    try {
                        $workflow = $this->workflowRegistry->get($conversation);
                    } catch (InvalidArgumentException $e) {
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
            } else {
                $this->logger->debug('[TelegramWebHookRequestHandler] Skip handling not textual message');
            }

            // build response
            return new Response(200);
        } catch (\Throwable $e) {
            $this->logger->critical('[TelegramWebHookRequestHandler] ' . $e->getMessage());

            return new Response(500);
        }
    }
}