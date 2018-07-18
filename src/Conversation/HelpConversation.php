<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Conversation;

use Sokil\TelegramBot\Service\ConversationManager\AbstractConversation;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;
use Sokil\TelegramBot\Service\TelegramBotClient\Type\ParseMode;
use Symfony\Component\Config\FileLocator;

/**
 * Show help menu
 */
class HelpConversation extends AbstractConversation
{
    /**
     * @var FileLocator
     */
    private $resourceFileLocator;

    /**
     * @param TelegramBotClientInterface $telegramBotClient
     * @param FileLocator $resourceFileLocator
     */
    public function __construct(
        TelegramBotClientInterface $telegramBotClient,
        FileLocator $resourceFileLocator
    ) {
        parent::__construct($telegramBotClient);

        $this->resourceFileLocator = $resourceFileLocator;
    }

    /**
     * @param Update $update
     *
     * @return string|null
     */
    public function apply(Update $update): ?string
    {
        $chatId = $update->getMessage()->getChat()->getId();

        $helpMessagePath = $this->resourceFileLocator->locate('Help.md');
        $message = file_get_contents($helpMessagePath);

        $this->telegramBotClient->sendMessage(
            (string)$chatId,
            $message,
            ParseMode::MARKDOWN()
        );

        return null;
    }
}