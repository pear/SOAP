<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'InteropTypes.php';

class test_Client_Calls extends PHPUnit_Framework_TestCase {
    public function testEchoString() {
        $in = 'Hello World';
        $out = $this->client->echoString($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

    public function testEchoStringArray() {
        $in = array('Hello World','Hello Dolly','Good Morning Santa Clara');
        $out = $this->client->echoStringArray($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

    public function testEchoInteger() {
        $in = 2345;
        $out = $this->client->echoInteger($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

    public function testEchoIntegerArray() {
        $in = array(1234,1,2);
        $out = $this->client->echoIntegerArray($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

    public function testEchoFloat() {
        $in = (float)23.45;
        $out = $this->client->echoFloat($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

    public function testEchoFloatArray() {
        $in = array((float)12.34,(float)1.2,(float).23);
        $out = $this->client->echoFloatArray($in)->deserializeBody();
        $this->assertEquals($in, $out, .01);
    }

    public function testEchoStruct() {
        $in = new SOAPStruct;
        $out = $this->client->echoStruct($in)->deserializeBody();
        $this->assertEquals($in, $out, .01);
    }

    public function testEchoStructArray() {
        $in = array(new SOAPStruct,
                    new SOAPStruct,
                    new SOAPStruct);
        $out = $this->client->echoStructArray($in)->deserializeBody();
        $this->assertEquals($in, $out, .01);
    }

    public function testEchoBase64() {
        $in = 'not implemented yet';
        $out = $this->client->echoBase64($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

    public function testEchoDate() {
        $in = 'not implemented yet';
        $out = $this->client->echoDate($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

    public function testEchoHexBinary() {
        $in = 'not implemented yet';
        $out = $this->client->echoHexBinary($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

    public function testDecimal() {
        $in = (double)23.45;
        $out = $this->client->echoDecimal($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

    public function testEchoBoolean() {
        $in = false;
        $out = $this->client->echoBoolean($in)->deserializeBody();
        $this->assertEquals($in, $out);
    }

}

?>