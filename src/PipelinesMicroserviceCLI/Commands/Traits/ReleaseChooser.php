<?php
namespace PipelinesMicroserviceCLI\Commands\Traits;

use Symfony\Component\Console\Question\ChoiceQuestion;

trait ReleaseChooser
{
    use Chooser;
    
    private function askChooseRelease($releases, $input, $output)
    {
        $releaseNames = [];
        foreach ($releases as $rel) {
            $releaseNames[$rel->getName()] = "Release ".$rel->getName();
        }
        
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select the release reference: ',
            $releaseNames
        );
        $question->setErrorMessage('Selected reference is invalid.');
        $question->setMaxAttempts(3);
        
        $releaseName = $helper->ask($input, $output, $question);
        $release = null;
        foreach ($releases as $item) {
            if( $item->getName()==$releaseName ){
                $release = $item;
                break;
            }
        }
        
        return $release;
    }
}
