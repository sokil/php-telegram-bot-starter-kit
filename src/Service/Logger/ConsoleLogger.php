<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\Logger;

use Psr\Log\AbstractLogger;

class ConsoleLogger extends AbstractLogger
{
    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        echo sprintf(
            "[%s] %s\n%s",
            $level,
            $message,
            $context ? var_export($context, true) : null
        );
    }

}