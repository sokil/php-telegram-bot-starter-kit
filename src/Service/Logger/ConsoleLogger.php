<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends AbstractLogger
{
    /**
     * @var OutputInterface
     */
    private $output;

    private const ERROR_FORMAT = [
        LogLevel::DEBUG => 'comment',
        LogLevel::INFO => 'info',
        LogLevel::NOTICE => 'info',
        LogLevel::WARNING => 'question',
        LogLevel::ERROR => 'error',
        LogLevel::CRITICAL => 'error',
        LogLevel::ALERT => 'error',
        LogLevel::EMERGENCY => 'error',
    ];

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $contextLogString = null;
        if (!empty($context)) {
            $contextLogString = sprintf(
                "\n<fg=white>%s</>",
                var_export($context, true)
            );
        }

        $this->output->writeln(sprintf(
            '<%1$s>[%2$s]</%1$s> %3$s%4$s',
            self::ERROR_FORMAT[$level] ?? 'error',
            $level,
            $message,
            $contextLogString
        ));
    }

}