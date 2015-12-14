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
    protected function createTesterWithCommandNameAndMockDataAndAnswers(
            $commandName,
            $answers,
            array $mockReponsePaths = null
    ) {
        $mockHandler = null;
        if ( $mockReponsePaths ) {
            $mockHandler = $this->getHttpMockHandler($mockReponsePaths);
        }
        $application = new PipelineManagerApplication($this->env, $mockHandler);

        $command = $application->find($commandName);

        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream($answers));

        return new CommandTester($command);
    }

    protected function execute($commandName, $answers, array $arguments = [], array $mockReponsePaths = null)
    {
        $commandTester = $this->createTesterWithCommandNameAndMockDataAndAnswers($commandName, $answers, $mockReponsePaths);
        if ( !isset($arguments["command"]) ) {
            $arguments["command"] = $commandName;
        }
        $commandTester->execute($arguments);
        return $commandTester->getDisplay();
    }

    protected function getHttpMockHandler( $mockReponsePaths ){
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
