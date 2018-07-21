<?php
declare(strict_types=1);

use Sokil\TelegramBot\Service\Console\Application;

/**
 * @return null|string
 */
function locateAutoloadFilePath(): ?string
{
    // find and load autoloader
    $autoloadPathList = array(
        // phar, global install, dev
        __DIR__ . '/../vendor/autoload.php',
        // install to vendor/bin
        __DIR__ . '/../../../autoload.php'
    );

    foreach ($autoloadPathList as $autoloadPath) {
        if (file_exists($autoloadPath)) {
            return realpath($autoloadPath);
        }
    }
}

// run autoloader
$autoloadPath = locateAutoloadFilePath();
if ($autoloadPath === null) {
    echo 'Please, install composer dependencies...';
    exit(1);
}

require_once $autoloadPath;

// define directories
$kernelDir = realpath(__DIR__ . '/../');
$projectDir = dirname(dirname($autoloadPath));


// run app
$application = new Application($kernelDir, $projectDir);
$application->run();
