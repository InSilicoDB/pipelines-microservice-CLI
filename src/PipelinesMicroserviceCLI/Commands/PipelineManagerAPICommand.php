<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Command\Command;
use GuzzleHttp\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;
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
        $config = ['base_uri' => $this->appConfiguration['base_uri']];
        if ($httpHandler){
            $config['handler'] = $httpHandler;
        }
        
        return new Client($config);
    }
    
    protected function getPipelineMicroserviceApi()
    {
        return $this->api;
    }
    
    protected function askConfirmChoice($input, $output, $questionString = "Are you sure?", $default = true)
    {
        $confirmed = true;
        $helper = $this->getHelper('question');
        $questionConfirm = new ConfirmationQuestion($questionString, $default);
        if ( !$helper->ask($input, $output, $questionConfirm) ) {
            $confirmed = false;
        }
        return $confirmed;
    }
}