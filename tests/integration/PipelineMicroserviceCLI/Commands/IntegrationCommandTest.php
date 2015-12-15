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
        if( empty($this->configurationArray) ){
            $this->configurationArray = Yaml::parse(file_get_contents(TEST_DIR."/../../src/resources/PipelineManagerAPICommand.integration-test.yml"));
        }
        $this->api = $this->createApi();
    }
    
    public function testCanPublishAPipeline()
    {
        $pipeline = $this->givenThereIsAPipeline();
        $pipelineId = $pipeline->getId();
        $commandOutput = $this->execute('pipeline:publish', "$pipelineId\n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, '/Publishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?published[\"']?\s?:\s?[\"']?Published[\"']?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$pipelineId."[\"']?/");
    }
    
    public function testCanHideAPipeline()
    {
        $pipeline = $this->givenThereIsAPipeline();
        $pipeline = $this->whenAPipelineIsPublished($pipeline);
        $pipelineId = $pipeline->getId();
        $commandOutput = $this->execute('pipeline:hide', "$pipelineId\n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, '/Unpublishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?published[\"']?\s?:\s?[\"']?Hidden[\"']?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$pipelineId."[\"']?/");
    }
    
    public function testCanApproveAPipelineRelease()
    {
        $pipeline = $this->givenThereIsAPipeline();
        $pipeline = $this->whenPipelineReleasesAreFetched($pipeline);
        $pipeline = $this->whenAPipelineIsPublished($pipeline);

        $release = $pipeline->getDeniedReleases()[0];
        $releaseName = $release->getName();
        
        $pipelineId = $pipeline->getId();
        $commandOutput = $this->execute('pipeline:approve', "$pipelineId\n $releaseName \n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, "/.*Are you sure to approve release $releaseName?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*Approving release: $releaseName\n.*/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$pipelineId."[\"']?/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?name[\"']?\s?:\s?[\"']?".$releaseName."[\"']?,[\\n\\r]*\s*[\"']?executePermission[\"']?\s?:\s?[\"']?Approved[\"']?/");
    }
    
    public function testCanDenyAPipelineRelease()
    {
        $pipeline = $this->givenThereIsAPipeline();
        $pipeline = $this->whenPipelineReleasesAreFetched($pipeline);
        $pipeline = $this->whenAPipelineIsPublished($pipeline);

        $release = $pipeline->getDeniedReleases()[0];
        $releaseName = $release->getName();
        $pipeline = $this->whenAReleaseIsApproved($pipeline,$release);
        
        $pipelineId = $pipeline->getId();
        $commandOutput = $this->execute('pipeline:deny', "$pipelineId\n $releaseName \n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, "/.*Are you sure to deny release .*?\nDenying release.*/");
        $this->stringShouldMatchPattern($commandOutput, "/.*Denying release: $releaseName\n.*/");
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?id[\"']?\s?:\s?[\"']?".$pipelineId."[\"']?/");
    }
    
    protected function givenThereIsAPipeline()
    {
        return $this->api->pipelines->register(98, "NextFlow", "Git", "https://github.com/InSilicoDB/pipeline-kallisto.git");;
    }
    
    protected function whenAPipelineIsPublished($pipeline)
    {
        return $this->api->pipelines->publish($pipeline->getId());
    }
    
    protected function whenPipelineReleasesAreFetched(Pipeline $pipeline)
    {
        $timeout = 10;
        $time = 0;
        $timeStart = microtime(true);
        while ( empty($pipeline->getDeniedReleases()) ){
            if( $time >= $timeout ){
                throw new \Exception("Timeout of $timeout seconds is passed to fetch releases");
                break;
            }
            if ( $time>0 ) {
                sleep(1);
            }
            $pipeline = $this->api->pipelines->findById($pipeline->getId());
            $time = microtime(true)-$timeStart;
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
