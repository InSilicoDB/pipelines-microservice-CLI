<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use PipelinesMicroserviceCLI\Commands\Traits\PipelineChooser;
use PipelinesMicroserviceCLI\Commands\Traits\ReleaseChooser;
use PipelinesMicroservice\Types\ReleaseParameter;
use PipelinesMicroserviceCLI\QuestionValidators\Validators;

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
        
        $releaseParameters = $release->getParameters();
        $pipelineParameters = $this->askEnterReleaseParameters($releaseParameters, $input, $output);
        
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
    
    private function askEnterReleaseParameters( array $releaseParameters, InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');

        if (!empty($releaseParameters)) {
            $formattedLine = $formatter->formatSection($this->parameterInputSection, "Please provide the parameters to run the pipeline:");
            $output->writeln( $formattedLine );
        }
        $pipelineParameters = [];
        
        foreach ($releaseParameters as $releaseParameter) {
            $value = $this->askEnterReleaseParameter($releaseParameter, $input, $output);
            if ( !$value && !$releaseParameter->isRequired() ) {
                continue;
            } else {
                $pipelineParameters[$releaseParameter->getName()] = $value;
            }
        }
        return $pipelineParameters;
    }
    
    private function askEnterReleaseParameter( ReleaseParameter $releaseParameter, InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $formatter = $this->getHelper('formatter');
        $formattedLine = $formatter->formatSection($this->parameterInputSection, "Parameter: ".$releaseParameter->getName()." - ".$releaseParameter->getDescription());
        $output->writeln($formattedLine);
        $requiredText = $releaseParameter->isRequired() ? '<fg=red>REQUIRED</>':'<fg=green>NOT REQUIRED</>';
        $value = null;
        $formattedQuestionStr = null;
        $validator = null;
        switch ($releaseParameter->getType()) {
            case ReleaseParameter::TYPE_BOOLEAN:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please confirm if you want $requiredText ".$releaseParameter->getName()." (y,n,ENTER=ignore): "
                );
                return $this->askConfirmChoice($input, $output, $formattedQuestionStr, false);
            break;
            case ReleaseParameter::TYPE_INT:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$releaseParameter->getName().", which need to be a integer: "
                );
                $validator = Validators::integerValidator($releaseParameter->isRequired());
            break;
            case ReleaseParameter::TYPE_DOUBLE:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$releaseParameter->getName().", which need to be a double: "
                );
                $validator = Validators::doubleValidator($releaseParameter->isRequired());
            break;
            case ReleaseParameter::TYPE_FILE:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$releaseParameter->getName().", which need to be a file: "
                );
                $validator = Validators::fileValidator($releaseParameter->isRequired());
            break;
            case ReleaseParameter::TYPE_FILES:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$releaseParameter->getName().", which need to be a comma separated files: "
                );
                $validator = Validators::filesValidator($releaseParameter->isRequired());
            break;
            case ReleaseParameter::TYPE_STRING:
                $formattedQuestionStr = $formatter->formatSection(
                    $this->parameterInputSection,
                    "Please provide $requiredText ".$releaseParameter->getName().", which need to be a string: "
                );
                $validator = Validators::stringValidator($releaseParameter->isRequired());
            break;
        }
        $question = new Question($formattedQuestionStr);
        $question->setValidator($validator);
        return $helper->ask($input, $output, $question);
    }
}