<?php

namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PipelinesMicroservice\Services\PipelineApi;
use GuzzleHttp\Client;
use PipelinesMicroservice\PipelinesMicroserviceApi;

class PublishPipeline extends CLICommand
{
    protected function configure()
    {
        $this
        ->setName('pipeline:publish')
        ->setDescription('Publish a pipeline')
        ->addArgument(
                'base_url',
                InputArgument::REQUIRED,
                'The location of the pipeline microservice'
        )->addArgument(
                'id',
                InputArgument::REQUIRED,
                'The id of the pipeline you want to publish'
        );
        
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl   = $input->getArgument('base_url');
        $id        = $input->getArgument('id');
        if ($id && $baseUrl) {
            $client   = $this->getHttpClient($baseUrl,$this->httpHandler);
            $api      = new PipelinesMicroserviceApi($client);
            $output->write( json_encode( $api->pipelines->publish($id)) );
        }
    }
}