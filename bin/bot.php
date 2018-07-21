<?php
declare(strict_types=1);

use Sokil\TelegramBot\Service\Console\Application;

// find and load autoloader
$autoloadPathList = array(
    // phar, global install, dev
    __DIR__ . '/../vendor/autoload.php',
    // install to vendor/bin
    __DIR__ . '/../../../autoload.php'
);

// locate project dir and run autoloader
$projectDir = null;
foreach ($autoloadPathList as $autoloadPath) {
    if (file_exists($autoloadPath)) {
        // define root of project
        $projectDir = dirname(dirname($autoloadPath));
        // run autoloader
        require_once $autoloadPath;
        break;
    }
}

// handle error about not installed project
if ($projectDir === null) {
    echo 'Please, install composer dependencies...';
}

// run app
$application = new Application($projectDir);
$application->run();
