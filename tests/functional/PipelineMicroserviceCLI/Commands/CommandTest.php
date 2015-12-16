<?php
namespace PipelinesMicroserviceCLI\Commands;

use PipelinesMicroservice\Entities\Job;

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
    
    public function testCanFindAJobById()
    {
        $jobId = 1;
        $commandOutput = $this->execute(
            'job:id',
            $jobId,
            [],
            ["JobSingle.txt"]
        );
    
        $this->stringShouldMatchPattern($commandOutput, '/.*Please enter the id of the job.*/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$jobId."[\"']?/");
    }
    
    public function testCanFindAJobsByUserId()
    {
        $userId = 1;
        $commandOutput = $this->execute(
            'job:user',
            $userId,
            [],
            ["JobWithStatusRunning.txt"]
        );
    
        $this->stringShouldMatchPattern($commandOutput, '/.*Please enter the id of the user you want to filter on.*/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?user[\"']?\s?:\s?[\"']?".$userId."[\"']?/");
    }
    
    public function testCanFindJobsByStatus()
    {
        $commandOutput = $this->execute(
            'job:status',
            Job::STATUS_RUNNING,
            [],
            ["JobWithStatusRunning.txt"]
        );
    
        $this->stringShouldMatchPattern($commandOutput, '/.*Please choose the status you want to filter on.*/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?status[\"']?\s?:\s?[\"']?running[\"']?/");
    }
}
