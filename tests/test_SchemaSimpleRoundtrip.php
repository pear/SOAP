<?php
 
require_once '../WSDL.php';
require_once '../Schema.php';
require_once 'PHPUnit/Framework/TestCase.php';

class test_SchemaSimpleRoundtrip extends PHPUnit_Framework_TestCase {

    public function __construct($name = 'test_Schema') {
        parent::__construct($name);
        PHPUnit_Framework_Assert::setLooselyTyped(true);
    }

    private function assertXMLRoundtripEquals($type, $value, $expected = NULL) {
        if (!$expected) $expected = $value;

        $doc = new DOMDocument;
        $node = SchemaSimple::domSerialize($doc,'test',$value,true);
        $nodetype = $node->attributes['xsi:type']->nodeValue;
        $this->assertEquals($type, $nodetype);
        $val = SchemaSimple::domDeserialize($node);
        $this->assertEquals($expected, $val, .01);
    }

    public function test_Roundtrip_XSD_string() {
        $this->assertXMLRoundtripEquals('xsd:string','hello world');
    }
    
    public function test_Roundtrip_XSD_integer() {
        $this->assertXMLRoundtripEquals('xsd:int',12345);
    }

    public function test_Roundtrip_XSD_boolean_True() {
        $this->assertXMLRoundtripEquals('xsd:boolean',true);
    }

    public function test_Roundtrip_XSD_boolean_False() {
        $this->assertXMLRoundtripEquals('xsd:boolean',false);
    }

    public function test_Roundtrip_XSD_boolean_Numeric_True() {
        $this->assertXMLRoundtripEquals('xsd:boolean',1);
    }

    public function test_Roundtrip_XSD_boolean_Numeric_False() {
        $this->assertXMLRoundtripEquals('xsd:boolean',0);
    }

    public function test_Roundtrip_XSD_float() {
        $this->assertXMLRoundtripEquals('xsd:float',(float)123.123);
    }

    public function test_Roundtrip_XSD_double() {
        $this->assertXMLRoundtripEquals('xsd:double',(double)123.123);
    }

    public function test_Roundtrip_XSD_decimal() {
        $this->assertXMLRoundtripEquals('xsd:decimal',123.123);
    }

    public function test_Roundtrip_XSD_duration() {
        $this->assertXMLRoundtripEquals('xsd:duration','P1347Y');
    }

    public function test_Roundtrip_XSD_dateTime() {
        $this->assertXMLRoundtripEquals('xsd:dateTime','2000-01-01T12:00:00');
    }

    public function test_Roundtrip_XSD_time() {
        $this->assertXMLRoundtripEquals('xsd:time','13:20:00-05:00');
    }

    public function test_Roundtrip_XSD_date() {
        $this->assertXMLRoundtripEquals('xsd:date','1999-05-31');
    }

    public function test_Roundtrip_XSD_gYearMonth() {
        $this->assertXMLRoundtripEquals('xsd:gYearMonth','1999-05');
    }

    public function test_Roundtrip_XSD_gYear() {
        $this->assertXMLRoundtripEquals('xsd:gYear','1999');
    }

    public function test_Roundtrip_XSD_gMonthDay() {
        $this->assertXMLRoundtripEquals('xsd:gMonthDay','05-31');
    }

    public function test_Roundtrip_XSD_gDay() {
        $this->assertXMLRoundtripEquals('xsd:gDay','31');
    }

    public function test_Roundtrip_XSD_gMonth() {
        $this->assertXMLRoundtripEquals('xsd:gMonth','05');
    }

    public function test_Roundtrip_XSD_hexBinary() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_base64Binary() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_normalizedString() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_token() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_language() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_NMTOKEN() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_NMTOKENS() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_Name() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_NCName() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_ID() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_IDREF() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_IDREFS() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_ENTITY() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_ENTITIES() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_nonPositiveInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_negativeInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_long() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_short() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_byte() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_nonNegativeInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_unsignedLong() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_unsignedInt() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_unsignedShort() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_unsignedByte() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_positiveInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_anyType() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_anyURI() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Roundtrip_XSD_QName() {
        $this->assertFalse(true, 'Not Implemented');
    }
}

# Unit test runner

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$class = new Reflection_Class(new test_SchemaSimpleRoundtrip);

$suite = new PHPUnit_Framework_TestSuite($class);

$result = PHPUnit_TextUI_TestRunner::run($suite);

?>