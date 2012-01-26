--TEST--
Deserialize multi-reference value with nested references
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';

$msg = '<?xml version="1.0"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:NS2="http://soapinterop.org/xsd">
<NS1:echoNestedStructResponse xmlns:NS1="http://soapinterop.org/">
<return href="#1"/>
</NS1:echoNestedStructResponse>
<NS2:SOAPStructStruct id="1" xsi:type="NS2:SOAPStructStruct">
<varString xsi:type="xsd:string">
arg</varString>
<varInt xsi:type="xsd:int">
34</varInt>
<varFloat xsi:type="xsd:float">
325.325012207031</varFloat>
<varStruct href="#2"/>
</NS2:SOAPStructStruct>
<varStruct id="2" xsi:type="NS2:SOAPStruct">
<varString xsi:type="xsd:string">
arg</varString>
<varInt xsi:type="xsd:int">
34</varInt>
<varFloat xsi:type="xsd:float">
325.325012207031</varFloat>
</varStruct>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

$val = parseMessage($msg);
var_dump($val);

?>
--EXPECTF--
object(stdClass)%s1) {
  ["return"]=>
 %sobject(stdClass)%s4) {
    ["varString"]=>
    string(4) "
arg"
    ["varInt"]=>
    int(34)
    ["varFloat"]=>
    float(325.32501220703)
    ["varStruct"]=>
   %sobject(stdClass)%s3) {
      ["varString"]=>
      string(4) "
arg"
      ["varInt"]=>
      int(34)
      ["varFloat"]=>
      float(325.32501220703)
    }
  }
}
