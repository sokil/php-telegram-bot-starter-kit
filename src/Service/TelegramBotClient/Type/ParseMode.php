<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Type;

use MyCLabs\Enum\Enum;

class ParseMode extends Enum
{
    public const MARKDOWN = 'Markdown';
    public const HTML = 'HTML';
}