<?php

namespace PipelinesMicroserviceCLI\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use PipelinesMicroserviceCLI\Application\PipelineManagerApplication;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    
    /**
     * 
     * @param String $commandName
     * @param array $mockReponsePaths
     * @param String $answers
     * @return CommandTester
     */
    protected function createTesterWithCommandNameAndMockDataAndAnswers(
        $commandName,
        array $mockReponsePaths,
        $answers
    ) {
        $mockHandler = $this->getHttpMockHandler($mockReponsePaths);
        $application = new PipelineManagerApplication('test', $mockHandler);
        
        $command = $application->find($commandName);
        
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream($answers));
        
        return new CommandTester($command);
    }
    
    protected function execute($commandName, array $mockReponsePaths, $answers, array $arguments = [] )
    {
        $commandTester = $this->createTesterWithCommandNameAndMockDataAndAnswers($commandName, $mockReponsePaths, $answers);
        if ( !isset($arguments["command"]) ) {
            $arguments["command"] = $commandName;
        }
        $commandTester->execute($arguments);
        return $commandTester->getDisplay();
    }
    
    protected function getHttpMockHandler( $mockReponsePaths ){
        $responses = [];
        foreach ($mockReponsePaths as $mockReponsePath) {
            $mockFile = __DIR__."/../../mock/".$mockReponsePath;
            if ( !file_exists($mockFile) ) {
                throw new InvalidArgumentException('Unable to open mock file: ' . $mockFile);
            }
            $responses[] = \GuzzleHttp\Psr7\parse_response(file_get_contents($mockFile));
        }
        // Create a mock and queue responses.
        $mock           = new MockHandler($responses);
        $handler        = HandlerStack::create($mock);
        return $handler;
    }
    
    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);
    
        return $stream;
    }
    
    public function stringShouldMatchPattern($string,$pattern)
    {
        return $this->assertRegExp($pattern, $string);
    }
}
