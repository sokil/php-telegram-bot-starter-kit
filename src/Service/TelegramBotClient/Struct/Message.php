<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Struct;

/**
 * @see https://core.telegram.org/bots/api#message
 */
class Message
{
    /**
     * Conversation the message belongs to
     *
     * @var Chat
     */
    private $chat;

    /**
     * Sender, empty for messages sent to channels
     *
     * @var User|null
     */
    private $from;
    /**
     * Optional. For text messages, the actual UTF-8 text of the message, 0-4096 characters.
     *
     * @var string|null
     */
    private $text;

    /**
     * @param Chat $chat
     * @param User|null $from
     * @param string|null $text
     */
    public function __construct(Chat $chat, ?User $from, ?string $text)
    {
        $this->chat = $chat;
        $this->from = $from;
        $this->text = $text;
    }

    /**
     * @return Chat
     */
    public function getChat(): Chat
    {
        return $this->chat;
    }

    /**
     * @return null|User
     */
    public function getFrom(): ?User
    {
        return $this->from;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->text;
    }

}