<?php
// Call SOAP_BugsTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SOAP_BugsTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SOAP/Base.php';

/**
 * Test class for SOAP bugs.
 */
class SOAP_BugsTest extends PHPUnit_Framework_TestCase {
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("SOAP_BugsTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
    }



    /**
    *   Bug #10131: incorrect return value in case of an empty array
    */
    public function testBug10131()
    {
        require_once 'SOAP/Parser.php';

        /*
        require_once 'SOAP/Base.php';
        require_once 'SOAP/Value.php';
        $val  = new SOAP_Value('arraytest', 'Array', array());
        $val  = new SOAP_Value('arraytest', 'Array', array('test', 12));
        $base = new SOAP_Base();
        echo $val->serialize($base);
        /**/

        //test filled array
        $msg =<<<EOT
<?xml version="1.0" encoding="UTF-8"?>

<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<SOAP-ENV:Body>
<arraytest xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:anyType[2]" SOAP-ENC:offset="[0]">
<item xsi:type="xsd:string">test</item>
<item xsi:type="xsd:int">12</item></arraytest>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOT;

        $parser = new SOAP_Parser($msg);
        $soapval = $parser->getResponse();

        $this->assertEquals('arraytest', $soapval->name);
        $this->assertEquals('Array',     $soapval->type);
        $this->assertEquals(2,           count($soapval->value));

        $this->assertEquals('item',      $soapval->value[0]->name);
        $this->assertEquals('string',    $soapval->value[0]->type);
        $this->assertEquals('test',      $soapval->value[0]->value);

        $this->assertEquals('item',      $soapval->value[1]->name);
        $this->assertEquals('int',       $soapval->value[1]->type);
        $this->assertEquals(12,          $soapval->value[1]->value);



        //test empty array
        $msg =<<<EOT
<?xml version="1.0" encoding="UTF-8"?>

<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<SOAP-ENV:Body>
<arraytest xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:anyType[0]" xsi:nil="true"/>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOT;

        $parser = new SOAP_Parser($msg);
        $soapval = $parser->getResponse();
        $this->assertEquals('arraytest', $soapval->name);
        $this->assertEquals(array(),     $soapval->value);
    }


}

// Call SOAP_BugsTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "SOAP_BugsTest::main") {
    SOAP_BugsTest::main();
}
?>
