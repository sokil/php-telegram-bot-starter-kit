<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Exception;

/**
 * @see https://core.telegram.org/bots/api#responseparameters
 */
class ErrorResponseParameters
{
    /**
     * The group has been migrated to a supergroup with the specified identifier.
     * This number may be greater than 32 bits and some programming languages may have
     * difficulty/silent defects in interpreting it. But it is smaller than 52 bits,
     * so a signed 64 bit integer or double-precision float type are safe for storing this identifier.
     *
     * @var int|null
     */
    private $migrateToChatId;

    /**
     * In case of exceeding flood control, the number of seconds left to wait before the request can be repeated
     *
     * @var int|null
     */
    private $retryAfter;

    /**
     * @param int|null $migrateToChatId
     * @param int|null $retryAfter
     */
    public function __construct(?int $migrateToChatId, ?int $retryAfter)
    {
        $this->migrateToChatId = $migrateToChatId;
        $this->retryAfter = $retryAfter;
    }

    /**
     * @return int|null
     */
    public function getMigrateToChatId(): ?int
    {
        return $this->migrateToChatId;
    }

    /**
     * @return int|null
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}