#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use PipelinesMicroserviceCLI\Commands\GreetCommand;

$application = new Application();
$application->add( new GreetCommand() );
$application->run();