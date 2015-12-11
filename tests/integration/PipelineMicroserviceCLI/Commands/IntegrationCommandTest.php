<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Yaml\Yaml;

class IntegrationCommandTest extends IntegrationCommandTestCase
{
//     public function testPublishPipeline()
//     {
//         $commandOutput = $this->execute('pipeline:publish', "1\n y \n");
        
//         $this->stringShouldMatchPattern($commandOutput, '/Publishing pipeline:/');
//         $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Published["\']?/');
//     }
    
    public function testHidePipeline()
    {
        $commandOutput = $this->execute('pipeline:hide', "0\n y \n");
        
        echo $commandOutput;
        
        $this->stringShouldMatchPattern($commandOutput, '/Unpublishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, '/.*["\']?published["\']?\s?:\s?["\']?Hidden["\']?/');
    }
    
//     public function testApprovePipelineRelease()
//     {
//         $commandOutput = $this->execute('pipeline:approve', "0\n 3 \n y \n");
        
//         $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to approve release.*/');
//     }
    
//     public function testDenyPipelineRelease()
//     {
//         $commandOutput = $this->execute('pipeline:deny', "0\n 2 \n y \n");
        
//         $this->stringShouldMatchPattern($commandOutput, '/.*Are you sure to deny release.*\nDenying release.*/');
//     }
}
