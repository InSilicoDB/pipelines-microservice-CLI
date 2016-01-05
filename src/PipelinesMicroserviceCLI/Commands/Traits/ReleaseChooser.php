<?php
namespace PipelinesMicroserviceCLI\Commands\Traits;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PipelinesMicroservice\Types\Release;

trait ReleaseChooser
{
    use Chooser;
    
    /**
     * @param [Release] $releases
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return Release
     */
    private function askChooseRelease($releases, InputInterface $input, OutputInterface $output)
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
