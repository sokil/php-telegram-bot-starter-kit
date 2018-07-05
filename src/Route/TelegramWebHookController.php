<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Route;

use React\Http\Response;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;

class TelegramWebHookController
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
     * @return Response
     */
    public function handle(): Response
    {
        return new Response(
            200,
            [],
            $this->telegramBotClient->getWebHookInfo()->getUrl()
        );
    }
}