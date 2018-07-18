<?php
declare(strict_types=1);

namespace Sokil\TelegramBot\Console;

use Sokil\TelegramBot\Service\ConversationManager\DependencyInjection\CompilerPass\ConversationLocatorPass;
use Sokil\TelegramBot\Service\Logger\ConsoleLogger;
use Sokil\TelegramBot\Service\Workflow\DependencyInjection\CompilerPass\WorkflowBuildCompilerPass;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader as DependencyInjectionYamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Application
{
    public const ENVIRONMENT_DEV = 'dev';
    public const ENVIRONMENT_PROD = 'prod';

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @throws \Exception
     */
    public function run(): void
    {
        // load env configuration
        $configPath = $this->projectDir . '/.env';
        if (!is_readable($configPath)) {
            echo 'Please, create .env file in project root dir.' . PHP_EOL;
            return;
        }

        $dotEnv = new Dotenv();
        $dotEnv->load($configPath);

        // configure environment
        $inputArguments = new ArgvInput();
        $environment = $inputArguments->getParameterOption(
            ['--env', '-e'],
            $_SERVER['APP_ENV'] ?? self::ENVIRONMENT_DEV,
            true
        );

        // configure debug mode-
        $isDebug = (bool)($_SERVER['APP_ENV'] ?? ($environment !== self::ENVIRONMENT_PROD));

        // directories
        $configDir = $this->projectDir . '/src/Config';
        $resourceDir = $this->projectDir . '/src/Resource';
        $cacheDir =  $this->projectDir . '/runtime/cache/' . $environment;
        $logsDir = $this->projectDir . '/runtime/logs/' . $environment;

        // init dependency injection container
        $containerConfigCache = new ConfigCache(
            $cacheDir .'/container.php',
            $isDebug
        );

        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();

            // add parameters
            $containerBuilder->getParameterBag()->add([
                'kernel.project_dir' => $this->projectDir,
                'kernel.environment' => $environment,
                'kernel.debug' => $isDebug,
                'kernel.config_dir' => $configDir,
                'kernel.resource_dir' => $resourceDir,
                'kernel.cache_dir' => $cacheDir,
                'kernel.logs_dir' => $logsDir,
            ]);

            // add compiler passes
            $containerBuilder->addCompilerPass(
                new AddConsoleCommandPass(),
                PassConfig::TYPE_BEFORE_REMOVING,
                0
            );

            $containerBuilder->addCompilerPass(new WorkflowBuildCompilerPass());
            $containerBuilder->addCompilerPass(new ConversationLocatorPass());

            // allow autoconfiguration
            $containerBuilder
                ->registerForAutoconfiguration(Command::class)
                ->addTag('console.command');

            // load services from config
            $serviceConfigLoader = new DependencyInjectionYamlFileLoader(
                $containerBuilder,
                new FileLocator($this->projectDir . '/src/Config/Service')
            );

            $serviceConfigLoader->load('commands.yml');
            $serviceConfigLoader->load('common.yml');
            $serviceConfigLoader->load('conversation.yml');
            $serviceConfigLoader->load('router.yml');
            $serviceConfigLoader->load('telegramApi.yml');
            $serviceConfigLoader->load('workflow.yml');

            $containerBuilder->compile();

            // dump compiled container to cache
            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(['class' => 'ProjectServiceContainer']),
                $containerBuilder->getResources()
            );
        }

        // run container
        require_once $containerConfigCache->getPath();
        $container = new \ProjectServiceContainer();

        // configure events
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $container->get('event_dispatcher');

        // init application
        $application = new ConsoleApplication();
        $application->setDispatcher($eventDispatcher);
        $application->setCommandLoader($container->get('console.command_loader'));

        // configure console logger with output
        $eventDispatcher->addListener(
            ConsoleEvents::COMMAND,
            function(ConsoleCommandEvent $event) use ($container) {
                /** @var ConsoleLogger $logger */
                $logger = $container->get('logger');
                $logger->setOutput($event->getOutput());
            }
        );

        // run application
        $application->run();
    }
}