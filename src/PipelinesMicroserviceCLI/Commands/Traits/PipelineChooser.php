<?php
namespace PipelinesMicroserviceCLI\Commands\Traits;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PipelinesMicroservice\Entities\Pipeline;

trait PipelineChooser
{
    use Chooser;
    
    /**
     * 
     * @param [Pipeline] $pipelines
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $questionString
     * @return Pipeline $pipeline
     */
    protected function askChoosePipeline($pipelines, InputInterface $input, OutputInterface $output, $questionString = "Please select a pipeline: ")
    {
        $pipelineResources = [];
        foreach ($pipelines as $pipeline) {
            $pipelineResources[$pipeline->getId()] = "Pipeline id: ".$pipeline->getId()." on ".$pipeline->getSource()->getResource();
        }
    
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            $questionString,
            $pipelineResources
        );
        $question->setErrorMessage('Selected id %s is invalid.');
        $question->setMaxAttempts(3);
    
        $pipelineResource = $helper->ask($input, $output, $question);
        $pipelineId = array_search($pipelineResource, $pipelineResources);
        $pipeline = null;
        foreach ($pipelines as $item) {
            if( $item->getId()==$pipelineId ){
                $pipeline = $item;
                break;
            }
        }
        
        return $pipeline;
    }
}
