<?php

use Symfony\Component\Console\Application;
use Celebrator\Command\RunCommand;

require_once __DIR__ . '/../vendor/autoload.php';

$application = new Application();
$application->add(new RunCommand());

$application->run();