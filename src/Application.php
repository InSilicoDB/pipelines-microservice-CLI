#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/../vendor/autoload.php';

use PipelinesMicroserviceCLI\Application\PipelineManagerApplication;

$application = new PipelineManagerApplication('local');
$application->run();