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

class HidePipeline extends PipelineManagerAPICommand
{
    use PipelineChooser;
    
    protected function configure()
    {
        $this
            ->setName('pipeline:hide')
            ->setDescription('Hide a published pipeline');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api       = $this->getPipelineMicroserviceApi();
        $pipelines = $api->pipelines->getPublished();
        
        if( !empty($pipelines) ){
            $pipeline = $this->askChoosePipeline($pipelines, $input, $output, 'Please select the id of the pipeline you wish to unpublish: ');
            
            if ( !$this->askConfirmChoice($input, $output, "Are you sure to unpublish the pipeline?") ) {
                return;
            }
            
            $output->writeln( "" );
            $output->writeln( "Unpublishing pipeline: " );
            $response = $api->pipelines->hide( $pipeline->getId() );
            $output->writeln( json_encode( $response, JSON_PRETTY_PRINT) );
        }else{
            $output->writeln( "There are no pipelines available to unpublish." );
        }
    }
}