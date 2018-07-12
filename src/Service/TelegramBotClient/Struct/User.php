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
     * @var string
     */
    private $firstName;

    /**
     * @var string|null
     */
    private $lastName;

    /**
     * @var string|null
     */
    private $userName;

    /**
     * User constructor.
     * @param int $id
     * @param string $firstName
     * @param null|string $lastName
     * @param null|string $userName
     */
    public function __construct(int $id, string $firstName, ?string $lastName, ?string $userName)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->userName = $userName;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return null|string
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }
}