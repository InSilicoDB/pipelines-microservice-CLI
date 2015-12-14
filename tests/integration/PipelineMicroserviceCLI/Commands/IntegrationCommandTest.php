<?php
namespace PipelinesMicroserviceCLI\Commands;

use Symfony\Component\Yaml\Yaml;

class IntegrationCommandTest extends \PipelineMicroserviceCLITestCase
{
    protected $env = "integration-test";
    
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
     * The truncate is done twice because it does not always works.
     * The db is assigne null to close the connection with the database.
     * */
    public function tearDown()
    {
        $this->db->exec("TRUNCATE TABLE pipelines;");
        $this->db = null;
    }
    
    public function testPublishPipeline()
    {
        $commandOutput = $this->execute('pipeline:publish', "0\n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, '/Publishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?published[\"']?\s?:\s?[\"']?Published[\"']?/");
    }
    
    public function testHidePipeline()
    {
        $commandOutput = $this->execute('pipeline:hide', "0\n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, '/Unpublishing pipeline:/');
        $this->stringShouldMatchPattern($commandOutput, "/.*[\"']?published[\"']?\s?:\s?[\"']?Hidden[\"']?/");
    }
    
    public function testApprovePipelineRelease()
    {
        $commandOutput = $this->execute('pipeline:approve', "0\n 0 \n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, "/.*Are you sure to approve release.*/");
    }
    
    public function testDenyPipelineRelease()
    {
        $commandOutput = $this->execute('pipeline:deny', "0\n 0 \n y \n");
        
        $this->stringShouldMatchPattern($commandOutput, "/.*Are you sure to deny release.*\nDenying release.*/");
    }
}
