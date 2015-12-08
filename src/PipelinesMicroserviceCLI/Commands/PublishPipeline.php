<?php

namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PipelinesMicroservice\Services\PipelineApi;
use GuzzleHttp\Client;
use PipelinesMicroservice\PipelinesMicroserviceApi;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
        );
        
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl  = $input->getArgument('base_url');
        $client   = $this->getHttpClient($baseUrl,$this->httpHandler);
        $api      = new PipelinesMicroserviceApi($client);
        $pipelinesToPublish = $api->pipelines->getHidden();
        
        if( !empty($pipelinesToPublish) ){
            $pipelineIds = [];
            $messages    = [];
            foreach ($pipelinesToPublish as $pipeline) {
                $pipelineIds[]  = $pipeline->getId();
                $messages[]     = json_encode($pipeline,JSON_PRETTY_PRINT);
            }
            
            $output->write($messages,true);
            
            $helper   = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please the id of the pipeline you which to publish: ',
                $pipelineIds
            );
            $question->setErrorMessage('Pipeline id %s is invalid.');
            $id = $helper->ask($input, $output, $question);
            $questionConfirm = new ConfirmationQuestion("Are you sure to publish pipeline $id?");
            
            if (!$helper->ask($input, $output, $questionConfirm)) {
                return;
            }
            
            $output->writeln( "Publishing pipeline $id: " );
            $output->writeln( json_encode( $api->pipelines->publish($id)) );
        }else{
            $output->writeln( "There are no pipelines available to publish." );
        }
    }
}