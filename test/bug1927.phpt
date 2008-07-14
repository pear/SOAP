--TEST--
Bug #1927: Missing namespace in SOAP_Fault response
--FILE--
<?php

require_once 'SOAP/Value.php';
require_once 'SOAP/Fault.php';

$fault = new SOAP_Fault();
echo $fault->message();

?>
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope
 xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>
<SOAP-ENV:Fault>
<faultcode xsi:type="xsd:QName">SOAP-ENV:Client</faultcode>
<faultstring xsi:type="xsd:string">unknown error</faultstring>
<faultactor xsi:type="xsd:anyURI"></faultactor>
<detail xsi:type="xsd:string"></detail></SOAP-ENV:Fault>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
