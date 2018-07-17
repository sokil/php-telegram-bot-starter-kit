<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\HttpRequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\Http\Response;
use Sokil\TelegramBot\Service\ConversationManager\ConversationDispatcher;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;
use Sokil\TelegramBot\Service\ConversationManager\ConversationCollection;
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
     * @var ConversationCollection
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
     * @param ConversationCollection $conversationCollection
     * @param WorkflowRegistry $workflowRegistry
     */
    public function __construct(
        TelegramBotClientInterface $telegramBotClient,
        ConversationDispatcher $conversationDispatcher,
        ConversationCollection $conversationCollection,
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
            '[TelegramWebHookRequestHandler] Request handled',
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
                $workflow = $this->workflowRegistry->get($conversation);

                // apply new state for conversation
                if (!empty($nextState) && $workflow->can($conversation, $nextState)) {
                    $workflow->apply($conversation, $nextState);
                }

                // if conversation finished remove it from collection
                if (count($workflow->getEnabledTransitions($conversation)) === 0) {
                    $this->conversationCollection->remove($userId);
                }
            }

            // build response
            return new Response(200);
        } catch (\Throwable $e) {
            $this->logger->critical('[TelegramWebHookRequestHandler] ' . $e->getMessage());

            return new Response(500);
        }
    }
}