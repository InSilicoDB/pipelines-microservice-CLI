<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use PipelinesMicroserviceCLI\Commands\Traits\PipelineChooser;
use PipelinesMicroserviceCLI\Commands\Traits\ReleaseChooser;
use PipelinesMicroserviceCLI\QuestionValidators\Validators;
use PipelinesMicroservice\Types\PipelineParameter;

class LaunchJob extends PipelineManagerAPICommand
{
    use PipelineChooser;
    use ReleaseChooser;
    
    private $parameterInputSection = "Parameters-input";
    
    protected function configure()
    {
        $this
            ->setName('job:launch')
            ->setDescription('Launch a job for given pipeline and release');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = $this->getPipelineMicroserviceApi();
        
        $pipelines = $api->pipelines->getPublished();
        $pipeline = $this->askChoosePipeline($pipelines, $input, $output);
        
        $approvedReleases = $pipeline->getApprovedReleases();
        $release = $this->askChooseRelease($approvedReleases, $input, $output);
        
        $pipelineParameters = $release->getParameters();
        $pipelineParameters = $this->askEnterPipelineParameters($pipelineParameters, $input, $output);
        
        $userId = (int) $this->askEnterUserId($input, $output);
        
        $job = $api->jobs->runPipeline($pipeline->getId(), $userId, $release->getName(), $pipelineParameters);

        $output->writeln( "" );
        $output->writeln( json_encode( $job, JSON_PRETTY_PRINT) );
    }
    
    private function askEnterUserId(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the id of the user the job belongs to: ');
        $question->setValidator(Validators::integerValidator(true));
        
        return $helper->ask($input, $output, $question);
    }
    
    private function askEnterPipelineParameters( array $pipelineParameters, InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');

        if (!empty($pipelineParameters)) {
            $formattedLine = $formatter->formatSection($this->parameterInputSection, "Please provide the parameters to run the pipeline:");
            $output->writeln( $formattedLine );
        }
        $pipelineParametersToReturn = [];
        
        foreach ($pipelineParameters as $pipelineParameter) {
            $value = $this->askEnterPipelineParameter($pipelineParameter, $input, $output);
            if ( !$value && !$pipelineParameter->isRequired() ) {
                continue;
            } else {
                $pipelineParametersToReturn[$pipelineParameter->getName()] = $value;
            }
        }
        return $pipelineParametersToReturn;
    }
    
    private function askEnterPipelineParameter( PipelineParameter $pipelineParameter, InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $formatter = $this->getHelper('formatter');
        $formattedLine = $formatter->formatSection($this->parameterInputSection, "Parameter: ".$pipelineParameter->getName()." - ".$pipelineParameter->getDescription());
        $output->writeln($formattedLine);
        $requiredText = $pipelineParameter->isRequired() ? '<fg=red>REQUIRED</>':'<fg=green>NOT REQUIRED</>';
        $value = null;
        $formattedQuestionStr = null;
        $validator = null;
        switch ($pipelineParameter->getType()) {
            case PipelineParameter::TYPE_BOOLEAN:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please confirm if you want $requiredText ".$pipelineParameter->getName()." (y,n,ENTER=ignore): "
                );
                return $this->askConfirmChoice($input, $output, $formattedQuestionStr, false);
            break;
            case PipelineParameter::TYPE_INT:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$pipelineParameter->getName().", which need to be a integer: "
                );
                $validator = Validators::integerValidator($pipelineParameter->isRequired());
            break;
            case PipelineParameter::TYPE_DOUBLE:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$pipelineParameter->getName().", which need to be a double: "
                );
                $validator = Validators::doubleValidator($pipelineParameter->isRequired());
            break;
            case PipelineParameter::TYPE_FILE:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$pipelineParameter->getName().", which need to be a file: "
                );
                $validator = Validators::fileValidator($pipelineParameter->isRequired());
            break;
            case PipelineParameter::TYPE_FILES:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$pipelineParameter->getName().", which need to be a comma separated files: "
                );
                $validator = Validators::filesValidator($pipelineParameter->isRequired());
            break;
            case PipelineParameter::TYPE_STRING:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$pipelineParameter->getName().", which need to be a string: "
                );
                $validator = Validators::stringValidator($pipelineParameter->isRequired());
            break;
        }
        $question = new Question($formattedQuestionStr);
        $question->setValidator($validator);
        return $helper->ask($input, $output, $question);
    }
}