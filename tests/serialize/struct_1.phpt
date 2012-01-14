--TEST--
Serialize typed struct
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';
require_once 'SOAP/Client.php';
$soap_client = new SOAP_Client('');

$p = array(
    new SOAP_Value(
        'inputStruct', 'Struct',
        array(new SOAP_Value('age', 'int', 45),
              new SOAP_Value('height', 'float', 5.9),
              new SOAP_Value('displacement', 'negativeInteger', -450),
              new SOAP_Value('color', 'string', 'Blue'))));
echo $soap_client->_generate('echoStruct', $p);

?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope
 xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>
<echoStruct>
<inputStruct>
<age xsi:type="xsd:int">45</age>
<height xsi:type="xsd:float">5.9</height>
<displacement xsi:type="xsd:negativeInteger">-450</displacement>
<color xsi:type="xsd:string">Blue</color></inputStruct></echoStruct>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
