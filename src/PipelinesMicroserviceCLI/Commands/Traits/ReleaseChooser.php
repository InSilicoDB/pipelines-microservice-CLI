<?php
namespace PipelinesMicroserviceCLI\Commands\Traits;

use Symfony\Component\Console\Question\ChoiceQuestion;

trait ReleaseChooser
{
    use Chooser;
    
    private function askChooseRelease($releases, $input, $output)
    {
        $releseNames = [];
        foreach ($releases as $rel) {
            $releseNames[] = $rel->getName();
        }
        
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select the number of the release: ',
            $releseNames
        );
        $question->setErrorMessage('Selected number is invalid.');
        
        $releaseName = $helper->ask($input, $output, $question);
        $releaseIndex = array_search($releaseName, $releseNames);
        $release = $releases[$releaseIndex];
        
        return $release;
    }
}
