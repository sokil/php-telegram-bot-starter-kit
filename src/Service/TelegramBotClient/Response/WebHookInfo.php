<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\TelegramBotClient\Response;

/**
 * @see https://core.telegram.org/bots/api#webhookinfo
 */
class WebHookInfo
{
    /**
     * @var string
     */
    private $url;

    /**
     * WebHookInfo constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string|null when hook not set return null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}