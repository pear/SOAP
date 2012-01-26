--TEST--
Deserialize null
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';

$msg = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
<SOAP-ENV:Body>
<ns1:echoVoidResponse xmlns:ns1="http://soapinterop.org/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
</ns1:echoVoidResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

var_export(parseMessage($msg));

?>
--EXPECT--
NULL