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

namespace Sokil\TelegramBot\Service\TelegramBotClient\Type;

use MyCLabs\Enum\Enum;

class ChatType extends Enum
{
    public const PRIVATE = 'private';
    public const GROUP = 'group';
    public const SUPERGROUP = 'supergroup';
    public const CHANNEL = 'channel';
}