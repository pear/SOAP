<?php
 
require_once '../WSDL.php';
require_once '../Schema.php';
require_once 'PHPUnit/Framework/TestCase.php';

class test_SchemaSimpleSerialize extends PHPUnit_Framework_TestCase {

    public function __construct($name = 'test_Schema') {
        parent::__construct($name);
        PHPUnit_Framework_Assert::setLooselyTyped(true);
    }

    private function assertXMLSerializedEquals($type, $value, $expected = NULL) {
        if (!$expected) $expected = $value;
        
        $doc = new DOMDocument;
        $node = SchemaSimple::domSerialize($doc, 'test', null, $value,true);
        $doc->appendChild($node);
        $xml = $doc->saveXML();
        $xml = preg_replace('/\n|\r/','',$xml);
        $this->assertEquals('<?xml version="1.0"?><test xsi:type="'.$type.'">'.$expected.'</test>',$xml);
    }
    
    public function test_Serialize_XSD_string() {
        $this->assertXMLSerializedEquals('xsd:string','hello world');
    }
    
    public function test_Serialize_XSD_integer() {
        $this->assertXMLSerializedEquals('xsd:int',12345);
    }

    public function test_Serialize_XSD_boolean_True() {
        $this->assertXMLSerializedEquals('xsd:boolean',true,'true');
    }

    public function test_Serialize_XSD_boolean_False() {
        $this->assertXMLSerializedEquals('xsd:boolean',false,'false');
    }

    public function test_Serialize_XSD_boolean_Numeric_True() {
        $this->assertXMLSerializedEquals('xsd:boolean',1,'true');
    }

    public function test_Serialize_XSD_boolean_Numeric_False() {
        $this->assertXMLSerializedEquals('xsd:boolean',0,'false');
    }

    public function test_Serialize_XSD_float() {
        $this->assertXMLSerializedEquals('xsd:float',123.123);
    }

    public function test_Serialize_XSD_double() {
        $this->assertXMLSerializedEquals('xsd:double',123.123);
    }

    public function test_Serialize_XSD_decimal() {
        $this->assertXMLSerializedEquals('xsd:decimal',123.123);
    }

    public function test_Serialize_XSD_duration() {
        $this->assertXMLSerializedEquals('xsd:duration','P1347Y');
    }

    public function test_Serialize_XSD_dateTime() {
        $this->assertXMLSerializedEquals('xsd:dateTime','2000-01-01T12:00:00');
    }

    public function test_Serialize_XSD_time() {
        $this->assertXMLSerializedEquals('xsd:time','13:20:00-05:00');
    }

    public function test_Serialize_XSD_date() {
        $this->assertXMLSerializedEquals('xsd:date','1999-05-31');
    }

    public function test_Serialize_XSD_gYearMonth() {
        $this->assertXMLSerializedEquals('xsd:gYearMonth','1999-05');
    }

    public function test_Serialize_XSD_gYear() {
        $this->assertXMLSerializedEquals('xsd:gYear','1999');
    }

    public function test_Serialize_XSD_gMonthDay() {
        $this->assertXMLSerializedEquals('xsd:gMonthDay','05-31');
    }

    public function test_Serialize_XSD_gDay() {
        $this->assertXMLSerializedEquals('xsd:gDay','31');
    }

    public function test_Serialize_XSD_gMonth() {
        $this->assertXMLSerializedEquals('xsd:gMonth','05');
    }

    public function test_Serialize_XSD_hexBinary() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_base64Binary() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_normalizedString() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_token() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_language() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_NMTOKEN() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_NMTOKENS() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_Name() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_NCName() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_ID() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_IDREF() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_IDREFS() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_ENTITY() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_ENTITIES() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_nonPositiveInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_negativeInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_long() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_short() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_byte() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_nonNegativeInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_unsignedLong() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_unsignedInt() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_unsignedShort() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_unsignedByte() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_positiveInteger() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_anyType() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_anyURI() {
        $this->assertFalse(true, 'Not Implemented');
    }

    public function test_Serialize_XSD_QName() {
        $this->assertFalse(true, 'Not Implemented');
    }

}

# Unit test runner

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$class = new Reflection_Class(new test_SchemaSimpleSerialize);

$suite = new PHPUnit_Framework_TestSuite($class);

$result = PHPUnit_TextUI_TestRunner::run($suite);

?>