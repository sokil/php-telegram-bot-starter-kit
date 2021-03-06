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

namespace Sokil\TelegramBot\Service\TelegramBotClient\Struct;

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