<?php
declare(strict_types=1);

/**
 * This file is part of the PHP Telegram Starter Kit.
 *
 * (c) Dmytro Sokil <dmytro.sokil@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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