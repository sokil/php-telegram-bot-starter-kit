<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Command;

use Psr\Http\Server\RequestHandlerInterface;
use Sokil\TelegramBot\Service\TelegramBotClient\TelegramBotClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use React\EventLoop\Factory as ReactEventLoopFactory;
use React\Socket\Server as ReactSocketServer;
use React\Http\Server as ReactHttpServer;
use React\Http\Response as ReactHttpResponse;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
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
     * @var TelegramBotClientInterface
     */
    private $telegram;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ServiceLocator
     */
    private $requestHandlerLocator;

    /**
     * @var int
     */
    private $httpServerPort;

    /**
     * @param TelegramBotClientInterface $telegram
     * @param RouterInterface $router
     * @param ServiceLocator $requestHandlerLocator
     * @param int $httpServerPort
     */
    public function __construct(
        TelegramBotClientInterface $telegram,
        RouterInterface $router,
        ServiceLocator $requestHandlerLocator,
        int $httpServerPort
    ) {
        parent::__construct(null);

        $this->telegram = $telegram;
        $this->router = $router;
        $this->requestHandlerLocator = $requestHandlerLocator;
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

        // create HTTP server
        $server = new ReactHttpServer(function (ServerRequestInterface $request) use ($output) {
            try {
                // apply request method to context
                $routerContext = $this->router->getContext();
                $routerContext->setMethod($request->getMethod());

                // get route parameters
                $parameters = $this->router->match($request->getUri()->getPath());

                // handle request
                if (empty($parameters['_controller'])) {
                    throw new \Exception(sprintf(sprintf('Request handler for route "%s" not specified', $parameters['_router'])));
                }

                if (!$this->requestHandlerLocator->has($parameters['_controller'])) {
                    throw new ResourceNotFoundException(sprintf('Request handler "%" not configured', $parameters['_controller']));
                }

                /** @var RequestHandlerInterface $requestHandler */
                $requestHandler = $this->requestHandlerLocator->get($parameters['_controller']);

                $response = $requestHandler->handle($request);
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

        // build event loop
        $loop = ReactEventLoopFactory::create();

        // create TCP server
        $socket = new ReactSocketServer($this->httpServerPort, $loop);
        $server->listen($socket);

        // run bot
        $output->writeln(sprintf(
            '<info>Bot listens web hooks at http://127.0.0.1:%d',
            $this->httpServerPort
        ));

        $loop->run();

        return 0;
    }
}
