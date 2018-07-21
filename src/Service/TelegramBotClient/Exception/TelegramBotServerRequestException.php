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

namespace Sokil\TelegramBot\Service\TelegramBotClient\Exception;

/**
 * This bot is a server and can't handle request from client
 */
class TelegramBotServerRequestException extends TelegramBotClientException
{

}