#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use PipelinesMicroserviceCLI\Commands\ApprovePipelineRelease;
use PipelinesMicroserviceCLI\Application\PipelineManagerApplication;

$application = new PipelineManagerApplication('local');
$application->run();