<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Command\Command;
use GuzzleHttp\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use PipelinesMicroservice\PipelinesMicroserviceApi;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class PipelineManagerAPICommand extends Command
{
    private $appConfiguration;
    
    private $api;
    
    public function __construct(array $appConfiguration, $httpHandler) 
    {
        parent::__construct();
        $this->appConfiguration = $appConfiguration;
        $this->api = new PipelinesMicroserviceApi($this->createHttpClient($httpHandler));
    }
    
    private function createHttpClient($httpHandler)
    {
        $config = ['base_url' => $this->appConfiguration['base_url']];
        if ($httpHandler){
            $config['handler'] = $httpHandler;
        }
        
        return new Client($config);
    }
    
    protected function getPipelineMicroserviceApi()
    {
        return $this->api;
    }
    
    protected function askConfirmChoice($input, $output, $questionString = "Are you sure?")
    {
        $confirmed = true;
        $helper = $this->getHelper('question');
        $questionConfirm = new ConfirmationQuestion($questionString);
        if ( !$helper->ask($input, $output, $questionConfirm) ) {
            $confirmed = false;
        }
        return $confirmed;
    }
}