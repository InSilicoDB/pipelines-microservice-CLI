<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PipelinesMicroserviceCLI\Commands\Traits\PipelineChooser;

class PublishPipeline extends PipelineManagerAPICommand
{
    use PipelineChooser;
    
    protected function configure()
    {
        $this
            ->setName('pipeline:publish')
            ->setDescription('Publish a hidden pipeline');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getPipelineMicroserviceApi();
        $pipelinesToPublish = $api->pipelines->getHidden();
        
        if( !empty($pipelinesToPublish) ){
            $pipeline = $this->askChoosePipeline($pipelinesToPublish, $input, $output, 'Please select the id of the pipeline you which to publish: ');
            
            if ( !$this->askConfirmChoice($input, $output, "Are you sure to publish the pipeline?") ) {
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