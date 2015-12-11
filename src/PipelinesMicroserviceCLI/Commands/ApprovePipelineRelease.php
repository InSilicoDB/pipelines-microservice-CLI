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
use PipelinesMicroserviceCLI\Commands\Traits\PipelineChooser;
use PipelinesMicroserviceCLI\Commands\Traits\ReleaseChooser;

class ApprovePipelineRelease extends PipelineManagerAPICommand
{
    use PipelineChooser;
    use ReleaseChooser;
    
    protected function configure()
    {
        $this
            ->setName('pipeline:approve')
            ->setDescription('Approve a pipeline release');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getPipelineMicroserviceApi();
        $publishedPipelines = $api->pipelines->getPublished();
        
        if ( !empty($publishedPipelines) ) {
            
            $pipeline = $this->askChoosePipeline($publishedPipelines, $input, $output, 'Please select the pipeline you wish to approve a release of: ');
            
            $deniedReleases = $pipeline->getDeniedReleases();
            if( empty($deniedReleases) ){
                $output->writeln( "This pipeline has no releases to approve." );
                return;
            }
            $release = $this->askChooseRelease($deniedReleases, $input, $output);
            
            if ( !$this->askConfirmChoice($input, $output, "Are you sure to approve release ".$release->getName()."?") ) {
                return;
            }
            
            $output->writeln( "" );
            $output->writeln( "Approving release: ".$release->getName() );
            $response = $api->pipelines->approveRelease($pipeline, $release);
            $output->writeln( json_encode( $response, JSON_PRETTY_PRINT) );
        }else{
            $output->writeln( "There are no pipelines available to publish." );
        }
    }

}