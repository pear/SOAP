--TEST--
Deserialize untyped struct
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';

$msg = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<SOAP-ENV:Body>
<m:test xmlns:m="http://soapinterop.org/">
<age>45</age>
<height>5.9</height>
<displacement>-450</displacement>
<color>Blue</color>
</m:test>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

$val = parseMessage($msg);
var_dump($val);

?>
--EXPECTF--
object(stdClass)%s4) {
  ["age"]=>
  string(2) "45"
  ["height"]=>
  string(3) "5.9"
  ["displacement"]=>
  string(4) "-450"
  ["color"]=>
  string(4) "Blue"
}
