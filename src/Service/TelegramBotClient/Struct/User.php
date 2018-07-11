<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Struct;

/**
 * This object represents a Telegram user or bot.
 *
 * @link https://core.telegram.org/bots/api#user
 */
class User
{
    /**
     * Unique identifier for this chat
     *
     * @var int
     */
    private $id;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}