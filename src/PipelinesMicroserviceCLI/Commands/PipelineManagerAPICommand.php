<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Command\Command;
use GuzzleHttp\Client;

abstract class PipelineManagerAPICommand extends Command
{
    
    protected $httpHandler = null;
    
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
   
}