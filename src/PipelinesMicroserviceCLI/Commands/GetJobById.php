<?php

namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PipelinesMicroservice\Services\PipelineApi;
use GuzzleHttp\Client;
use PipelinesMicroservice\PipelinesMicroserviceApi;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use PipelinesMicroserviceCLI\Commands\Traits\PipelineChooser;
use Symfony\Component\Console\Question\Question;

class GetJobById extends PipelineManagerAPICommand
{
    protected function configure()
    {
        $this
            ->setName('job:id')
            ->setDescription('Find a job by id');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getPipelineMicroserviceApi();
        
        $jobId = $this->askEnterIdOfJob($input, $output);
        
        $job = $api->jobs->findById($jobId);
        $output->writeln( "" );
        $output->writeln( json_encode( $job, JSON_PRETTY_PRINT) );
    }
    
    private function askEnterIdOfJob(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the id of the job');
        $question->setValidator(function( $answer)
        {
            if ( !is_numeric($answer) ){
                throw new \RuntimeException("The id of the job should be numeric");
            }
            return $answer;
        });
        return $helper->ask($input, $output, $question);
    }
}