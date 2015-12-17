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
    
    /**
     *
     * @param String $commandName
     * @param array $mockReponsePaths
     * @param String $answers
     * @return CommandTester
     */
    protected function createTesterWithCommandNameAndMockDataAndAnswers($config) {
        $mockHandler = null;
        if ( isset($config["mockReponsePaths"]) ) {
            $mockHandler = $this->getHttpMockHandler($config["mockReponsePaths"]);
        }
        $application = new PipelineManagerApplication($this->env, $mockHandler);

        $command = $application->find($config["command"]);

        if ( isset($config["answers"]) ) {
            $helper = $command->getHelper('question');
            $helper->setInputStream($this->getInputStream($config["answers"]));
        }

        return new CommandTester($command);
    }

    protected function execute($config)
    {
        $commandTester = $this->createTesterWithCommandNameAndMockDataAndAnswers($config);
        if ( !isset($config["arguments"]) ) {
            $config["arguments"] = [];
        }
        
        $config["arguments"]["command"] = $config["command"];
        
        $commandTester->execute($config["arguments"]);
        
        return $commandTester->getDisplay();
    }

    protected function getHttpMockHandler( $mockReponsePaths )
    {
        $responses = [];
        foreach ($mockReponsePaths as $mockReponsePath) {
            $mockFile = __DIR__."/functional/mock/".$mockReponsePath;
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
