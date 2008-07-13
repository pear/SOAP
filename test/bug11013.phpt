--TEST--
Bug #11013: Endless loop with empty service provider
--FILE--
<?php

$_SERVER['SERVER_NAME'] = 'localhost';
$GLOBALS['HTTP_RAW_POST_DATA'] = 'Helgi';

// Make parser handle array requests and just simply only accept strings
// just handle it gracefully

require_once 'SOAP/Server.php';
$soap = new SOAP_Server();
$service = new Service();

$soap->addObjectMap($service,'urn:soapservice');
$soap->service($GLOBALS['HTTP_RAW_POST_DATA']);

class Service
{
}

echo 'OK';

?>
--EXPECT--
OK
