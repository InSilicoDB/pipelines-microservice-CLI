<?php
namespace PipelinesMicroserviceCLI\Commands\Traits;

use Symfony\Component\Console\Question\ChoiceQuestion;

trait PipelineChooser
{
    protected function askChoosePipeline($pipelines, $input, $output, $questionString = "Please select a pipeline: "){
        $pipelineJsons = [];
        foreach ($pipelines as $pipeline) {
            $pipelineJsons[] = json_encode($pipeline,JSON_PRETTY_PRINT);
        }
    
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
                $questionString,
                $pipelineJsons
        );
        $question->setErrorMessage('Selected number %s is invalid.');
    
        $pipelineJson = $helper->ask($input, $output, $question);
        $pipelineIndex = array_search($pipelineJson, $pipelineJsons);
        $pipeline = $pipelines[$pipelineIndex];
        return $pipeline;
    }
}
