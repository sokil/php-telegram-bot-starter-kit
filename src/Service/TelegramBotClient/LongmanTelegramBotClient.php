<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient;

use Psr\Http\Message\RequestInterface;
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
     * @param RequestInterface $request
     *
     * @throws TelegramBotServerRequestException
     *
     * @return Update
     */
    public function buildWebHookUpdateFromRequest(RequestInterface $request): Update
    {
        try {
            $input = $request->getBody()->getContents();
        } catch (\Throwable $e) {
            throw new TelegramBotServerRequestException($e->getMessage(), $e->getCode(), $e);
        }

        $updateData = json_decode($input, true);
        if (empty($updateData)) {
            throw new TelegramBotServerRequestException('Invalid JSON');
        }

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
     *
     * @return Message
     */
    public function sendMessage(string $chatId, string $text): Message
    {

    }
}