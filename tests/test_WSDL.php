<?php
 
require_once '../WSDL.php';
require_once 'PHPUnit/Framework/TestCase.php';
    
class test_WSDL extends PHPUnit_Framework_TestCase {
    public $urlList = array();

    public function __construct($name = '') {
        parent::__construct($name);
    }
    
    public function setUp() {
        $this->urlList[] = 'http://www.xmethods.net/wsdl/query.wsdl';
        $this->urlList[] = 'http://localhost/soap/tests/imported/InteropTest.wsdl';
        $this->urlList[] = 'http://localhost/soap/tests/interop.wsdl';
    }

    public function testFetch() {
        foreach($this->urlList as $url) {
            $result = WSDLManager::fetch($url);
            self::assertNotNull($result);
        }
    }

    public function testGet() {
        foreach($this->urlList as $url) {
            $result = WSDLManager::get($url);
            self::assertNotNull($result);
        }
    }

}

# Unit test runner

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$class = new Reflection_Class(new test_WSDL);

$suite = new PHPUnit_Framework_TestSuite($class);

$result = PHPUnit_TextUI_TestRunner::run($suite);

?>