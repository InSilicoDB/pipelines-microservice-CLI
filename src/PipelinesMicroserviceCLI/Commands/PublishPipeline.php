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

class PublishPipeline extends PipelineManagerAPICommand
{
    protected $commandName = 'pipeline:publish';
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl  = $input->getArgument('base_url');
        $api      = $this->getPipelineMicroserviceApi($baseUrl,$this->httpHandler);
        $pipelinesToPublish = $api->pipelines->getHidden();
        
        if( !empty($pipelinesToPublish) ){
            $messages = [];
            foreach ($pipelinesToPublish as $pipe) {
                $messages[] = json_encode($pipe,JSON_PRETTY_PRINT);
            }
            
            $helper   = $this->getHelper('question');
            $question = new ChoiceQuestion(
                    'Please select the id of the pipeline you which to publish: ',
                    $messages
            );
            $question->setErrorMessage('Selected number %s is invalid.');
            
            $pipelineJson = $helper->ask($input, $output, $question);
            $idx          = array_search($pipelineJson, $messages);
            $pipeline     = $pipelinesToPublish[$idx];
            
            $questionConfirm = new ConfirmationQuestion("Are you sure to publish pipeline $idx?");
            
            if (!$helper->ask($input, $output, $questionConfirm)) {
                return;
            }
            $output->writeln( "" );
            $output->writeln( "Publishing pipeline: " );
            $response = $api->pipelines->publish( $pipeline->getId() );
            $output->writeln( json_encode( $response, JSON_PRETTY_PRINT) );
        }else{
            $output->writeln( "There are no pipelines available to publish." );
        }
    }
}