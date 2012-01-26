--TEST--
Bad array assignment when using WSDL client (Bug #16968).
--FILE--
<?php

require_once 'SOAP/WSDL.php';
$wsdl = new SOAP_WSDL('file://' . dirname(__FILE__) . '/bug16968.wsdl');
$wsdl->getOperationData('WeatherAttachment', 'getDayForecastImage');

?>
--EXPECT--
