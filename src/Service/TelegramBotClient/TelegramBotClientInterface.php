<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient;

use Psr\Http\Message\RequestInterface;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramApiRequestException;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramApiResponseException;
use Sokil\TelegramBot\Service\TelegramBotClient\Response\Update;
use Sokil\TelegramBot\Service\TelegramBotClient\Response\WebHookInfo;

interface TelegramBotClientInterface
{
    /**
     * @param string $url
     *
     * @throws TelegramApiRequestException
     * @throws TelegramApiResponseException
     */
    public function setWebHook(string $url): void;

    /**
     * Use this method to remove webhook integration if you decide to
     * switch back to getUpdates. Returns True on success.
     *
     * @see https://core.telegram.org/bots/api#deletewebhook
     *
     * @throws TelegramApiRequestException
     * @throws TelegramApiResponseException
     */
    public function deleteWebHook(): void;

    /**
     * @see https://core.telegram.org/bots/api#getwebhookinfo
     *
     * @return WebHookInfo
     *
     * @throws TelegramApiRequestException
     * @throws TelegramApiResponseException
     */
    public function getWebHookInfo(): WebhookInfo;

    /**
     * @param RequestInterface $request
     *
     * @throws TelegramApiRequestException
     *
     * @return Update
     *
     * @see https://core.telegram.org/bots/webhooks#testing-your-bot-with-updates Examples
     * @see https://core.telegram.org/bots/api#update Specification
     */
    public function buildWebHookUpdateFromRequest(RequestInterface $request): Update;
}