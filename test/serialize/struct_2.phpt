--TEST--
Serialize untyped struct
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';
require_once 'SOAP/Client.php';
$soap_client = new SOAP_Client('');

$p = array(
    new SOAP_Value(
        'inputStruct', 'Struct',
        array('age'          => 45,
              'height'       => 5.9,
              'displacement' => -450,
              'color'        => 'Blue')));
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
<displacement xsi:type="xsd:int">-450</displacement>
<color xsi:type="xsd:string">Blue</color></inputStruct></echoStruct>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
