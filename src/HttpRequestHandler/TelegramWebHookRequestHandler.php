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
use Sokil\TelegramBot\Service\ConversationManager\ConversationDispatcher;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;

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
     * @var ConversationDispatcher
     */
    private $conversationDispatcher;

    /**
     * @var LoggerInterfaces
     */
    private $logger;

    /**
     * @param TelegramBotClientInterface $telegramBotClient
     * @param ConversationDispatcher $conversationDispatcher
     */
    public function __construct(
        TelegramBotClientInterface $telegramBotClient,
        ConversationDispatcher $conversationDispatcher,
        LoggerInterface $logger
    ) {
        $this->telegramBotClient = $telegramBotClient;
        $this->conversationDispatcher = $conversationDispatcher;
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

            // dispatch update
            $this->conversationDispatcher->dispatchConversation($update);

            // build response
            return new Response(200);
        } catch (\Throwable $e) {
            $this->logger->critical('[TelegramWebHookRequestHandler] ' . $e->getMessage());

            return new Response(500);
        }
    }
}