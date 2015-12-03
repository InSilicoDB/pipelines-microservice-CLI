<?php

namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PipelinesMicroservice\Services\PipelineApi;
use GuzzleHttp\Client;
use PipelinesMicroservice\PipelinesMicroserviceApi;

class RegisterPipeline extends CLICommand
{
    protected function configure()
    {
        $this
        ->setName('pipeline:register')
        ->setDescription('Register a pipeline in the microservice')
        ->addArgument(
                'base_url',
                InputArgument::REQUIRED,
                'The location of the pipeline microservice'
        )->addArgument(
                'author',
                InputArgument::REQUIRED,
                'The id of the author defined in the InSilico DB application'
        )->addArgument(
                'source-resource',
                InputArgument::REQUIRED,
                'The url where the pipeline code is located'
        )->addArgument(
                'source-method',
                InputArgument::OPTIONAL,
                'The type of source where the pipeline code is located',
                'Git'
        )->addArgument(
                'engine',
                InputArgument::OPTIONAL,
                'The engine which the pipeline is made to run on.',
                'Nextflow'
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl        = $input->getArgument('base_url');
        $author         = $input->getArgument('author');
        $engine         = $input->getArgument('engine');
        $sourceMethod   = $input->getArgument('source-method');
        $sourceResource = $input->getArgument('source-resource');
        
        $client             = $this->getHttpClient($baseUrl,$this->httpHandler);
        $api                = new PipelinesMicroserviceApi($client);
        $registeredPipeline = $api->pipelines->register($author, $engine, $sourceMethod, $sourceResource);
        $output->write( json_encode( $registeredPipeline ) );
    }
}