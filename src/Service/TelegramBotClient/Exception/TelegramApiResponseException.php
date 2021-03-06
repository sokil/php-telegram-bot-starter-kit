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
 * This bot is a client and get error response from api server
 */
class TelegramApiResponseException extends TelegramBotClientException
{
    public function __construct(string $description)
    {
        parent::__construct($description);
    }
}