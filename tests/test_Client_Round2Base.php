<?php
 
require_once '../util.php';
require_once '../Client.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Client_Round2Base.php';

class test_Client_WSDL extends test_Client_Calls {
    public $server;

    public function __construct($server = NULL, $name = 'test_Client_WSDL') {
        $this->server = $server;
        parent::__construct($name);
        PHPUnit_Framework_Assert::setLooselyTyped(true);
    }
    
    public function setUp() {
        if (!$this->server) {
            $this->server = 'http://localhost/soap/tests/interop.wsdl';
        }
        $this->client = new SOAP_Client($this->server, true);
    }
    
}

# Unit test runner

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$test = new test_Client_WSDL();
$class = new Reflection_Class($test);

$suite = new PHPUnit_Framework_TestSuite($class);

$result = PHPUnit_TextUI_TestRunner::run($suite);

?>