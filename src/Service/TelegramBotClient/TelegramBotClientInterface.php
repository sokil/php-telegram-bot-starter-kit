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

namespace Sokil\TelegramBot\Service\TelegramBotClient;

use Psr\Http\Message\ServerRequestInterface;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramApiRequestException;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramApiResponseException;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Message;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\WebHookInfo;
use Sokil\TelegramBot\Service\TelegramBotClient\Type\ParseMode;

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
     * @param ParseMode|null $parseMode
     *
     * @return Message
     */
    public function sendMessage(string $chatId, string $text, ParseMode $parseMode = null): Message;
}