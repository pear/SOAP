--TEST--
Bug 11013 - SOAP crashes php with SEGV
--FILE--
<?php
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$GLOBALS['HTTP_RAW_POST_DATA'] = array(array('Helgi'));

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