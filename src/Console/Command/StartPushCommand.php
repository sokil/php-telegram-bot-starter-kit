<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Console\Command;

use Sokil\TelegramBot\Service\HttpServer\HttpServer;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Get updates from Telegram bot from webhooks
 *
 * @see https://core.telegram.org/bots/webhooks
 * @see https://core.telegram.org/bots/api#setwebhook
 */
class StartPushCommand extends Command
{
    public static $defaultName = 'start:push';

    /**
     * @var TelegramBotClientInterface
     */
    private $telegram;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var HttpServer
     */
    private $httpServer;

    /**
     * @param TelegramBotClientInterface $telegram
     * @param RouterInterface $router
     * @param HttpServer $httpServer
     */
    public function __construct(
        TelegramBotClientInterface $telegram,
        RouterInterface $router,
        HttpServer $httpServer
    ) {
        parent::__construct(null);

        $this->telegram = $telegram;
        $this->router = $router;
        $this->httpServer = $httpServer;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Run bot server and get updates from webhooks');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // build absolute URL to webhook
        $telegramWebHookUrl = $this->router->generate('telegramWebHook', [], UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            // check if webhook configured
            $webHookInfo = $this->telegram->getWebHookInfo();
            if ($webHookInfo->getUrl() === null) {
                // set web hook
                $this->telegram->setWebhook($telegramWebHookUrl);
            }

        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return 1;
        }

        $this->httpServer->run();

        return 0;
    }
}
