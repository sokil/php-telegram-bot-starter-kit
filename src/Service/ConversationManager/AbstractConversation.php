<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\ConversationManager;

use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;
use Symfony\Component\Workflow\Workflow;

/**
 * State of conversation with user
 */
abstract class AbstractConversation
{
    /**
     * @var TelegramBotClientInterface;
     */
    protected $telegramBotClient;

    /**
     * @var string
     */
    protected $state;

    /**
     * @param TelegramBotClientInterface $telegramBotClient
     */
    public function __construct(TelegramBotClientInterface $telegramBotClient)
    {
        $this->telegramBotClient = $telegramBotClient;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param Update $update
     *
     * @return string|null Next state
     */
    abstract public function apply(Update $update): ?string;
}