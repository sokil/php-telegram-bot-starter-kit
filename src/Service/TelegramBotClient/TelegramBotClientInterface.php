<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient;

use Psr\Http\Message\ServerRequestInterface;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramApiRequestException;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramApiResponseException;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Message;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\WebHookInfo;

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
     * @param array $updateData
     *
     * @throws TelegramApiRequestException
     *
     * @return Update
     *
     * @see https://core.telegram.org/bots/webhooks#testing-your-bot-with-updates Examples
     * @see https://core.telegram.org/bots/api#update Specification
     */
    public function buildWebHookUpdateFromRequest(array $updateData): Update;

    /**
     * @link https://core.telegram.org/bots/api#sendmessage
     *
     * @param string $chatId Unique identifier for the target chat or username of the target channel (in the format @channelusername)
     * @param string $text Text of the message to be sent
     *
     * @return Message
     */
    public function sendMessage(string $chatId, string $text): Message;
}