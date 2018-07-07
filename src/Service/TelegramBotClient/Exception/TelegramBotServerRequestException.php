<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Exception;

/**
 * This bot is a server and can't handle request from client
 */
class TelegramBotServerRequestException extends TelegramBotClientException
{

}