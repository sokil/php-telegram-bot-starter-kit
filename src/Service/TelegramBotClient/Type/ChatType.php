<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Type;

use MyCLabs\Enum\Enum;

class ChatType extends Enum
{
    public const PRIVATE = 'private';
    public const GROUP = 'group';
    public const SUPERGROUP = 'supergroup';
    public const CHANNEL = 'channel';
}