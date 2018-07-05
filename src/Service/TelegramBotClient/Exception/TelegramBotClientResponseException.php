<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Exception;

class TelegramBotClientResponseException extends TelegramBotClientException
{
    public function __construct(string $description)
    {
        parent::__construct($description);
    }
}