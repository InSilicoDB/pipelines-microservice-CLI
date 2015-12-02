<?php

namespace PipelinesMicroserviceCLI\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class CommandTestCase extends \PHPUnit_Framework_TestCase {
    
    protected function getHttpMockHandler( $mockReponsePaths ){
        $responses = [];
        foreach ($mockReponsePaths as $mockReponsePath) {
            $mockFile = __DIR__."/../../mock/".$mockReponsePath;
            if (!file_exists($mockFile)) {
                throw new InvalidArgumentException('Unable to open mock file: ' . $mockFile);
            }
            $responses[] = \GuzzleHttp\Psr7\parse_response(file_get_contents($mockFile));
        }
        // Create a mock and queue responses.
        $mock           = new MockHandler($responses);
        $handler        = HandlerStack::create($mock);
        return $handler;
    }
    
}
