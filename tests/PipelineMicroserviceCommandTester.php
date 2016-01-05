<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use PipelinesMicroserviceCLI\Application\PipelineManagerApplication;
use Symfony\Component\Console\Tester\CommandTester;

class PipelineMicroserviceCommandTester
{

    protected $env;
    
    protected $commandName;
    
    protected $answers;
    
    protected $arguments;
    
    protected $mockReponsePaths;
    
    public function __construct($commandName, $env)
    {
        $this->commandName = $commandName;
        $this->env = $env;
    }
    
    public function withCommandArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }
    
    public function withAnswersToCommandQuestions($answerString)
    {
        $this->answers = $answerString;
        return $this;
    }
    
    public function withMocksForCommandHttpRequests(array $mockReponsePaths)
    {
        $this->mockReponsePaths = $mockReponsePaths;
        return $this;
    }
    
    public function execute()
    {
        $application = new PipelineManagerApplication($this->env, $this->createHttpMockHandler());
        
        $command = $application->find($this->commandName);
        
        $this->setCommandInputStream($command);
        
        $commandTester = new CommandTester($command);
        
        $commandTester->execute($this->getNormalizedCommandArguments());
        
        return $commandTester->getDisplay();
    }

    private function getNormalizedCommandArguments()
    {
        if ( !isset($this->arguments) ) {
            $this->arguments = [];
        }
        $this->arguments["command"] = $this->commandName;
        
        return $this->arguments;
    }
    
    private function setCommandInputStream($command)
    {
        if ( isset($this->answers) ) {
            $helper = $command->getHelper('question');
            $helper->setInputStream($this->getInputStream($this->answers));
        }
    }
    
    private function createHttpMockHandler()
    {
        $mockHandler = null;
        if ( isset($this->mockReponsePaths) ) {
            $responses = [];
            foreach ($this->mockReponsePaths as $mockReponsePath) {
                $mockFile = __DIR__."/functional/mock/".$mockReponsePath;
                if ( !file_exists($mockFile) ) {
                    throw new InvalidArgumentException('Unable to open mock file: ' . $mockFile);
                }
                $responses[] = \GuzzleHttp\Psr7\parse_response(file_get_contents($mockFile));
            }
            // Create a mock and queue responses.
            $mock           = new MockHandler($responses);
            $mockHandler        = HandlerStack::create($mock);
        }
        
        return $mockHandler;
    }

    private function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);
        
        return $stream;
    }

}
