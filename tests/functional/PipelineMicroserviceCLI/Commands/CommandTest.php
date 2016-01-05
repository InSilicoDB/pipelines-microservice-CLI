<?php
namespace PipelinesMicroserviceCLI\Commands;

use PipelinesMicroservice\Entities\Job;

class CommandTest extends \PipelineMicroserviceCLITestCase
{
    public function testCanPublishAPipeline()
    {
        $commandOutput = $this->createCommandTester('pipeline:publish')
            ->withAnswersToCommandQuestions("1\n y \n")
            ->withMocksForCommandHttpRequests(["HiddenPipelines.txt", "PublishedPipeline.txt"])
            ->execute();
        
        $this->stringShouldMatchPattern($commandOutput, '/Publishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Published["\']?/');
    }
    
    public function testCanHideAPipeline()
    {
        $commandOutput = $this->createCommandTester('pipeline:hide')
            ->withAnswersToCommandQuestions("1\n y \n")
            ->withMocksForCommandHttpRequests(["PublicPipelines.txt", "PipelineToPublish.txt"])
            ->execute();
        
        $this->stringShouldMatchPattern($commandOutput, '/Unpublishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?/');
    }
    
    public function testCanApproveAPipelineRelease()
    {
        $commandOutput = $this->createCommandTester('pipeline:approve')
            ->withAnswersToCommandQuestions("1\n 0.2.0 \n y \n")
            ->withMocksForCommandHttpRequests(["PublicPipelines.txt", "PipelineReleaseApproved.txt"])
            ->execute();
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to approve release.*/');
    }
    
    public function testCanDenyAPipelineRelease()
    {
        $commandOutput = $this->createCommandTester('pipeline:deny')
            ->withAnswersToCommandQuestions("1\n 0.1.0 \n y \n")
            ->withMocksForCommandHttpRequests(["PublicPipelines.txt", "PipelineReleaseDenied.txt"])
            ->execute();
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to deny release.*\nDenying release.*/');
    }
    
    public function testCanRegisterAPipeline()
    {
        $authorId = 1;
        $sourceResource = "https://github.com/InSilicoDB/pipeline-kallisto.git";
        $commandOutput = $this->createCommandTester('pipeline:register')
            ->withCommandArguments(["author" => $authorId, "source-resource" => $sourceResource])
            ->withMocksForCommandHttpRequests(["PipelineToPublish.txt"])
            ->execute();
        
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?author[\"']?\s?:\s?[\"']?".$authorId."[\"']?,.*/");
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?,.*/');
    }
    
    public function testCanFindAJobById()
    {
        $jobId = 1;
        $commandOutput = $this->createCommandTester('job:id')
            ->withAnswersToCommandQuestions($jobId)
            ->withMocksForCommandHttpRequests(["JobSingle.txt"])
            ->execute();
    
        $this->stringShouldMatchPattern($commandOutput, '/.*Please enter the id of the job.*/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$jobId."[\"']?/");
    }
    
    public function testCanFindAJobsByUserId()
    {
        $userId = 1;
        $commandOutput = $this->createCommandTester('job:user')
            ->withAnswersToCommandQuestions($userId)
            ->withMocksForCommandHttpRequests(["JobsWithStatusRunning.txt"])
            ->execute();
        
        $this->stringShouldMatchPattern($commandOutput, '/.*Please enter the id of the user you want to filter on.*/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?user[\"']?\s?:\s?[\"']?".$userId."[\"']?/");
    }
    
    public function testCanFindJobsByStatus()
    {
        $commandOutput = $this->createCommandTester('job:status')
            ->withAnswersToCommandQuestions(Job::STATUS_RUNNING)
            ->withMocksForCommandHttpRequests(["JobsWithStatusRunning.txt"])
            ->execute();
    
        $this->stringShouldMatchPattern($commandOutput, '/.*Please choose the status you want to filter on.*/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?status[\"']?\s?:\s?[\"']?running[\"']?/");
    }
    
    public function testCanLaunchAJob()
    {
        $commandOutput = $this->createCommandTester('job:launch')
            ->withAnswersToCommandQuestions("1\n 0.1.0 \n \n /somepath/somefile.txt;/somepath/somefile.txt \n \n 30 \n \n 136 \n")
            ->withMocksForCommandHttpRequests(["PublicPipelines.txt", "JobLaunch.txt"])
            ->execute();
    
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?status[\"']?\s?:\s?[\"']?scheduled[\"']?/");
    }
}
