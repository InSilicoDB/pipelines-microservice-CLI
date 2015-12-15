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
            $releseNames[$rel->getName()] = "Release ".$rel->getName();
        }
        
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select the release reference: ',
            $releseNames
        );
        $question->setErrorMessage('Selected reference is invalid.');
        
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
