<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\HttpRequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
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
     * @param TelegramBotClientInterface $telegramBotClient
     * @param ConversationDispatcher $conversationDispatcher
     * @param ConversationCollection $conversationCollection
     * @param WorkflowRegistry $workflowRegistry
     */
    public function __construct(
        TelegramBotClientInterface $telegramBotClient,
        ConversationDispatcher $conversationDispatcher,
        ConversationCollection $conversationCollection,
        WorkflowRegistry $workflowRegistry
    ) {
        $this->telegramBotClient = $telegramBotClient;
        $this->conversationDispatcher = $conversationDispatcher;
        $this->conversationCollection = $conversationCollection;
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // debug
        echo $request->getBody()->getContents();

        try {
            // build update from request
            $update = $this->telegramBotClient->buildWebHookUpdateFromRequest($request);

            $userId = $update->getMessage()->getFrom()->getId();
            $messageText = $update->getMessage()->getText();

            // try to get already started conversation
            $conversation = $this->conversationCollection->getByUserId($userId);

            // no conversation found, try to init new conversation if detected initial message
            if ($conversation === null) {
                $conversation = $this->conversationDispatcher->dispatchConversation($messageText);
                $this->conversationCollection->add($conversation);
            }

            // route request to related command handler
            if ($conversation !== null) {
                // get workflow for conversation
                $workflow = $this->workflowRegistry->get($conversation);

                // apply update for conversation
                $nextState = $conversation->apply($update);

                // apply new state for conversation
                if ($workflow->can($conversation, $nextState)) {
                    $workflow->apply($conversation, $nextState);
                }

                // if conversation finished remove it from collection
                if (count($workflow->getEnabledTransitions($conversation)) === 0) {
                    $this->conversationCollection->remove($conversation);
                }
            }

            // build response
            return new Response(200);
        } catch (\Throwable $e) {
            return new Response(500);
        }
    }
}