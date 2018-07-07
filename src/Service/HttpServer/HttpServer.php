<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Service\HttpServer;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Routing\RouterInterface;
use Psr\Http\Server\RequestHandlerInterface;
use React\EventLoop\Factory as ReactEventLoopFactory;
use React\Socket\Server as ReactSocketServer;
use React\Http\Server as ReactHttpServer;
use React\Http\Response as ReactHttpResponse;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class HttpServer
{
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
     * @param RouterInterface $router
     * @param ServiceLocator $requestHandlerLocator
     * @param int $httpServerPort
     */
    public function __construct(
        RouterInterface $router,
        ServiceLocator $requestHandlerLocator,
        int $httpServerPort
    ) {
        $this->router = $router;
        $this->requestHandlerLocator = $requestHandlerLocator;
        $this->httpServerPort = $httpServerPort;
    }

    /**
     * Run server
     */
    public function run(): void
    {
        // create HTTP server
        $server = new ReactHttpServer(function (ServerRequestInterface $request) {
            try {
                // apply request method to context
                $routerContext = $this->router->getContext();
                $routerContext->setMethod($request->getMethod());

                // get route parameters
                $parameters = $this->router->match($request->getUri()->getPath());

                // handle request
                if (empty($parameters['_controller'])) {
                    throw new \Exception(sprintf('Request handler for route "%s" not specified', $parameters['_router']));
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
        $loop->run();
    }
}