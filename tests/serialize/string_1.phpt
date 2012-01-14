--TEST--
Serialize typed string
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';
require_once 'SOAP/Client.php';
$soap_client = new SOAP_Client('');

$p = array(new SOAP_Value('inputString', 'string', 'hello world'));
echo $soap_client->_generate('echoString', $p);

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
<echoString>
<inputString xsi:type="xsd:string">hello world</inputString></echoString>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
