<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PipelinesMicroserviceCLI\Commands\Traits\PipelineChooser;
use PipelinesMicroserviceCLI\Commands\Traits\ReleaseChooser;

class DenyPipelineRelease extends PipelineManagerAPICommand
{
    use PipelineChooser;
    use ReleaseChooser;
    
    protected function configure()
    {
        $this
            ->setName('pipeline:deny')
            ->setDescription('Deny a pipeline release');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getPipelineMicroserviceApi();
        $publishedPipelines = $api->pipelines->getPublished();
        
        if( !empty($publishedPipelines) ){
            $pipeline = $this->askChoosePipeline($publishedPipelines, $input, $output, 'Please select the pipeline you wish to deny a release of: ');
            
            $approvedReleases = $pipeline->getApprovedReleases();
            if( empty($approvedReleases) ){
                $output->writeln( "This pipeline has no releases to deny." );
                return;
            }
            $release = $this->askChooseRelease($approvedReleases, $input, $output);
            
            if ( !$this->askConfirmChoice($input, $output, "Are you sure to deny release ".$release->getName()."?") ) {
                return;
            }
            
            $output->writeln( "" );
            $output->writeln( "Denying release: ".$release->getName() );
            $response = $api->pipelines->denyRelease($pipeline, $release);
            $output->writeln( json_encode( $response, JSON_PRETTY_PRINT) );
        }else{
            $output->writeln( "There are no pipelines available to publish." );
        }
    }
}