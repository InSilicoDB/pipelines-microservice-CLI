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

class DenyPipelineRelease extends PipelineManagerAPICommand
{
    protected function configure()
    {
        $this
        ->setName('pipeline:deny')
        ->setDescription('Deny a pipeline release')
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
        $publishedPipelines = $api->pipelines->getPublished();
        
        if( !empty($publishedPipelines) ){
            $pipelineIds = [];
            $messages    = [];
            foreach ($publishedPipelines as $pipeline) {
                $messages[]     = json_encode($pipeline,JSON_PRETTY_PRINT);
            }
            
            $helper   = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please select the id of the pipeline you which to deny a release of: ',
                $messages
            );
            $question->setErrorMessage('Pipeline id %s is invalid.');
            
            $pipelineJson = $helper->ask($input, $output, $question);
            $idx        = array_search($pipelineJson, $messages);
            $pipeline   = $publishedPipelines[$idx];
            
            $approvedReleases = $pipeline->getApprovedReleases();
            if( empty($approvedReleases) ){
                $output->writeln( "This pipeline has no releases to deny." );
                return;
            }
            
            $messages    = [];
            foreach ($approvedReleases as $release) {
                $messages[]     = $release->getName();
            }
            
            $question = new ChoiceQuestion(
                'Please select the number of the release: ',
                $messages
            );
            $question->setErrorMessage('Selected number is invalid.');
            
            $releaseName = $helper->ask($input, $output, $question);
            $idx     = array_search($releaseName, $messages);
            $release = $approvedReleases[$idx];
            
            $questionConfirm = new ConfirmationQuestion("Are you sure to deny release $releaseName?");
            
            if (!$helper->ask($input, $output, $questionConfirm)) {
                return;
            }
            $output->writeln( "" );
            $output->writeln( "Denying release: $releaseName" );
            $output->writeln( json_encode( $api->pipelines->denyRelease($pipeline, $release),JSON_PRETTY_PRINT) );
        }else{
            $output->writeln( "There are no pipelines available to publish." );
        }
    }
}