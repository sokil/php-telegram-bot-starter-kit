<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramBotClientRequestException;

class TelegramWebHookRequestHandler implements RequestHandlerInterface
{
    /**
     * @var TelegramBotClientInterface
     */
    private $telegramBotClient;

    /**
     * @param TelegramBotClientInterface $telegramBotClient
     */
    public function __construct(TelegramBotClientInterface $telegramBotClient)
    {
        $this->telegramBotClient = $telegramBotClient;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws TelegramBotClientRequestException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->telegramBotClient->handleWebHook();

        return new Response(
            200,
            [],
            'OK'
        );
    }
}