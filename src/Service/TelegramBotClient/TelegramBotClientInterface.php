<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient;

use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramBotClientRequestException;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramBotClientResponseException;
use Sokil\TelegramBot\Service\TelegramBotClient\Response\WebHookInfo;

interface TelegramBotClientInterface
{
    /**
     * @param string $url
     *
     * @throws TelegramBotClientRequestException
     * @throws TelegramBotClientResponseException
     */
    public function setWebHook(string $url): void;

    /**
     * Use this method to remove webhook integration if you decide to
     * switch back to getUpdates. Returns True on success.
     *
     * @see https://core.telegram.org/bots/api#deletewebhook
     *
     * @throws TelegramBotClientRequestException
     * @throws TelegramBotClientResponseException
     */
    public function deleteWebHook(): void;

    /**
     * @see https://core.telegram.org/bots/api#getwebhookinfo
     *
     * @return WebHookInfo
     *
     * @throws TelegramBotClientRequestException
     * @throws TelegramBotClientResponseException
     */
    public function getWebHookInfo(): WebhookInfo;
}