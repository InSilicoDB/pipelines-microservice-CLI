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

class ApprovePipelineRelease extends PipelineManagerAPICommand
{
    protected $commandName = 'pipeline:approve';
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl  = $input->getArgument('base_url');
        $api      = $this->getPipelineMicroserviceApi($baseUrl,$this->httpHandler);
        $publishedPipelines = $api->pipelines->getPublished();
        
        if( !empty($publishedPipelines) ){
            $messages    = [];
            foreach ($publishedPipelines as $pipeline) {
                $messages[] = json_encode($pipeline,JSON_PRETTY_PRINT);
            }
            
            $helper   = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please select the pipeline you wish to approve a release of: ',
                $messages
            );
            $question->setErrorMessage('Pipeline id %s is invalid.');
            
            $pipelineJson = $helper->ask($input, $output, $question);
            $idx          = array_search($pipelineJson, $messages);
            $pipeline     = $publishedPipelines[$idx];
            
            $deniedReleases = $pipeline->getDeniedReleases();
            if( empty($deniedReleases) ){
                $output->writeln( "This pipeline has no releases to approve." );
                return;
            }
            
            $messages    = [];
            foreach ($deniedReleases as $rel) {
                $messages[] = $rel->getName();
            }
            
            $question = new ChoiceQuestion(
                'Please select the number of the release: ',
                $messages
            );
            $question->setErrorMessage('Selected number is invalid.');
            
            $releaseName = $helper->ask($input, $output, $question);
            $releaseIdx  = array_search($releaseName, $messages);
            $release     = $deniedReleases[$releaseIdx];
            
            $questionConfirm = new ConfirmationQuestion("Are you sure to approve release $releaseName?");
            
            
            if (!$helper->ask($input, $output, $questionConfirm)) {
                return;
            }
            
            $output->writeln( "" );
            $output->writeln( "Approving release: $releaseName" );
            $response = $api->pipelines->approveRelease($pipeline, $release);
            $output->writeln( json_encode( $response, JSON_PRETTY_PRINT) );
        }else{
            $output->writeln( "There are no pipelines available to publish." );
        }
    }
}