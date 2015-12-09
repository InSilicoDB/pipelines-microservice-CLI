<?php

namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends CommandTestCase {
    
    public function testPublishPipeline(){
        $mockHandler = $this->getHttpMockHandler(["HiddenPipelines.txt", "PublishedPipeline.txt"]);
        $application = new Application();
        $application->add(new PublishPipeline(null,$mockHandler));
        
        $command       = $application->find('pipeline:publish');
        
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream("0\n y \n"));
        
        $commandTester = new CommandTester($command);
        $commandTester->execute( [
                'command' => $command->getName(),
                "base_url" => "todo"
        ] );
        
//         echo $commandTester->getDisplay();
        
        $this->assertRegExp('/Publishing pipeline:/', $commandTester->getDisplay());
        $this->assertRegExp('/.*["\']?published["\']?\s?:\s?["\']?Published["\']?/', $commandTester->getDisplay());
    }
    
    public function testHidePipeline(){
        $mockHandler = $this->getHttpMockHandler(["PublicPipelines.txt", "PipelineToPublish.txt"]);
        $application = new Application();
        $application->add(new HidePipeline(null,$mockHandler));
    
        $command       = $application->find('pipeline:hide');
        
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream("0\n y \n"));
        
        $commandTester = new CommandTester($command);
        $commandTester->execute( [
                'command' => $command->getName(),
                "base_url" => "todo"
        ] );

//         echo $commandTester->getDisplay();
        $this->assertRegExp('/Unpublishing pipeline:/', $commandTester->getDisplay());
        $this->assertRegExp('/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?/', $commandTester->getDisplay());
    }
    
    public function testApprovePipelineRelease(){
        $mockHandler = $this->getHttpMockHandler(["PublicPipelines.txt", "PipelineReleaseApproved.txt"]);
        $application = new Application();
        $application->add(new ApprovePipelineRelease(null,$mockHandler));
    
        $command       = $application->find('pipeline:approve');
    
        // Equals to a user inputting "2" and hitting ENTER
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream("0\n 0 \n y \n"));
    
        $commandTester = new CommandTester($command);
        $commandTester->execute( [
                'command' => $command->getName(),
                "base_url" => "todo"
        ] );
//         echo $commandTester->getDisplay();
        
        $this->assertRegExp('/.*Are you sure to approve release.*/', $commandTester->getDisplay());
    }
    
    public function testDenyPipelineRelease(){
        $mockHandler = $this->getHttpMockHandler(["PublicPipelines.txt", "PipelineReleaseDenied.txt"]);
        $application = new Application();
        $application->add(new DenyPipelineRelease(null,$mockHandler));
    
        $command       = $application->find('pipeline:deny');
    
        // Equals to a user inputting "2" and hitting ENTER
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream("0\n 2 \n y \n"));
    
        $commandTester = new CommandTester($command);
        $commandTester->execute( [
                'command' => $command->getName(),
                "base_url" => "todo"
        ] );
        
//         echo $commandTester->getDisplay();
        
        $this->assertRegExp('/.*Are you sure to deny release.*\nDenying release.*/', "".$commandTester->getDisplay(false));
    }
}
