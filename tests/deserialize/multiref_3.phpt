--TEST--
Deserialize multi-reference value with outside reference
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';

$msg = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" soap:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns5="http://soapinterop.org/xsd">
<soap:Body>
<n:echoStructResponse xmlns:n="http://soapinterop.org/">
<Result href="#id0"/>
</n:echoStructResponse>
<id0 id="id0" soapenc:root="0" xsi:type="ns5:SOAPStruct">
<varString xsi:type="xsd:string">
arg</varString>
<varInt href="#id1"/>
<varFloat href="#id2"/>
</id0>
<id1 id="id1" soapenc:root="0" xsi:type="xsd:int">
34</id1>
<id2 id="id2" soapenc:root="0" xsi:type="xsd:float">
325.325</id2>
</soap:Body>
</soap:Envelope>
';

$val = parseMessage($msg);
var_dump($val);

?>
--EXPECTF--
object(stdClass)%s1) {
  ["Result"]=>
 %sobject(stdClass)%s3) {
    ["varString"]=>
    string(4) "
arg"
    ["varInt"]=>
    int(34)
    ["varFloat"]=>
    float(325.325)
  }
}
