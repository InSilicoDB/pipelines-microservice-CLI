<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use PipelinesMicroservice\Entities\Job;

class FindJobByStatus extends PipelineManagerAPICommand
{
    protected function configure()
    {
        $this
            ->setName('job:status')
            ->setDescription('Find jobs by the status of the jobs');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $status = $this->askChooseStatus($input, $output);
        
        $jobs = $this->getPipelineMicroserviceApi()->jobs->findByStatus($status);
        $output->writeln( "" );
        $output->writeln( json_encode( $jobs, JSON_PRETTY_PRINT) );
    }
    
    private function askChooseStatus(InputInterface $input, OutputInterface $output)
    {
        $jobStatusArray = [
            Job::STATUS_SCHEDULED   => Job::STATUS_SCHEDULED,
            Job::STATUS_RUNNING     => Job::STATUS_RUNNING,
            Job::STATUS_FINISHED    => Job::STATUS_FINISHED,
            Job::STATUS_FINISHED    => Job::STATUS_ERROR
        ];
        
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please choose the status you want to filter on',
            $jobStatusArray
        );
        $question->setErrorMessage('Selected status %s is invalid.');
        $question->setMaxAttempts(3);
        
        return $helper->ask($input, $output, $question);
    }
}