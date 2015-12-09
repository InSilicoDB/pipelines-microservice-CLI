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

class HidePipeline extends PipelineManagerAPICommand
{
    protected $commandName = 'pipeline:hide';
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl   = $input->getArgument('base_url');
        $api       = $this->getPipelineMicroserviceApi($baseUrl,$this->httpHandler);
        $pipelines = $api->pipelines->getPublished();
        
        if( !empty($pipelines) ){
            $messages    = [];
            foreach ($pipelines as $pipe) {
                $messages[] = json_encode($pipe,JSON_PRETTY_PRINT);
//                 $messages[] = json_encode($pipe);
            }
            
            $helper   = $this->getHelper('question');
            $question = new ChoiceQuestion(
                    'Please select the id of the pipeline you wish to unpublish: ',
                    $messages
            );
            $question->setErrorMessage('Selected number %s is invalid.');
            
            $pipelineJson = $helper->ask($input, $output, $question);
            $idx          = array_search($pipelineJson, $messages);
            $pipeline     = $pipelines[$idx];
            
            $questionConfirm = new ConfirmationQuestion("Are you sure to unpublish pipeline $idx?");
            
            if (!$helper->ask($input, $output, $questionConfirm)) {
                return;
            }
            
            $output->writeln( "" );
            $output->writeln( "Unpublishing pipeline: " );
            $response = $api->pipelines->publish( $pipeline->getId() );
            $output->writeln( json_encode( $response, JSON_PRETTY_PRINT) );
//             $output->writeln( json_encode( $response) );
        }else{
            $output->writeln( "There are no pipelines available to unpublish." );
        }
    }
}