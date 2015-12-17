<?php
namespace PipelinesMicroserviceCLI\Commands;

use PipelinesMicroservice\Entities\Job;

class CommandTest extends \PipelineMicroserviceCLITestCase
{
    public function testCanPublishAPipeline()
    {
        $commandOutput = $this->execute([
            "command" => 'pipeline:publish',
            "answers" => "1\n y \n",
            "mockReponsePaths" => ["HiddenPipelines.txt", "PublishedPipeline.txt"]
        ]);
        
        $this->stringShouldMatchPattern($commandOutput, '/Publishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Published["\']?/');
    }
    
    public function testCanHideAPipeline()
    {
        $commandOutput = $this->execute([
            "command" => 'pipeline:hide',
            "answers" => "1\n y \n",
            "mockReponsePaths" => ["PublicPipelines.txt", "PipelineToPublish.txt"]
        ]);
        
        $this->stringShouldMatchPattern($commandOutput, '/Unpublishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?/');
    }
    
    public function testCanApproveAPipelineRelease()
    {
        $commandOutput = $this->execute([
            "command" => 'pipeline:approve',
            "answers" => "1\n 0.2.0 \n y \n",
            "mockReponsePaths" => ["PublicPipelines.txt", "PipelineReleaseApproved.txt"]
        ]);
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to approve release.*/');
    }
    
    public function testCanDenyAPipelineRelease()
    {
        $commandOutput = $this->execute([
            "command" => 'pipeline:deny',
            "answers" => "1\n 0.1.0 \n y \n",
            "mockReponsePaths" => ["PublicPipelines.txt", "PipelineReleaseDenied.txt"]
        ]);
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to deny release.*\nDenying release.*/');
    }
    
    public function testCanRegisterAPipeline()
    {
        $authorId = 1;
        $sourceResource = "https://github.com/InSilicoDB/pipeline-kallisto.git";
        $commandOutput = $this->execute([
            "command" => 'pipeline:register',
            "arguments" => ["author" => $authorId, "source-resource" => $sourceResource],
            "mockReponsePaths" => ["PipelineToPublish.txt"]
        ]);
        
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?author[\"']?\s?:\s?[\"']?".$authorId."[\"']?,.*/");
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?,.*/');
    }
    
    public function testCanFindAJobById()
    {
        $jobId = 1;
        $commandOutput = $this->execute([
            "command" => 'job:id',
            "answers" => $jobId,
            "mockReponsePaths" => ["JobSingle.txt"]
        ]);
    
        $this->stringShouldMatchPattern($commandOutput, '/.*Please enter the id of the job.*/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$jobId."[\"']?/");
    }
    
    public function testCanFindAJobsByUserId()
    {
        $userId = 1;
        $commandOutput = $this->execute([
            "command" => 'job:user',
            "answers" => $userId,
            "mockReponsePaths" => ["JobsWithStatusRunning.txt"]
        ]);
    
        $this->stringShouldMatchPattern($commandOutput, '/.*Please enter the id of the user you want to filter on.*/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?user[\"']?\s?:\s?[\"']?".$userId."[\"']?/");
    }
    
    public function testCanFindJobsByStatus()
    {
        $commandOutput = $this->execute([
            "command" => 'job:status',
            "answers" => Job::STATUS_RUNNING,
            "mockReponsePaths" => ["JobsWithStatusRunning.txt"]
        ]);
    
        $this->stringShouldMatchPattern($commandOutput, '/.*Please choose the status you want to filter on.*/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?status[\"']?\s?:\s?[\"']?running[\"']?/");
    }
    
    public function testCanLaunchAJob()
    {
        $commandOutput = $this->execute([
            "command" => 'job:launch',
            "answers" => "1\n 0.1.0 \n \n /somepath/somefile.txt,/somepath/somefile.txt \n \n 30 \n \n 136 \n",
            "mockReponsePaths" => ["PublicPipelines.txt", "JobLaunch.txt"]
        ]);
    
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?status[\"']?\s?:\s?[\"']?scheduled[\"']?/");
    }
}
