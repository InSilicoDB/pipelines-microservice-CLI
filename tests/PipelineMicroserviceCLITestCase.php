<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use PipelinesMicroserviceCLI\Application\PipelineManagerApplication;
use Symfony\Component\Console\Tester\CommandTester;

abstract class PipelineMicroserviceCLITestCase extends \PHPUnit_Framework_TestCase
{
    protected $env = "test";
    
    public function stringShouldMatchPattern($string,$pattern)
    {
        return $this->assertRegExp($pattern, $string);
    }
    
    public function createCommandTester($commandName)
    {
        return new PipelineMicroserviceCommandTester($commandName, $this->env);
    }
}
