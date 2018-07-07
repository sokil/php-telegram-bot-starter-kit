<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Exception;

/**
 * This bot is a server and can't send response to client
 */
class TelegramBotServerResponseException extends TelegramBotClientException
{

}