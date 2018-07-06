<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramBotClientRequestException;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramBotClientResponseException;

class TelegramWebHookRequestHandler implements RequestHandlerInterface
{
    /**
     * @var TelegramBotClientInterface
     */
    private $telegramBotClient;

    /**
     * TelegramWebHookController constructor.
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
     * @throws TelegramBotClientResponseException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(
            200,
            [],
            $this->telegramBotClient->getWebHookInfo()->getUrl()
        );
    }
}