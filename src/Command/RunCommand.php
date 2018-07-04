<?php
declare(strict_types=1);

namespace Celebrator\Command;

use Longman\TelegramBot\Telegram;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use React\EventLoop\Factory as ReactEventLoopFactory;
use React\Socket\Server as ReactSocketServer;
use React\Http\Server as ReactHttpServer;
use React\Http\Response as ReactHttpResponse;
use Psr\Http\Message\ServerRequestInterface;

class RunCommand extends Command
{
    public static $defaultName = 'run';

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
    public function __construct(Telegram $telegram, int $httpServerPort)
    {
        parent::__construct(null);

        $this->telegram = $telegram;
        $this->httpServerPort = $httpServerPort;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run bot server');
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
        $loop = ReactEventLoopFactory::create();

        $server = new ReactHttpServer(function (ServerRequestInterface $request) {
            return new ReactHttpResponse(
                200,
                array('Content-Type' => 'text/plain'),
                $request->getUri()
            );
        });

        $socket = new ReactSocketServer($this->httpServerPort, $loop);
        $server->listen($socket);

        $output->writeln(sprintf(
            '<info>Bot listens web hooks at at http://127.0.0.1:%d',
            $this->httpServerPort
        ));

        $loop->run();

        return 0;
    }
}