--TEST--
Serialize typed string (document)
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';
require_once 'SOAP/Client.php';
$soap_client = new SOAP_Client('');
$soap_client->setStyle('document');

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
<inputString xsi:type="xsd:string">hello world</inputString>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
