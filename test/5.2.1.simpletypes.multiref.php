<?php
require_once("SOAP/test/test.utility.php");
$prefix = "5.2.1 Multi-Reference";

$msg = '<?xml version="1.0" encoding="UTF-8"?>

<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<SOAP-ENV:Body>
<m:test xmlns:m="http://soapinterop.org/">
<greeting id="String-0">Hello</greeting>
<salutation href="#String-0"/>
</m:test>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

$expect = array('greeting'=>'Hello');
$expect['salutation'] = &$expect['greeting'];
$val = parseMessage($msg);
if (array_compare($val, $expect)) {
    print "$prefix Backward Reference decode OK\n";
} else {
    print "$prefix Backward Reference decode FAILED\n";
}

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
if (array_compare($val, $expect)) {
    print "$prefix Forward Reference decode OK\n";
} else {
    print "$prefix Forward Reference decode FAILED\n";
}


$msg = "<soap:Envelope xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:soap='http://schemas.xmlsoap.org/soap/envelope/' xmlns:soapenc='http://schemas.xmlsoap.org/soap/encoding/' soap:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/' xmlns:ns5='http://soapinterop.org/xsd'>
<soap:Body>
<n:echoStructResponse xmlns:n='http://soapinterop.org/'>
<Result href='#id0'/>
</n:echoStructResponse>
<id0 id='id0' soapenc:root='0' xsi:type='ns5:SOAPStruct'>
<varString xsi:type='xsd:string'>
arg</varString>
<varInt href='#id1'/>
<varFloat href='#id2'/>
</id0>
<id1 id='id1' soapenc:root='0' xsi:type='xsd:int'>
34</id1>
<id2 id='id2' soapenc:root='0' xsi:type='xsd:float'>
325.325</id2>
</soap:Body>
</soap:Envelope>
";

$expect = array('varString' => 'arg', 'varInt' => 34, 'varFloat' => 325.325);
$val = parseMessage($msg);
if (array_compare($val, $expect)) {
    print "$prefix Forward Reference root decode OK\n";
} else {
    print "$prefix Forward Reference root decode FAILED\n";
}

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

$expect = array('varString' => 'arg', 'varInt' => 34, 'varFloat' => 325.325, 'varStruct' => array('varString' => 'arg', 'varInt' => 34, 'varFloat' => 325.325));
$val = parseMessage($msg);
if (array_compare($val, $expect)) {
    print "$prefix Forward Reference root decode OK\n";
} else {
    print "$prefix Forward Reference root decode FAILED\n";
    print_r($val);
}

?>