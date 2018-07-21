<?php
declare(strict_types=1);

use Sokil\TelegramBot\Console\Application;

// find and load autoloader
$autoloadPathList = array(
    // phar, global install, dev
    __DIR__ . '/../vendor/autoload.php',
    // install to vendor/bin
    __DIR__ . '/../../../autoload.php'
);

// locate project dir and run autoloader
foreach ($autoloadPathList as $autoloadPath) {
    if (file_exists($autoloadPath)) {
        // define root of project
        $projectDir = dirname(dirname($autoloadPath));
        // run autoloader
        require_once $autoloadPath;
        break;
    }
}

// run app
$application = new Application($projectDir);
$application->run();

