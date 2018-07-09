<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Response;

/**
 * @see https://core.telegram.org/bots/api#message
 */
class Message
{
    /**
     * @var string
     */
    private $text;

    /**
     * Message constructor.
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

}