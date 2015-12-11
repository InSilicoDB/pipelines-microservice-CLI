<?php

namespace PipelinesMicroserviceCLI\Commands;

use PipelinesMicroserviceCLI\Application\PipelineManagerApplication;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

class IntegrationCommandTestCase extends \PHPUnit_Framework_TestCase
{
    private $db;
    
    public function setUp()
    {
        $configArray = Yaml::parse(file_get_contents(TEST_DIR.'/../../src/resources/PipelineManagerAPICommand.integration-test.yml'));
        $dbConfigArray = $configArray["db"];
        $host = $dbConfigArray["host"];
        $port = $dbConfigArray["port"];
        $dbName = $dbConfigArray["name"];
        $user = $dbConfigArray["user"];
        $passwd = $dbConfigArray["password"];
        
        $this->db = new \PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $passwd);
        
        $this->db->exec("TRUNCATE TABLE pipelines;");
        
        $sql = file_get_contents(TEST_DIR.'/resources/setup.sql');
        
        $qr = $this->db->exec($sql);
    }
    
    /**
     * 
     * @param String $commandName
     * @param array $mockReponsePaths
     * @param String $answers
     * @return CommandTester
     */
    protected function createTesterWithCommandNameAndAnswers($commandName, $answers)
    {
        $application = new PipelineManagerApplication('integration-test');
        
        $command = $application->find($commandName);
        
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream($answers));
        
        return new CommandTester($command);
    }
    
    protected function execute($commandName, $answers, array $arguments = [] )
    {
        $commandTester = $this->createTesterWithCommandNameAndAnswers($commandName, $answers);
        if ( !isset($arguments["command"]) ) {
            $arguments["command"] = $commandName;
        }
        $commandTester->execute($arguments);
        return $commandTester->getDisplay();
    }
    
    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);
    
        return $stream;
    }
    
    public function stringShouldMatchPattern($string,$pattern)
    {
        return $this->assertRegExp($pattern, $string);
    }
}
