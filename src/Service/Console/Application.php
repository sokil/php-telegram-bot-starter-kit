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

namespace Sokil\TelegramBot\Service\Console;

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
     * Directory where library's composer.json placed
     *
     * If this library installed as project, then kerned and project dirs are same.
     *
     * @var string
     */
    private $kernelDir;

    /**
     * Directory where user code and project's composer.json placed.
     *
     * If this library installed as project, then kerned and project dirs are same.
     *
     * @var string
     */
    private $projectDir;

    /**
     * If library installed as project and project dir also is kernel dir
     *
     * @var bool
     */
    private $isProjectMode;

    /**
     * @param string $kernelDir
     * @param string $projectDir
     */
    public function __construct(string $kernelDir, string $projectDir)
    {
        $this->kernelDir = $kernelDir;
        $this->projectDir = $projectDir;
        $this->isProjectMode = $projectDir === $kernelDir;
    }

    /**
     * @throws \Exception
     */
    public function run(): void
    {
        // load env configuration
        $envConfigPath = $this->projectDir . '/.env';
        if (!is_readable($envConfigPath)) {
            echo 'Please, create .env file in project root dir.' . PHP_EOL;
            return;
        }

        $dotEnv = new Dotenv();
        $dotEnv->load($envConfigPath);

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
        $cacheDir =  $this->projectDir . '/runtime/cache/' . $environment;

        // init dependency injection container
        $containerConfigCache = new ConfigCache(
            $cacheDir .'/container.php',
            $isDebug
        );

        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();

            // add parameters
            $containerBuilder->getParameterBag()->add([
                // kernel vars
                'kernel.environment' => $environment,
                'kernel.debug' => $isDebug,
                'kernel.dir' => $this->projectDir,
                'kernel.config_dir' => $this->kernelDir . '/src/Config',
                'kernel.resource_dir' => $this->kernelDir . '/src/Resource',
                // project vars
                'project.dir' => $this->projectDir,
                'project.config_dir' => $this->projectDir . '/src/Config',
                'project.resource_dir' => $this->projectDir . '/src/Resource',
                'project.cache_dir' => $cacheDir,
                'project.logs_dir' => $this->projectDir . '/runtime/logs/' . $environment,
            ]);

            // add compiler passes
            $containerBuilder->addCompilerPass(new WorkflowBuildCompilerPass());
            $containerBuilder->addCompilerPass(new ConversationLocatorPass());

            // allow autoconfiguration and lazy add of console commands (used by service 'console.command_loader')
            $containerBuilder->addCompilerPass(new AddConsoleCommandPass(), PassConfig::TYPE_BEFORE_REMOVING, 0);
            $containerBuilder
                ->registerForAutoconfiguration(Command::class)
                ->addTag('console.command');

            // load services from config
            $serviceConfigLoader = new DependencyInjectionYamlFileLoader(
                $containerBuilder,
                new FileLocator([
                    $this->projectDir . '/src/Config/Service',
                    $this->kernelDir . '/src/Config/Service',
                ])
            );

            $serviceConfigLoader->load('application.yml');
            $serviceConfigLoader->load('consoleCommands.yml');
            $serviceConfigLoader->load('conversation.yml');
            $serviceConfigLoader->load('router.yml');
            $serviceConfigLoader->load('telegramApi.yml');
            $serviceConfigLoader->load('workflow.yml');
            $serviceConfigLoader->load('projectServices.yml'); // load services from project dir in library mode

            // compile container
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