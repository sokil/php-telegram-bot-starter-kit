<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\ConversationManager;

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
     * @var Workflow
     */
    protected $workflow;

    /**
     * @param TelegramBotClientInterface $telegramBotClient
     */
    public function __construct(TelegramBotClientInterface $telegramBotClient)
    {
        $this->telegramBotClient = $telegramBotClient;
    }

    /**
     * @param Update $update
     */
    abstract public function apply(Update $update): void;
}