<?php
namespace PipelinesMicroserviceCLI\Commands;

class CommandTest extends \PipelineMicroserviceCLITestCase
{
    public function testPublishPipeline()
    {
        $commandOutput = $this->execute(
            'pipeline:publish',
            "0\n y \n",
            [],
            ["HiddenPipelines.txt", "PublishedPipeline.txt"]
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/Publishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Published["\']?/');
    }
    
    public function testHidePipeline()
    {
        $commandOutput = $this->execute(
            'pipeline:hide',
            "0\n y \n",
            [],
            ["PublicPipelines.txt", "PipelineToPublish.txt"]
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/Unpublishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?/');
    }
    
    public function testApprovePipelineRelease()
    {
        $commandOutput = $this->execute(
            'pipeline:approve',
            "0\n 0 \n y \n",
            [],
            ["PublicPipelines.txt", "PipelineReleaseApproved.txt"]
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to approve release.*/');
    }
    
    public function testDenyPipelineRelease()
    {
        $commandOutput = $this->execute(
            'pipeline:deny',
            "0\n 2 \n y \n",
            [],
            ["PublicPipelines.txt", "PipelineReleaseDenied.txt"]
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to deny release.*\nDenying release.*/');
    }
}
