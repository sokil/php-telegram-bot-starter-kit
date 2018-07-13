<?php
declare(strict_types=1);

namespace Sokikl\TelegramBot\Conversation;

use Sokil\TelegramBot\ConversationManager\AbstractConversation;
use Sokil\TelegramBot\Service\TelegramBotClient\Struct\Update;

class WhoAmIConversation extends AbstractConversation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $userName;

    /**
     * @param Update $update
     */
    public function apply(Update $update): void
    {
        $this->id = $update->getMessage()->getFrom()->getId();
        $this->firstName = $update->getMessage()->getFrom()->getFirstName();
        $this->lastName = $update->getMessage()->getFrom()->getLastName();
        $this->userName = $update->getMessage()->getFrom()->getUserName();
    }
}