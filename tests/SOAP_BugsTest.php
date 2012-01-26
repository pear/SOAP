<?php
/**
 * @category Web Services
 * @package SOAP
 */

// Call SOAP_BugsTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "SOAP_BugsTest::main");
}
chdir(dirname(__FILE__) . '/../');

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'SOAP/Base.php';
require_once 'SOAP/Client.php';
require_once 'SOAP/Parser.php';
require_once 'SOAP/Value.php';
require_once 'SOAP/Type/dateTime.php';

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
    * Bug #10131: incorrect return value in case of an empty array
    *
    * @see http://pear.php.net/bugs/bug.php?id=10131
    */
    public function testBug10131()
    {

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
    }//public function testBug10131()



    /**
    * Bug 1312: When sending a nested array to a PEAR::SOAP WebService,
    * some elements are objects, not arrays.
    *
    * @see http://pear.php.net/bugs/bug.php?id=1312
    */
    public function testBug1312()
    {
        $msg = <<<EOX
<?xml version="1.0" encoding="utf-8"?><SOAP-ENV:Envelope
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body
xmlns:ns1="urn:something"><ns1:test
SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><anArray
soapenc:arrayType="xsd:anyType[4]"
xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/"
xsi:type="soapenc:Array"><item soapenc:arrayType="xsd:string[0]"
xsi:type="soapenc:Array" /><item soapenc:arrayType="xsd:double[4]"
xsi:type="soapenc:Array"><item xsi:type="xsd:double">1</item><item
xsi:type="xsd:double">5</item><item xsi:type="xsd:double">0</item><item
xsi:type="xsd:double">6</item></item><item
soapenc:arrayType="xsd:double[][3]" xsi:type="soapenc:Array"><item
soapenc:arrayType="xsd:double[3]" xsi:type="soapenc:Array"><item
xsi:type="xsd:double">8</item><item xsi:type="xsd:double">9</item><item
xsi:type="xsd:double">1</item></item><item
soapenc:arrayType="xsd:double[0]" xsi:type="soapenc:Array" /><item
soapenc:arrayType="xsd:double[6]" xsi:type="soapenc:Array"><item
xsi:type="xsd:double">5</item><item xsi:type="xsd:double">7</item><item
xsi:type="xsd:double">654</item><item
xsi:type="xsd:double">8</item><item xsi:type="xsd:double">1</item><item
xsi:type="xsd:double">32</item></item></item><item
soapenc:arrayType="xsd:double[2]" xsi:type="soapenc:Array"><item
xsi:type="xsd:double">54</item><item
xsi:type="xsd:double">57</item></item></anArray></ns1:test></SOAP-ENV:Body></SOAP-ENV:Envelope>
EOX;
        $parser = new SOAP_Parser($msg);
        $soapval = $parser->getResponse();

        $this->assertInternalType('array', $soapval->value);
        $this->assertInternalType('array', $soapval->value[0]->value);
        $this->assertEquals('anArray', $soapval->value[0]->name);
        //anArray -> item #3 is an object according to bug report
        $this->assertInternalType('array', $soapval->value[0]->value[2]->value);
    }



    /**
    * Bug #2627   Array in return object was not parsed correctly
    *
    * @see http://pear.php.net/bugs/bug.php?id=2627
    */
    public function testBug2627()
    {
        $val = new SOAP_Value('StructTest', 'Struct', array(
            new SOAP_Value('zero', 'string', 'val01'),
            new SOAP_Value('one', 'string', 'val11'),
            new SOAP_Value('one', 'string', 'val12'),
            new SOAP_Value('two', 'string', 'val21'),
            new SOAP_Value('two', 'string', 'val22'),
        ));

        $client = new SOAP_Base();
        $dec    = $client->_decode($val);
        $this->assertInternalType('object', $dec);
        $this->assertInternalType('string', $dec->zero);
        $this->assertInternalType('array' , $dec->one);
        $this->assertInternalType('array' , $dec->two);
        $this->assertEquals(2, count($dec->one));
        $this->assertEquals(2, count($dec->two));

        $this->assertEquals('val01', $dec->zero);
        $this->assertEquals('val11', $dec->one[0]);
        $this->assertEquals('val12', $dec->one[1]);
        $this->assertEquals('val21', $dec->two[0]);
        $this->assertEquals('val22', $dec->two[1]);
    }//public function testBug2627()



    /**
    * Bug #10206: Timezone in Type/dateTime.php not converted correctly
    *
    * @see http://pear.php.net/bugs/bug.php?id=10206
    */
    public function testBug10206()
    {
        $old = '2002-10-10T12:00:00+02:00';
        $dt  = new SOAP_Type_dateTime($old);
        $new = $dt->toString();
        //can only check for local timezone
        if (date('O') == '+0200') {
            $this->assertEquals($old, $new);
        }

        $dt  = new SOAP_Type_dateTime(time());
        $new = $dt->toString();
        $dt2 = new SOAP_Type_dateTime($new);
        $new2=$dt2->toString();
        $this->assertEquals($new, $new2);
        $this->assertEquals(13, strpos($new , ':'));
        $this->assertEquals(13, strpos($new2, ':'));
    }//public function testBug10206()



    public static function echoSoapVal($val, $indent = '')
    {
        echo $indent . $val->name . '(' . $val->type . '): ';
        if (is_array($val->value)) {
            echo gettype($val->value) . '(' . count($val->value) . '):' ;
        } else {
            echo gettype($val->value) . ':' ;
        }
        if (!is_array($val->value)) {
            echo $val->value . "\n";
        } else {
            echo "\n";
            foreach ($val->value as $sub) {
                self::echoSoapVal($sub, $indent . '  ');
            }
        }
    }//public static function echoSoapVal($val, $indent = '')

}

// Call SOAP_BugsTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "SOAP_BugsTest::main") {
    SOAP_BugsTest::main();
}
?>
