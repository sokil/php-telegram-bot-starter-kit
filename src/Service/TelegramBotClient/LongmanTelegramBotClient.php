<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient;

use Psr\Http\Message\ServerRequestInterface;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\Update as LongmanTelegramBotUpdate;
use Longman\TelegramBot\Exception\TelegramException as LongmanTelegramBotException;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramApiRequestException;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramApiResponseException;
use Sokil\TelegramBot\Service\TelegramBotClient\Exception\TelegramBotServerRequestException;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Chat;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Message;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\User;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\WebHookInfo;
use Sokil\TelegramBot\Service\TelegramBotClient\Type\ChatType;
use Sokil\TelegramBot\Service\TelegramBotClient\Type\ParseMode;

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
     * @throws TelegramApiRequestException
     * @throws TelegramApiResponseException
     */
    public function setWebHook(string $url): void
    {
        try {
            $response = Request::setWebhook([
                'url' => $url,
            ]);
        } catch (\Throwable $e) {
            throw new TelegramApiRequestException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$response->isOk()) {
            throw new TelegramApiResponseException($response->getDescription());
        }
    }

    /**
     * @throws TelegramApiRequestException
     * @throws TelegramApiResponseException
     */
    public function deleteWebHook(): void
    {
        try {
            $response = Request::deleteWebhook();
        } catch (\Throwable $e) {
            throw new TelegramApiRequestException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$response->isOk()) {
            throw new TelegramApiResponseException($response->getDescription());
        }
    }

    /**
     * @return WebHookInfo
     *
     * @throws TelegramApiRequestException
     * @throws TelegramApiResponseException
     */
    public function getWebHookInfo(): WebhookInfo
    {
        try {
            $response = Request::getWebhookInfo();
        } catch (\Throwable $e) {
            throw new TelegramApiRequestException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$response->isOk()) {
            throw new TelegramApiResponseException($response->getDescription());
        }

        /** @var \Longman\TelegramBot\Entities\WebhookInfo $result */
        $result = $response->getResult();

        $webHookInfo = new WebHookInfo(
            $result->getUrl()
        );

        return $webHookInfo;
    }

    /**
     * @param array $updateData
     *
     * @throws TelegramBotServerRequestException
     *
     * @return Update
     */
    public function buildWebHookUpdateFromRequest(array $updateData): Update
    {
        try {
            $update = new LongmanTelegramBotUpdate($updateData);
        } catch (LongmanTelegramBotException $e) {
            throw new TelegramBotServerRequestException('Can not create Update object');
        }

        /** @var \Longman\TelegramBot\Entities\Message $message */
        $message = $update->getUpdateContent();

        return new Update(
            new Message(
                new Chat(
                    $message->getChat()->getId(),
                    new ChatType($message->getChat()->getType())
                ),
                new User(
                    $message->getFrom()->getId(),
                    $message->getFrom()->getFirstName(),
                    $message->getFrom()->getLastName(),
                    $message->getFrom()->getUsername()
                ),
                $message->getText()
            )
        );
    }

    /**
     * @param string $chatId
     * @param string $text
     * @param ParseMode|null $parseMode
     *
     * @return Message
     *
     * @throws TelegramApiRequestException
     * @throws TelegramApiResponseException
     */
    public function sendMessage(string $chatId, string $text, ParseMode $parseMode = null): Message
    {
        try {
            $response = Request::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $parseMode ? $parseMode->getValue() : null,
            ]);
        } catch (\Throwable $e) {
            throw new TelegramApiRequestException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$response->isOk()) {
            throw new TelegramApiResponseException($response->getDescription());
        }

        /** @var \Longman\TelegramBot\Entities\Message $message */
        $message = $response->getResult();

        // @todo duplicate Message object build, same with buildWebHookUpdateFromRequest
        return new Message(
            new Chat(
                $message->getChat()->getId(),
                new ChatType($message->getChat()->getType())
            ),
            new User(
                $message->getFrom()->getId(),
                $message->getFrom()->getFirstName(),
                $message->getFrom()->getLastName(),
                $message->getFrom()->getUsername()
            ),
            $message->getText()
        );
    }
}