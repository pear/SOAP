<?php
 
require_once '../util.php';
require_once '../Server.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'ServerTest_Round2Base.php';
require_once 'ServerHandler_Round2Base.php';
    

class test_Server_Round2Base extends test_ServerTest_Round2Base {
    public $server;
    public $wsdl;
    
    public function __construct($name = 'test_Server_Round2Base') {
        parent::__construct($name);
        PHPUnit_Framework_Assert::setLooselyTyped(true);
    }
    
    public function setUp() {
        $this->server = new SOAP_Server;
        $service = new ServerHandler_Round2Base();
        $this->wsdl = WSDLManager::get($service->getWSDLURI());
        $this->server->addService($service);
    }
    
}

# Unit test runner

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$test = new test_Server_Round2Base();
$class = new Reflection_Class($test);

$suite = new PHPUnit_Framework_TestSuite($class);

$result = PHPUnit_TextUI_TestRunner::run($suite);

?>