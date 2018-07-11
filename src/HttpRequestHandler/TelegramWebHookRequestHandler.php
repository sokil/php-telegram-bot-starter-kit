<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\HttpRequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use Sokil\TelegramBot\ConversationManager\ConversationDispatcher;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;
use Sokil\TelegramBot\ConversationManager\ConversationCollection;

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
     * @param TelegramBotClientInterface $telegramBotClient
     * @param ConversationDispatcher $conversationDispatcher
     * @param ConversationCollection $conversationCollection
     */
    public function __construct(
        TelegramBotClientInterface $telegramBotClient,
        ConversationDispatcher $conversationDispatcher,
        ConversationCollection $conversationCollection
    ) {
        $this->telegramBotClient = $telegramBotClient;
        $this->conversationDispatcher = $conversationDispatcher;
        $this->conversationCollection = $conversationCollection;
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
                $conversation = $this->conversationDispatcher->dispatch($messageText);
            }

            // route request to related command handler
            $conversation->apply($messageText);

            // if conversation finished remove it from collection
            if ($conversation->isFinished()) {
                $this->conversationCollection->remove($conversation);
            }

            // build response
            return new Response(200);
        } catch (\Throwable $e) {
            return new Response(500);
        }
    }
}