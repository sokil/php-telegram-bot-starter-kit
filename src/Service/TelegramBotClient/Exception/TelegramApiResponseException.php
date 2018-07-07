<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Exception;

/**
 * This bot is a client and get error response from api server
 */
class TelegramApiResponseException extends TelegramBotClientException
{
    public function __construct(string $description)
    {
        parent::__construct($description);
    }
}