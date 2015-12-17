<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Client;
use PipelinesMicroservice\Hydrators\PipelineHydrator;
use PipelinesMicroservice\PipelinesMicroserviceApi;
use PipelinesMicroservice\Entities\Pipeline;
use PipelinesMicroservice\Types\Release;

abstract class IntegrationCommandTestCase extends \PipelineMicroserviceCLITestCase
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
        $closure = function(Pipeline $pipeline)
        {
            return empty($pipeline->getReleases());
        };
        return $this->refreshPipelineUntilConditionFullfilled($pipeline, $closure);
    }
    
    protected function whenAPipelineReleaseContainsReleaseParameters(Pipeline $pipeline, Release $release)
    {
        $closure = function(Pipeline $pipeline) use ($release)
        {
            $releaseWithParameters = $pipeline->getRelease($release->getName());
            return empty($releaseWithParameters->getParameters());
        };
        return $this->refreshPipelineUntilConditionFullfilled($pipeline, $closure);
    }
    
    private function refreshPipelineUntilConditionFullfilled(Pipeline $pipeline, $conditionClosure, $timeout = 10)
    {
        $timeLooping = 0;
        $startTime = microtime(true);
        while ( call_user_func($conditionClosure, $pipeline) ) {
            if ( $timeLooping >= $timeout ) {
                throw new \Exception("Timeout of $timeout seconds is passed to refresh pipeline");
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
    
    private function createApi()
    {
        $client = new Client(["base_uri" => $this->configurationArray["base_uri"]]);
        
        return new PipelinesMicroserviceApi($client);
    }
    
    protected function whenAReleaseIsApproved(Pipeline $pipeline, Release $release)
    {
        return $this->api->pipelines->approveRelease($pipeline, $release);
    }
}
