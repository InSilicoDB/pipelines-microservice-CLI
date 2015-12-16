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

class Job extends PipelineManagerAPICommand
{
    protected function configure()
    {
        $this
            ->setName('job')
            ->setDescription('Show the list of commands to interact with pipelines-microservice jobs');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jobActions = [
            "Search job by job id",
            "Search jobs by user id",
            "Search jobs by job status",
        ];
        
        $jobAction = $this->askChooseJobAction($input, $output, $jobActions);
        
        switch ($jobAction) {
            case $jobActions[0]:
                $command = $this->getApplication()->find('job:id');
                $command->run($input, $output);
            break;
            case $jobActions[1]:
                ;
            break;
            case $jobActions[2]:
                ;
            break;
            default:
                ;
            break;
        }
    }
    
    protected function askChooseJobAction(InputInterface $input, OutputInterface $output, $jobActions, $questionString = "Select the interaction you wish to do:")
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
                $questionString,
                $jobActions
        );
        $question->setErrorMessage('Selected choise %s is invalid.');
        $question->setMaxAttempts(3);
        
        return $helper->ask($input, $output, $question);
    }
}