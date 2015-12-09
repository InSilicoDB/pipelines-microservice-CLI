<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Command\Command;
use GuzzleHttp\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;

abstract class PipelineManagerAPICommand extends Command
{
    
    protected $httpHandler = null;
    protected $commandName = null;
    
    public function __construct($name = null, $httpHandler=null)
    {
        parent::__construct($name);
        $this->httpHandler = $httpHandler;
    }
    
    protected function getHttpClient($baseUrl,$httpHandler=null)
    {
        $config = ["base_url"=>$baseUrl];
        if ($httpHandler){
            $config['handler'] = $httpHandler;
        }
        return new Client($config);
    }
    
    protected function getPipelineMicroserviceApi($baseUrl,$httpHandler=null){
        $client   = $this->getHttpClient($baseUrl,$httpHandler);
        return new PipelinesMicroserviceApi($client);
    }
    
    protected function configure()
    {
        $configDirectories  = [__DIR__.'/../..'];
        $locator            = new FileLocator($configDirectories);
        $yamlCommandFile    = $locator->locate('PipelineManagerAPICommand.yml');
        $configArray        = Yaml::parse($yamlCommandFile);
        $commandConfigArr   = $configArray["commands"][$this->commandName];
        
        $this
            ->setName($this->commandName)
            ->setDescription($commandConfigArr["description"]);
        
        if( isset($commandConfigArr["arguments"]) ){
            $this->addArguments($commandConfigArr["arguments"]);
        }
    }
    
    protected function addArguments(array $argurmentsConfig)
    {
        foreach ($argurmentsConfig as $argument=>$argumentConfig) {
            $mode = $argumentConfig["mode"];
            switch ($mode) {
                case "required":
                    $mode = InputArgument::REQUIRED;
                    break;
                case "optional":
                    $mode = InputArgument::OPTIONAL;
                    break;
                case "array":
                    $mode = InputArgument::IS_ARRAY;
                    break;
                default:
                    throw new InvalidArgumentException("Invalid argurment mode $mode");
                    break;
            }
        
            $this->addArgument(
                    $argument,
                    $mode,
                    $argumentConfig["description"],
                    isset($argumentConfig["default"])?$argumentConfig["default"]:null
            );
        }
    }
   
}