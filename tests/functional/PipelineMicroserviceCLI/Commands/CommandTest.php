<?php
namespace PipelinesMicroserviceCLI\Commands;

class CommandTest extends \PipelineMicroserviceCLITestCase
{
    public function testCanPublishAPipeline()
    {
        $commandOutput = $this->execute(
            'pipeline:publish',
            "1\n y \n",
            [],
            ["HiddenPipelines.txt", "PublishedPipeline.txt"]
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/Publishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Published["\']?/');
    }
    
    public function testCanHideAPipeline()
    {
        $commandOutput = $this->execute(
            'pipeline:hide',
            "1\n y \n",
            [],
            ["PublicPipelines.txt", "PipelineToPublish.txt"]
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/Unpublishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?/');
    }
    
    public function testCanApproveAPipelineRelease()
    {
        $commandOutput = $this->execute(
            'pipeline:approve',
            "1\n 0.2.0 \n y \n",
            [],
            ["PublicPipelines.txt", "PipelineReleaseApproved.txt"]
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to approve release.*/');
    }
    
    public function testCanDenyAPipelineRelease()
    {
        $commandOutput = $this->execute(
            'pipeline:deny',
            "1\n 0.1.0 \n y \n",
            [],
            ["PublicPipelines.txt", "PipelineReleaseDenied.txt"]
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to deny release.*\nDenying release.*/');
    }
    
    public function testCanRegisterAPipeline()
    {
        $commandOutput = $this->execute(
            'pipeline:register',
            null,
            ["author" => 1, "source-resource" => "https://github.com/InSilicoDB/pipeline-kallisto.git"],
            ["PipelineToPublish.txt"]
        );
        
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?.*["\']?id["\']?:1,/');
    }
}
