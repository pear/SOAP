<?php
 
require_once '../util.php';
require_once 'PHPUnit/Framework/TestCase.php';
    
class test_URI extends PHPUnit_Framework_TestCase {
    public $urlList = array();

    public function __construct($name = 'test_URI') {
        parent::__construct($name);
    }
    
    public function setUp() {
        # first url gets parsed, second is the relative, third is
        # what we expect to get back
        $this->urlList[] =
            array('file:///c:/usr/src/pear5/WSDL/tests/interop.wsdl',
                'http://www.whitemesa.com/wsdl/wmmsgrouter.xsd',
                'http://www.whitemesa.com/wsdl/wmmsgrouter.xsd');
        $this->urlList[] =
            array('file:///c:/usr/src/pear5/WSDL/tests/interop.wsdl',
                'import2.wsdl',
                'file:///c:/usr/src/pear5/WSDL/tests/import2.wsdl');
        $this->urlList[] =
            array('file:///c:/usr/src/pear5/WSDL/tests/interop.wsdl',
                'imports/import2.wsdl',
                'file:///c:/usr/src/pear5/WSDL/tests/imports/import2.wsdl');
    }
    
    public function testRelative() {
        foreach ($this->urlList as $uritest) {
            $uri = parse_url($uritest[0]);
            $got = URI::mergeURI($uri,$uritest[1]);
            $this->assertEquals($got, $uritest[2]);
        }
    }

}

# Unit test runner

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$test = new test_URI();
$class = new Reflection_Class($test);

$suite = new PHPUnit_Framework_TestSuite($class);

$result = PHPUnit_TextUI_TestRunner::run($suite);

?>