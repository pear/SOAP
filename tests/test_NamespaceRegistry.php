<?php
 
require_once '../NamespaceRegistry.php';
require_once 'PHPUnit/Framework/TestCase.php';
    
class test_Namespace_Registry extends PHPUnit_Framework_TestCase {

    public function __construct($name = '') {
        parent::__construct($name);
    }
    
    public function testGetPrefix() {
        $this->assertEquals('xsd', Namespace_Registry::getPrefix('http://www.w3.org/2001/XMLSchema'));
    }

    public function testGet() {
        $this->assertEquals('http://www.w3.org/2001/XMLSchema', Namespace_Registry::getURI('xsd'));
    }

    public function testRegister() {
        $ns = new Namespace('urn:myurn');
        $this->assertEquals($ns->prefix, Namespace_Registry::getPrefix('urn:myurn'));
        $this->assertEquals('urn:myurn', Namespace_Registry::getURI($ns->prefix));
    }

    public function testNamespace() {
        $ns = new Namespace('urn:myurn');
        $this->assertEquals($ns->uri, 'urn:myurn');
        $this->assertEquals((string)$ns,'urn:myurn');
    }

}

# Unit test runner

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$class = new Reflection_Class(new test_Namespace_Registry);

$suite = new PHPUnit_Framework_TestSuite($class);

$result = PHPUnit_TextUI_TestRunner::run($suite);

?>