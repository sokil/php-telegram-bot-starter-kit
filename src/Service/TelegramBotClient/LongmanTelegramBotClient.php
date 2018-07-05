<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramBotClientRequestException;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramBotClientResponseException;
use Sokil\TelegramBot\Service\TelegramBotClient\Response\WebHookInfo;

/**
 * Adapter to Longman's Telegram API client
 *
 * @see https://github.com/php-telegram-bot
 */
class LongmanTelegramBotClient implements TelegramBotClientInterface
{
    /**
     * @var Telegram
     */
    private $telegramClient;

    /**
     * @param Telegram $telegramClient
     */
    public function __construct(Telegram $telegramClient)
    {
        $this->telegramClient = $telegramClient;
    }

    /**
     * @param string $url
     *
     * @throws TelegramBotClientRequestException
     * @throws TelegramBotClientResponseException
     */
    public function setWebHook(string $url): void
    {
        try {
            $response = Request::setWebhook([
                'url' => $url,
            ]);
        } catch (\Throwable $e) {
            throw new TelegramBotClientRequestException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$response->isOk()) {
            throw new TelegramBotClientResponseException($response->getDescription());
        }
    }

    /**
     * @throws TelegramBotClientRequestException
     * @throws TelegramBotClientResponseException
     */
    public function deleteWebHook(): void
    {
        try {
            $response = Request::deleteWebhook();
        } catch (\Throwable $e) {
            throw new TelegramBotClientRequestException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$response->isOk()) {
            throw new TelegramBotClientResponseException($response->getDescription());
        }
    }

    /**
     * @return WebHookInfo
     *
     * @throws TelegramBotClientRequestException
     * @throws TelegramBotClientResponseException
     */
    public function getWebHookInfo(): WebhookInfo
    {
        try {
            $response = Request::getWebhookInfo();
        } catch (\Throwable $e) {
            throw new TelegramBotClientRequestException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$response->isOk()) {
            throw new TelegramBotClientResponseException($response->getDescription());
        }

        /** @var \Longman\TelegramBot\Entities\WebhookInfo $result */
        $result = $response->getResult();

        $webHookInfo = new WebHookInfo(
            $result->getUrl()
        );

        return $webHookInfo;
    }
}