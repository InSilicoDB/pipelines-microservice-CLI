#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use PipelinesMicroserviceCLI\Commands\GreetCommand;
use PipelinesMicroserviceCLI\Commands\PublishPipeline;
use PipelinesMicroserviceCLI\Commands\HidePipeline;
use PipelinesMicroserviceCLI\Commands\RegisterPipeline;

$application = new Application();
$application->add( new PublishPipeline() );
$application->add( new HidePipeline() );
$application->add( new RegisterPipeline() );
$application->run();