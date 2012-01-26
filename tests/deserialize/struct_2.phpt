--TEST--
Deserialize typed struct (with int)
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';

$msg = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<SOAP-ENV:Body>
<m:test xmlns:m="http://soapinterop.org/">
<age xsi:type="xsd:int">45</age>
<height xsi:type="xsd:float">5.9</height>
<displacement xsi:type="xsd:int">-450</displacement>
<color xsi:type="xsd:string">Blue</color>
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
 %sint(45)
  ["height"]=>
 %sfloat(5.9)
  ["displacement"]=>
 %sint(-450)
  ["color"]=>
 %sstring(4) "Blue"
}
