<?php
namespace PipelinesMicroserviceCLI\Commands\Traits;

use Symfony\Component\Console\Question\ChoiceQuestion;

trait Chooser
{
    abstract public function getHelper($name);
}
