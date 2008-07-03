--TEST--
5.2 Simple Types : Parse a deserialized Message with NULL value
--FILE--
<?php
require_once 'SOAP/Base.php';
require_once 'test.utility.php';
require_once 'SOAP/Fault.php';
$soap_base = new SOAP_Base();

// parse null value
$msg = '<?xml version="1.0" encoding="UTF-8"?>

<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
<SOAP-ENV:Body>
<ns1:echoVoidResponse xmlns:ns1="http://soapinterop.org/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
</ns1:echoVoidResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
$val = parseMessage($msg);
if ($val === NULL) {
    echo 'OK';
} else {
    var_dump($val);
}
?>
--EXPECT--
OK