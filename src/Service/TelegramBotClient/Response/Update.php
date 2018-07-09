<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Response;

use Sokil\TelegramBot\Service\TelegramBotClient\Response\Message;

/**
 * @see https://core.telegram.org/bots/webhooks#testing-your-bot-with-updates Examples
 * @see https://core.telegram.org/bots/api#update Specification of Update entity
 */
class Update
{
    /**
     * @var Message
     */
    private $message;

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }


    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->message;
    }
}