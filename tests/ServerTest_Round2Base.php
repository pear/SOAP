<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'InteropTypes.php';

class test_ServerTest_Round2Base extends PHPUnit_Framework_TestCase {
    private $methodNamespace = 'http://soapinterop.org/';

    private function generateClientRequest($method, $args)
    {
        $request = SOAP_Envelope::request($this->wsdl);
        $qm = new QName($method);
        $request->addMethod($qm->name, $args);
        return $request;
    }
    
    private function generateServerResponse($result) {
        return $result->deserializeBody();
    }
    
    private function localCall($method, $argarray) {
        $rq_env = $this->generateClientRequest("{{$this->methodNamespace}}$method",$argarray);
        $rq_env->normalizeDocument();
        #print $rq_env->saveXML();
        $rq_env = SOAP_Envelope::parse($rq_env->saveXML());
        $rs_env = $this->server->handleRequest($rq_env);
        $rs_env->normalizeDocument();
        $rs_env = SOAP_Envelope::parse($rs_env->saveXML());
        return $this->generateServerResponse($rs_env);
    }
    
    public function testEchoString() {
        $in = 'Hello World';
        $out = $this->localCall('echoString',
                            array('inputString'=>$in));
        $this->assertEquals($in, $out);
    }

    public function testEchoStringArray() {
        $in = array('Hello World','Hello Dolly','Good Afternoon Santa Clara');
        $out = $this->localCall('echoStringArray',
                            array('inputStringArray'=>$in));
        $this->assertEquals($in, $out);
    }

    public function testEchoInteger() {
        $in = 2345;
        $out = $this->localCall('echoInteger',
                            array('inputInteger'=>$in));
        $this->assertEquals($in, $out);
    }

    public function testEchoIntegerArray() {
        $in = array(1234,1,2);
        $out = $this->localCall('echoIntegerArray',
                            array('inputIntegerArray'=>$in));
        $this->assertEquals($in, $out);
    }

    public function testEchoFloat() {
        $in = (float)23.45;
        $out = $this->localCall('echoFloat',
                            array('inputFloat'=>$in));
        $this->assertEquals($in, $out);
    }

    public function testEchoFloatArray() {
        $in = array((float)12.34,(float)1.2,(float).23);
        $out = $this->localCall('echoFloatArray',
                            array('inputFloatArray'=>$in));
        $this->assertEquals($in, $out, .01);
    }

    public function testEchoStruct() {
        $in = new SOAPStruct;
        $out = $this->localCall('echoStruct',
                            array('inputStruct'=>$in));
        $this->assertEquals($in, $out, .01);
    }

    public function testEchoStructArray() {
        $in = array(new SOAPStruct,
                    new SOAPStruct,
                    new SOAPStruct);
        $out = $this->localCall('echoStructArray',
                            array('inputStructArray'=>$in));
        $this->assertEquals($in, $out, .01);
    }

    public function testEchoBase64() {
        $in = 'not implemented yet';
        $out = $this->localCall('echoBase64',
                            array('inputBase64'=>$in));
        $this->assertEquals($in, $out);
    }

    public function testEchoDate() {
        $in = 'not implemented yet';
        $out = $this->localCall('echoDate',
                            array('inputDate'=>$in));
        $this->assertEquals($in, $out);
    }

    public function testEchoHexBinary() {
        $in = 'not implemented yet';
        $out = $this->localCall('echoHexBinary',
                            array('inputHexBinary'=>$in));
        $this->assertEquals($in, $out);
    }

    public function testDecimal() {
        $in = (double)23.45;
        $out = $this->localCall('echoDecimal',
                            array('inputDecimal'=>$in));
        $this->assertEquals($in, $out);
    }

    public function testEchoBoolean() {
        $in = false;
        $out = $this->localCall('echoBoolean',
                            array('inputBoolean'=>$in));
        $this->assertEquals($in, $out);
    }

}

?>