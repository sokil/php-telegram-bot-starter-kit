<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Config\ConfigCache;

require_once __DIR__ . '/../vendor/autoload.php';

// load configuration
$dotEnv = new Dotenv();
$dotEnv->load(__DIR__ . '/../.env');

// init dependency injection container
$containerConfigCache = new ConfigCache(
    __DIR__ .'/../runtime/container.php',
    getenv('APP_ENVIRONMENT') === 'dev'
);

if (!$containerConfigCache->isFresh()) {
    $containerBuilder = new ContainerBuilder();

    $containerBuilder->addCompilerPass(
        new AddConsoleCommandPass(),
        PassConfig::TYPE_BEFORE_REMOVING,
        0
    );

    $containerBuilder
        ->registerForAutoconfiguration(Command::class)
        ->addTag('console.command');

    $serviceConfigLoader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../src/Config'));
    $serviceConfigLoader->load('services.yml');

    $containerBuilder->compile();

    $dumper = new PhpDumper($containerBuilder);
    $containerConfigCache->write(
        $dumper->dump(array('class' => 'ProjectServiceContainer')),
        $containerBuilder->getResources()
    );
}

require_once $containerConfigCache->getPath();
$container = new ProjectServiceContainer();

// configure application
$application = new Application();
$application->setCommandLoader($container->get('console.command_loader'));

// run application
$application->run();