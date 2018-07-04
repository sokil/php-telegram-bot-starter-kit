<?php
declare(strict_types=1);

use Sokil\TelegramBot\Application;

// define root of project
$projectDir = realpath(__DIR__ . '/../');

// add composer autoloader
require_once $projectDir . '/vendor/autoload.php';

// run app
$application = new Application($projectDir);
$application->run();

