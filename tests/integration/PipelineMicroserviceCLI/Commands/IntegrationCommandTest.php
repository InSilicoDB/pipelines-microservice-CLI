<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Client;
use PipelinesMicroservice\Hydrators\PipelineHydrator;
use PipelinesMicroservice\PipelinesMicroserviceApi;
use PipelinesMicroservice\Entities\Pipeline;
use PipelinesMicroservice\Types\Release;

class IntegrationCommandTest extends IntegrationCommandTestCase
{
    public function testCanPublishAPipeline()
    {
        $pipeline = $this->givenThereIsAPipeline();
        $commandOutput = $this->execute('pipeline:publish', $pipeline->getId()."\n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, '/Publishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?published[\"']?\s?:\s?[\"']?Published[\"']?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$pipeline->getId()."[\"']?/");
    }
    
    public function testCanHideAPipeline()
    {
        $pipeline = $this->givenThereIsAPipeline();
        $pipeline = $this->whenAPipelineIsPublished($pipeline);
        $commandOutput = $this->execute('pipeline:hide', $pipeline->getId()."\n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, '/Unpublishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?published[\"']?\s?:\s?[\"']?Hidden[\"']?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$pipeline->getId()."[\"']?/");
    }
    
    public function testCanApproveAPipelineRelease()
    {
        $pipeline = $this->givenThereIsAPipeline();
        $pipeline = $this->whenAPipelineContainsReleases($pipeline);
        $pipeline = $this->whenAPipelineIsPublished($pipeline);

        $release = $pipeline->getDeniedReleases()[0];
        
        $commandOutput = $this->execute('pipeline:approve', $pipeline->getId()."\n ".$release->getName()." \n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, "/.*Are you sure to approve release ".$release->getName()."?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*Approving release: ".$release->getName()."\n.*/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$pipeline->getId()."[\"']?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?name[\"']?\s?:\s?[\"']?".$release->getName()."[\"']?,[\\n\\r]*\s*[\"']?executePermission[\"']?\s?:\s?[\"']?Approved[\"']?/");
    }
    
    public function testCanDenyAPipelineRelease()
    {
        $pipeline = $this->givenThereIsAPipeline();
        $pipeline = $this->whenAPipelineContainsReleases($pipeline);
        $pipeline = $this->whenAPipelineIsPublished($pipeline);

        $release = $pipeline->getDeniedReleases()[0];
        $pipeline = $this->whenAReleaseIsApproved($pipeline,$release);
        
        $commandOutput = $this->execute('pipeline:deny', $pipeline->getId()."\n ".$release->getName()." \n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, "/.*Are you sure to deny release .*?\nDenying release.*/");
        $this->stringShouldMatchPattern($commandOutput, "/.*Denying release: ".$release->getName()."\n.*/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$pipeline->getId()."[\"']?/");
    }
    
    public function testCanRegisterAPipeline()
    {
        $authorId = 1;
        $sourceResource = "https://github.com/InSilicoDB/pipeline-kallisto.git";
        $commandOutput = $this->execute(
            'pipeline:register',
            null,
            ["author" => $authorId, "source-resource" => $sourceResource]
        );
    
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?author[\"']?\s?:\s?[\"']?".$authorId."[\"']?,.*/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?published[\"']?\s?:\s?[\"']?Hidden[\"']?,.*/");
    }
    
    public function testCanLaunchAJob()
    {
        $pipeline = $this->givenThereIsAPipeline();
        $pipeline = $this->whenAPipelineContainsReleases($pipeline);
        $pipeline = $this->whenAPipelineIsPublished($pipeline);
        
        $release = $pipeline->getRelease("0.10");
        $pipeline = $this->whenAReleaseIsApproved($pipeline,$release);
        $pipeline = $this->whenAPipelineReleaseContainsReleaseParameters($pipeline, $release);
        
        $commandOutput = $this->execute(
            'job:launch',
            $pipeline->getId()."\n ".$release->getName()." \n \n \n \n /somepath/somefile.txt,/somepath/somefile.txt \n \n /somepath/somefile.txt,/somepath/somefile.txt \n \n \n \n \n \n \n \n \n 136 \n"
        );
        
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?status[\"']?\s?:\s?[\"']?scheduled[\"']?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?pipelineId[\"']?\s?:\s?[\"']?".$pipeline->getId()."[\"']?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?releaseRef[\"']?\s?:\s?[\"']?".$release->getName()."[\"']?/");
    }
    
}
