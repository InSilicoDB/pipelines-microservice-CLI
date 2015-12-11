<?php
namespace PipelinesMicroserviceCLI\Commands;

class CommandTest extends CommandTestCase
{
    public function testPublishPipeline()
    {
        $commandOutput = $this->execute(
            'pipeline:publish',
            ["HiddenPipelines.txt", "PublishedPipeline.txt"],
            "0\n y \n"
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/Publishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Published["\']?/');
    }
    
    public function testHidePipeline()
    {
        $commandOutput = $this->execute(
            'pipeline:hide',
            ["PublicPipelines.txt", "PipelineToPublish.txt"],
            "0\n y \n"
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/Unpublishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?/');
    }
    
    public function testApprovePipelineRelease()
    {
        $commandOutput = $this->execute(
            'pipeline:approve',
            ["PublicPipelines.txt", "PipelineReleaseApproved.txt"],
            "0\n 0 \n y \n"
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to approve release.*/');
    }
    
    public function testDenyPipelineRelease()
    {
        $commandOutput = $this->execute(
            'pipeline:deny',
            ["PublicPipelines.txt", "PipelineReleaseDenied.txt"],
            "0\n 2 \n y \n"
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to deny release.*\nDenying release.*/');
    }
}
