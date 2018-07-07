<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\RequestHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;

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
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $update = $this->telegramBotClient->buildWebHookUpdateFromRequest($request);

            // debug
            echo $update->getMessage()->getText();

            // build response
            return new Response(
                200,
                [],
                $update->getMessage()->getText()
            );
        } catch (\Throwable $e) {
            return new Response(
                500,
                [],
                'Error accepting update: ' . $e->getMessage()
            );
        }

    }
}