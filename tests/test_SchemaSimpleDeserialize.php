<?php
 
require_once '../WSDL.php';
require_once '../Schema.php';
require_once 'PHPUnit/Framework/TestCase.php';

class test_SchemaSimpleDeserialize extends PHPUnit_Framework_TestCase {

    public function __construct($name = 'test_Schema') {
        parent::__construct($name);
        PHPUnit_Framework_Assert::setLooselyTyped(true);
    }


    private function assertXMLDeserializedEquals($type, $value, $expected = NULL) {
        if (!$expected) $expected = $value;
        
        $xml = '<?xml version="1.0"?><e xsi:type="'.$type.'">'.$value.'</e>';
        $doc = new DOMDocument;
        $doc->loadXML($xml);
        $val = SchemaSimple::domDeserialize($doc->documentElement);
        $this->assertEquals($expected, $val, .01);
    }
    
    public function test_Deserialize_XSD_string() {
        $this->assertXMLDeserializedEquals("xsd:string",'Hello World');
    }

    public function test_Deserialize_XSD_integer() {
        $this->assertXMLDeserializedEquals("xsd:int",12345);
    }

    public function test_Deserialize_XSD_boolean_True() {
        $this->assertXMLDeserializedEquals("xsd:boolean",'true',true);
    }

    public function test_Deserialize_XSD_boolean_False() {
        $this->assertXMLDeserializedEquals("xsd:boolean",false);
    }

    public function test_Deserialize_XSD_boolean_Numeric_True() {
        $this->assertXMLDeserializedEquals("xsd:boolean",1);
    }

    public function test_Deserialize_XSD_boolean_Numeric_False() {
        $this->assertXMLDeserializedEquals("xsd:boolean",0);
    }

    public function test_Deserialize_XSD_float() {
        $this->assertXMLDeserializedEquals("xsd:float",123.123);
    }

    public function test_Deserialize_XSD_double() {
        $this->assertXMLDeserializedEquals("xsd:double",123.123);
    }

    public function test_Deserialize_XSD_decimal() {
        $this->assertXMLDeserializedEquals("xsd:decimal",123.123);
    }

    public function test_Deserialize_XSD_duration() {
        $this->assertXMLDeserializedEquals("xsd:duration",'P1347Y');
    }

    public function test_Deserialize_XSD_dateTime() {
        $this->assertXMLDeserializedEquals("xsd:dateTime",'2000-01-01T12:00:00');
    }

    public function test_Deserialize_XSD_time() {
        $this->assertXMLDeserializedEquals("xsd:time",'13:20:00-05:00');
    }

    public function test_Deserialize_XSD_date() {
        $this->assertXMLDeserializedEquals("xsd:date",'1999-05-31');
    }

    public function test_Deserialize_XSD_gYearMonth() {
        $this->assertXMLDeserializedEquals("xsd:gYearMonth",'1999-05');
    }

    public function test_Deserialize_XSD_gYear() {
        $this->assertXMLDeserializedEquals("xsd:gYear",'1999');
    }

    public function test_Deserialize_XSD_gMonthDay() {
        $this->assertXMLDeserializedEquals("xsd:gMonthDay",'05-31');
    }

    public function test_Deserialize_XSD_gDay() {
        $this->assertXMLDeserializedEquals("xsd:gDay",'31');
    }

    public function test_Deserialize_XSD_gMonth() {
        $this->assertXMLDeserializedEquals("xsd:gMonth",'05');
    }

    public function test_Deserialize_XSD_hexBinary() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_base64Binary() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_normalizedString() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_token() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_language() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_NMTOKEN() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_NMTOKENS() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_Name() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_NCName() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_ID() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_IDREF() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_IDREFS() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_ENTITY() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_ENTITIES() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_nonPositiveInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_negativeInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_long() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_short() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_byte() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_nonNegativeInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_unsignedLong() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_unsignedInt() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_unsignedShort() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_unsignedByte() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_positiveInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_anyType() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_anyURI() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Deserialize_XSD_QName() {
        $this->assertFalse(true, 'Not Implemented');
    }
}

# Unit test runner

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$class = new Reflection_Class(new test_SchemaSimpleDeserialize);

$suite = new PHPUnit_Framework_TestSuite($class);

$result = PHPUnit_TextUI_TestRunner::run($suite);

?>