<?php
 
require_once '../WSDL.php';
require_once '../Schema.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'InteropTypes.php';

class test_Schema extends PHPUnit_Framework_TestCase {

    public function __construct($name = 'test_Schema') {
        parent::__construct($name);
    }

    public function test_Serialize_ArrayEncoded() {
        $doc = new DOMDocument;
        $node = SchemaSimple::domSerialize($doc,'test',NULL,array('hello world',1234),true);
        $doc->appendChild($node);
        $xml = $doc->saveXML();
        $xml = preg_replace('/\n|\r/','',$xml);
        $this->assertEquals('<?xml version="1.0"?><test soap-enc:arrayType="xsd:anyType[2]" soap-enc:offset="[0]" xsi:type="soap-enc:Array"><item xsi:type="xsd:string">hello world</item><item xsi:type="xsd:int">1234</item></test>',$xml);
    }
    
    public function test_Serialize_ArrayOfIntEncoded() {
        $doc = new DOMDocument;
        $node = SchemaSimple::domSerialize($doc,'test',NULL,array(1234,1234),true);
        $doc->appendChild($node);
        $xml = $doc->saveXML();
        $xml = preg_replace('/\n|\r/','',$xml);
        $this->assertEquals('<?xml version="1.0"?><test soap-enc:arrayType="xsd:int[2]" soap-enc:offset="[0]" xsi:type="soap-enc:Array"><item xsi:type="xsd:int">1234</item><item xsi:type="xsd:int">1234</item></test>',$xml);
    }

    public function test_Serialize_MapEncoded() {
        $doc = new DOMDocument;
        $node = SchemaSimple::domSerialize($doc,'test',NULL,array('varstring'=>'hello world','varint'=>1234,'varfloat'=>1234), true);
        $doc->appendChild($node);
        $xml = $doc->saveXML();
        $xml = preg_replace('/\n|\r/','',$xml);
        $this->assertEquals('<?xml version="1.0"?><test xsi:type="apachens:Map"><item><key>varstring</key><value xsi:type="xsd:string">hello world</value></item><item><key>varint</key><value xsi:type="xsd:int">1234</value></item><item><key>varfloat</key><value xsi:type="xsd:int">1234</value></item></test>',$xml);
    }

    public function test_Serialize_StructEncoded() {
        $doc = new DOMDocument;
        $node = SchemaSimple::domSerialize($doc,'{http://soapinterop.org/xsd}test',NULL,new SOAPStruct,true);
        $doc->appendChild($node);
        $xml = $doc->saveXML();
        $xml = preg_replace('/\n|\r/','',$xml);
        $this->assertEquals('<?xml version="1.0"?><test xmlns="http://soapinterop.org/xsd" xsi:type="xsd:Struct"><varInt xsi:type="xsd:int">123</varInt><varFloat xsi:type="xsd:float">123.123</varFloat><varString xsi:type="xsd:string">hello world</varString></test>',$xml);
    }

    /* XXX this currently fails as libxml schema validation doesn't
       use the default namespace from the element tag for the type
       attribute value if is is undefined.  lots of soap schema's
       appear that way, not sure who is right, but we have to go
       with what most soap implementations are doing. */
    /*
    public function testValidate() {
        // prep the wsdl which has our schema
        $wsdl = WSDLManager::get('InteropTest.wsdl');
        self::assertNotNull($wsdl);
        $schema = SchemaManager::get('http://soapinterop.org/xsd');
        self::assertNotNull($schema);
        $doc = new DOMDocument;
        $node = SchemaSimple::domSerialize($doc,'{http://soapinterop.org/xsd}test',NULL,new SOAPStruct,true);
        $doc->appendChild($node);
        $this->assertTrue($schema->validate($node));
    }
    */
}

# Unit test runner

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$class = new Reflection_Class(new test_Schema);

$suite = new PHPUnit_Framework_TestSuite($class);

$result = PHPUnit_TextUI_TestRunner::run($suite);

?>