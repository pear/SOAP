--TEST--
WSDL test
--FILE--
<?php

require_once 'SOAP/WSDL.php';
$wsdl = new SOAP_WSDL('file://' . dirname(__FILE__) . '/example.wsdl');
var_export($wsdl->service);
echo "\n";
var_export(array_keys($wsdl->messages));

?>
--EXPECT--
'ServerExampleService'
array (
  0 => 'echoStructAsSimpleTypesRequest',
  1 => 'echoStructAsSimpleTypesResponse',
  2 => 'echoStringSimpleRequest',
  3 => 'echoStringSimpleResponse',
  4 => 'echoStringRequest',
  5 => 'echoStringResponse',
  6 => 'divideRequest',
  7 => 'divideResponse',
  8 => 'echoStructRequest',
  9 => 'echoStructResponse',
  10 => 'echoMimeAttachmentRequest',
  11 => 'echoMimeAttachmentResponse',
)