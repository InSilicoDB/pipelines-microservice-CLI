<?php

namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends CommandTestCase {
    
    public function testPublishPipelineMissingBaseUrl(){
        $mockHandler = $this->getHttpMockHandler(["HiddenPipelines.txt", "PublishedPipeline.txt"]);
        $application = new Application();
        $application->add(new PublishPipeline(null,$mockHandler));
        
        $command       = $application->find('pipeline:publish');
        
        // Equals to a user inputting "2" and hitting ENTER
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream('2\\n'));
        
        $commandTester = new CommandTester($command);
        $commandTester->execute( [
                'command' => $command->getName(),
                "base_url" => "todo"
        ] );
        $this->assertRegExp('/.*["\']?published["\']?\s?:\s?["\']?Published["\']?.*["\']?id["\']?:1,/', $commandTester->getDisplay());
    }
    
    public function testHidePipelineMissingBaseUrl(){
        $mockHandler = $this->getHttpMockHandler(["PublicPipelines.txt", "PipelineToPublish.txt"]);
        $application = new Application();
        $application->add(new HidePipeline(null,$mockHandler));
    
        $command       = $application->find('pipeline:hide');
        
        // Equals to a user inputting "2" and hitting ENTER
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream('2\\n'));
        
        $commandTester = new CommandTester($command);
        $commandTester->execute( [
                'command' => $command->getName(),
                "base_url" => "todo"
        ] );
        $this->assertRegExp('/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?.*["\']?id["\']?:1,/', $commandTester->getDisplay());
    }
}
