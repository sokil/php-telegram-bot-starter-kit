<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Exception;

/**
 * This bot is a client and can't request server
 */
class TelegramApiRequestException extends TelegramBotClientException
{

}