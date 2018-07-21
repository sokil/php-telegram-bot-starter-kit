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

namespace Sokil\TelegramBot\Service\TelegramBotClient\Struct;

/**
 * @see https://core.telegram.org/bots/api#webhookinfo
 */
class WebHookInfo
{
    /**
     * @var string|null
     */
    private $url;

    /**
     * WebHookInfo constructor.
     * @param string|null $url
     */
    public function __construct(?string $url)
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