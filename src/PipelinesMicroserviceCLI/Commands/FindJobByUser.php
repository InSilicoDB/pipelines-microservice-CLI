<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class FindJobByUser extends PipelineManagerAPICommand
{
    protected function configure()
    {
        $this
            ->setName('job:user')
            ->setDescription('Find jobs by the user owning the jobs');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userId = $this->askEnterUserId($input, $output);
        
        $jobs = $this->getPipelineMicroserviceApi()->jobs->findByUserId($userId);
        $output->writeln( "" );
        $output->writeln( json_encode( $jobs, JSON_PRETTY_PRINT) );
    }
    
    private function askEnterUserId(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the id of the user you want to filter on');
        $question->setValidator(function( $answer)
        {
            if ( !is_numeric($answer) ){
                throw new \RuntimeException("The id of the user should be numeric");
            }
            return $answer;
        });
        return $helper->ask($input, $output, $question);
    }
}