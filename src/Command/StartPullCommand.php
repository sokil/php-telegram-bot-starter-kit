<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Command;

use Longman\TelegramBot\Telegram;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use React\EventLoop\Factory as ReactEventLoopFactory;
use React\Socket\Server as ReactSocketServer;
use React\Http\Server as ReactHttpServer;
use React\Http\Response as ReactHttpResponse;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Get updates from Telegram by getUpdates method
 *
 * @see https://core.telegram.org/bots/api#getupdates
 */
class StartPullCommand extends Command
{
    public static $defaultName = 'start:pull';

    /**
     * @var Telegram
     */
    private $telegram;

    /**
     * @var int
     */
    private $httpServerPort;

    /**
     * @param Telegram $telegram
     */
    public function __construct(Telegram $telegram)
    {
        parent::__construct(null);

        $this->telegram = $telegram;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Run bot server and get updates by periodic call of Telegram API method "getUpdates"');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}