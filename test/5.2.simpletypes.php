<?php
require_once("SOAP/test/test.utility.php");
require_once("SOAP/Fault.php");
$prefix = "5.2 Simple Types";

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

$expect = array('age'=>45, 'height'=> 5.9, 'displacement' => -450, 'color' => 'Blue');
$val = parseMessage($msg);
if (array_compare($expect, $val)) {
    print "$prefix Deserialize Message OK\n";
} else {
    print "$prefix Deserialize Message FAILED extected $expect, got $val\n";
}

# parse null value
$msg = '<?xml version="1.0" encoding="UTF-8"?>

<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
<SOAP-ENV:Body>
<ns1:echoVoidResponse xmlns:ns1="http://soapinterop.org/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
</ns1:echoVoidResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
$val = parseMessage($msg);
if ($val == NULL) {
    print "$prefix Deserialize Message with NULL value OK\n";
} else {
    print "$prefix Deserialize Message with NULL value FAILED\n";
}

# serialize a soap value
$expect = '<inputStruct>
<age xsi:type="xsd:int">45</age>
<height xsi:type="xsd:float">5.9</height>
<displacement xsi:type="xsd:negativeInteger">-450</displacement>
<color xsi:type="xsd:string">Blue</color>
</inputStruct>
';
$v =  new SOAP_Value("inputStruct","Struct",array(
        new SOAP_Value("age","int",45),
        new SOAP_Value("height","float",5.9),
        new SOAP_Value("displacement","negativeInteger",-450),
        new SOAP_Value("color","string","Blue")
        ));
$val = $v->serialize();
if (string_compare(str_replace("\r\n","",$expect), str_replace("\r\n","",$val))) {
    print "$prefix Serialize Type OK\n";
} else {
    print "$prefix Serialize Type FAILED\n[$val]\n[$expect]\n";
}

# deserialize a soap value
$expect = array('age'=>45, 'height'=> 5.9, 'displacement' => -450, 'color' => 'Blue');
$val = $v->decode();
if (string_compare($expect, $val)) {
    print "$prefix Deserialize known SOAP_Value OK\n";
} else {
    print "$prefix Deserialize known SOAP_Value FAILED\n";
}

# serialize a soap value with unknown type
$expect = '<inputString>
<age xsi:type="xsd:int">45</age>
<height xsi:type="xsd:float">5.9</height>
<displacement xsi:type="xsd:int">-450</displacement>
<color xsi:type="xsd:string">Blue</color>
</inputString>
';
$v =  new SOAP_Value("inputString","Struct",array('age'=>45, 'height'=> 5.9, 'displacement' => -450, 'color' => 'Blue'));
$val = $v->serialize();
if (string_compare(str_replace("\r\n","",$expect), str_replace("\r\n","",$val))) {
    print "$prefix Serialize Unknown Type OK\n";
} else {
    print "$prefix Serialize Unknown Type FAILED\n[$val]\n[$expect]\n";
}

# serialize a soap value with unknown type
$expect = array('age'=>45, 'height'=> 5.9, 'displacement' => -450, 'color' => 'Blue');
$val = $v->decode();
if (string_compare($expect, $val)) {
    print "$prefix Deserialize Unknown SOAP_Value OK\n";
} else {
    print "$prefix Deserialize Unknown SOAP_Value FAILED\n";
}
?>