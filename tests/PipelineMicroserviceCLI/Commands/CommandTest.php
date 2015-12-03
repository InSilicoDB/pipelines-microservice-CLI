<?php

namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends CommandTestCase {
    
    public function testPublishPipeline(){
        $mockHandler = $this->getHttpMockHandler(["PublishedPipeline.txt"]);
        $application = new Application();
        $application->add(new PublishPipeline(null,$mockHandler));
        
        $command       = $application->find('pipeline:publish');
        $commandTester = new CommandTester($command);
        $commandTester->execute( [
                'command'  => $command->getName(),
                "id"       => 1,
                "base_url" => "todo"
        ] );
        $this->assertRegExp('/.*["\']?published["\']?\s?:\s?["\']?Published["\']?.*["\']?id["\']?:1,/', $commandTester->getDisplay());
    }
    
    public function testHidePipeline(){
        $mockHandler = $this->getHttpMockHandler(["PipelineToPublish.txt"]);
        $application = new Application();
        $application->add(new HidePipeline(null,$mockHandler));
    
        $command       = $application->find('pipeline:hide');
        $commandTester = new CommandTester($command);
        $commandTester->execute( [
                'command'   => $command->getName(),
                "id"        => 1,
                "base_url"  => "todo"
        ] );
        $this->assertRegExp('/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?.*["\']?id["\']?:1,/', $commandTester->getDisplay());
    }
    
    public function testRegisterPipeline(){
        $mockHandler = $this->getHttpMockHandler(["PipelineToPublish.txt"]);
        $application = new Application();
        $application->add(new RegisterPipeline(null,$mockHandler));
    
        $command       = $application->find('pipeline:register');
        $commandTester = new CommandTester($command);
        $commandTester->execute( [
                'command'           => $command->getName(),
                "base_url"          => "todo",
                "author"            => 1,
                "source-resource"   => "https://todo"
        ] );
        $this->assertRegExp('/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?.*["\']?id["\']?:1,/', $commandTester->getDisplay());
    }
}
