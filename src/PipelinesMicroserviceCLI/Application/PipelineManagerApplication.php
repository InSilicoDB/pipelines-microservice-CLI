<?php
namespace PipelinesMicroserviceCLI\Application;

use Symfony\Component\Console\Application;
use PipelinesMicroserviceCLI\Commands\ApprovePipelineRelease;
use PipelinesMicroserviceCLI\Commands\DenyPipelineRelease;
use PipelinesMicroserviceCLI\Commands\HidePipeline;
use PipelinesMicroserviceCLI\Commands\PublishPipeline;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class PipelineManagerApplication extends Application
{
    private $environment;
    
    private $configuration;
    
    private $httpHandler;
    
    public function __construct($environment = 'test', $httpHandler = null)
    {
        parent::__construct();
        
        // configure application environment
        $this->environment = $environment;
        $this->loadConfiguration();
        $this->httpHandler = $httpHandler;
        
        // load all usual commands
        $this->addCommandWithName(ApprovePipelineRelease::class);
        $this->addCommandWithName(DenyPipelineRelease::class);
        $this->addCommandWithName(HidePipeline::class);
        $this->addCommandWithName(PublishPipeline::class);
    }
    
    public function addCommandWithName($commandName)
    {
        return $this->add( new $commandName($this->configuration, $this->httpHandler) );
    }
    
    protected function createHttpHandler()
    {
        return null;
    }
    
    private function loadConfiguration()
    {
        $configDirectories = [__DIR__.'/../../resources/'];
        $configurationLocator = new FileLocator($configDirectories);
        $configurationFile = $configurationLocator->locate(
            'PipelineManagerAPICommand.' . $this->environment . '.yml'
        );
        $this->configuration = Yaml::parse($configurationFile);
    }
    
    protected function getConfiguration()
    {
        return $this->configuration;
    }
}