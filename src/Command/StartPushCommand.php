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
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
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
     * @var Telegram
     */
    private $telegram;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var int
     */
    private $httpServerPort;

    /**
     * @param Telegram $telegram
     */
    public function __construct(Telegram $telegram, RouterInterface $router, int $httpServerPort)
    {
        parent::__construct(null);

        $this->telegram = $telegram;
        $this->router = $router;
        $this->httpServerPort = $httpServerPort;
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
        // set web hook
        try {
            // build absolute URL to webhook
            $telegramWebHookUrl = $this->router->generate('telegramWebHook', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $result = $this->telegram->setWebhook($telegramWebHookUrl);
            if ($result->isOk()) {
                $output->writeln(sprintf('<info>%s</info>', $result->getDescription()));
            } else {
                throw new \RuntimeException('Unknown error');
            }
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return 1;

        }

        // start server
        $loop = ReactEventLoopFactory::create();

        $server = new ReactHttpServer(function (ServerRequestInterface $request) use ($output) {
            try {
                $routerContext = $this->router->getContext();
                $routerContext->setMethod($request->getMethod());

                $parameters = $this->router->match($request->getUri()->getPath());

                call_user_func($parameters['_controller']);
            } catch (MethodNotAllowedException $e) {
                $response = new ReactHttpResponse(
                    400,
                    array('Content-Type' => 'text/plain'),
                    'Bad method'
                );
            } catch (ResourceNotFoundException $e) {
                $response = new ReactHttpResponse(
                    404,
                    array('Content-Type' => 'text/plain'),
                    'Not found'
                );
            } catch (\Throwable $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                $output->writeln(sprintf('<error>%s</error>', $e->getTraceAsString()));

                $response = new ReactHttpResponse(
                    500,
                    array('Content-Type' => 'text/plain'),
                    'Route not found'
                );
            }

            return $response;
        });

        $socket = new ReactSocketServer($this->httpServerPort, $loop);
        $server->listen($socket);

        $output->writeln(sprintf(
            '<info>Bot listens web hooks at http://127.0.0.1:%d',
            $this->httpServerPort
        ));

        $loop->run();

        return 0;
    }
}