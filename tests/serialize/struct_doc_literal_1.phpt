--TEST--
Serialize typed struct (document/literal)
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';
require_once 'SOAP/Client.php';
$soap_client = new SOAP_Client('');
$soap_client->setStyle('document');
$soap_client->setUse('literal');

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
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>
<inputStruct>
<age>45</age>
<height>5.9</height>
<displacement>-450</displacement>
<color>Blue</color></inputStruct>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
