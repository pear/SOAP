--TEST--
Bug #1927: Missing namespace in SOAP_Fault response
--SKIPIF--
<?php if (version_compare(zend_version(), 2, '>=')) echo 'skip PHP 4 only'; ?>
--FILE--
<?php

require_once 'SOAP/Value.php';
require_once 'SOAP/Fault.php';

$backtrace =& PEAR::getStaticProperty('SOAP_Fault', 'backtrace');
$backtrace = true;
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
<detail xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="SOAP-ENC:Struct[2]">
<item>
<file xsi:type="xsd:string">%s/SOAP/Fault.php</file>
<line xsi:type="xsd:int">64</line>
<function xsi:type="xsd:string">pear_error</function>
<class xsi:type="xsd:string">pear_error</class>
<type xsi:type="xsd:string">::</type>
<args xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:anyType[5]">
<item xsi:type="xsd:string">unknown error</item>
<item xsi:type="xsd:string">Client</item>
<item xsi:nil="true"/>
<item xsi:nil="true"/>
<item xsi:nil="true"/></args></item>
<item>
<file xsi:type="xsd:string">%s/test/bug1927.php</file>
<line xsi:type="xsd:int">%d</line>
<function xsi:type="xsd:string">soap_fault</function>
<class xsi:type="xsd:string">soap_fault</class>
<type xsi:type="xsd:string">-&gt;</type>
<args xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:anyType[0]" xsi:nil="true"/></item></detail></SOAP-ENV:Fault>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
