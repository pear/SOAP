--TEST--
Bug #4523: Parse dates without timezone
--FILE--
<?php

require 'SOAP/Type/dateTime.php';

$orig = '2001-04-25T09:31:41';
$dt = new SOAP_Type_dateTime();
$ts = $dt->toUnixtime($orig);
$d  = $dt->toString($ts);

$zone = date('O', $ts);
$zone = substr($zone, 0, 3) . ':' . substr($zone, 3);
var_dump($d == $orig . $zone);
?>
--EXPECT--
bool(true)
