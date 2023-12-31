#!/usr/bin/php
<?php
declare(strict_types=1);

use Gared\WebLogStats\Console\GenerateStatsCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$application = new Application();
$application->add(new GenerateStatsCommand());
$application->run();