--TEST--
Deserialize custom struct
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';
require_once 'PEAR/Config.php';
$config = &PEAR_Config::singleton();
require_once dirname(dirname(dirname(__FILE__))) . '/example/example_server.php';

$msg = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope
 xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 xmlns:ns4="urn:SOAP_Example_Server"
 SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>
<ns4:echoStruct>
<inputStruct>
<varString xsi:type="xsd:string">test string</varString>
<varInt xsi:type="xsd:int">123</varInt>
<varFloat xsi:type="xsd:float">123.123</varFloat></inputStruct></ns4:echoStruct>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

$val = parseMessage($msg);
var_dump($val);

?>
--EXPECTF--
object(stdClass)%s1) {
  ["inputStruct"]=>
 %sobject(stdClass)%s3) {
    ["varString"]=>
    string(11) "test string"
    ["varInt"]=>
    int(123)
    ["varFloat"]=>
    float(123.123)
  }
}
