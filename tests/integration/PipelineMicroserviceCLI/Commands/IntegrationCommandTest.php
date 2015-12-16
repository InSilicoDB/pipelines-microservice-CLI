<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Client;
use PipelinesMicroservice\Hydrators\PipelineHydrator;
use PipelinesMicroservice\PipelinesMicroserviceApi;
use PipelinesMicroservice\Entities\Pipeline;
use PipelinesMicroservice\Types\Release;

class IntegrationCommandTest extends \PipelineMicroserviceCLITestCase
{
    protected $env = "integration-test";
    
    protected $configurationArray;
    
    protected $api;
    
    public function setUp()
    {
        if ( empty($this->configurationArray) ) {
            $this->configurationArray = Yaml::parse(file_get_contents(TEST_DIR."/../../src/resources/PipelineManagerAPICommand.integration-test.yml"));
        }
        $this->api = $this->createApi();
    }
    
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
    
    protected function givenThereIsAPipeline()
    {
        return $this->api->pipelines->register(98, "NextFlow", "Git", "https://github.com/InSilicoDB/pipeline-kallisto.git");;
    }
    
    protected function whenAPipelineIsPublished($pipeline)
    {
        return $this->api->pipelines->publish($pipeline->getId());
    }
    
    protected function whenAPipelineContainsReleases(Pipeline $pipeline)
    {
        $timeout = 10;
        $timeLooping = 0;
        $startTime = microtime(true);
        while ( empty($pipeline->getReleases()) ) {
            if ( $timeLooping >= $timeout ) {
                throw new \Exception("Timeout of $timeout seconds is passed to fetch releases");
                break;
            }
            if ( $timeLooping > 0 ) {
                sleep(1);
            }
            $pipeline = $this->api->pipelines->findById($pipeline->getId());
            $timeLooping = microtime(true) - $startTime;
        }
    
        return $pipeline;
    }
    
    protected function createApi()
    {
        $client = new Client(["base_uri" => $this->configurationArray["base_uri"]]);
        
        return new PipelinesMicroserviceApi($client);
    }
    
    protected function whenAReleaseIsApproved(Pipeline $pipeline, Release $release)
    {
        return $this->api->pipelines->approveRelease($pipeline, $release);
    }
}
