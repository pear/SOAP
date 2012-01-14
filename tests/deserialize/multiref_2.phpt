--TEST--
Deserialize multi-reference value with forward reference
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';

$msg = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<SOAP-ENV:Body>
<m:test xmlns:m="http://soapinterop.org/">
<salutation href="#String-0"/>
<greeting id="String-0">Hello</greeting>
</m:test>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

$val = parseMessage($msg);
var_dump($val);

?>
--EXPECTF--
object(stdClass)%s2) {
  ["salutation"]=>
  string(5) "Hello"
  ["greeting"]=>
  string(5) "Hello"
}
