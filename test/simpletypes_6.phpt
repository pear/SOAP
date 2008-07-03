--TEST--
5.2 Simple Types : Deserialize a unknown SOAP_Value
--FILE--
<?php
require_once 'SOAP/Base.php';
require_once 'test.utility.php';
require_once 'SOAP/Fault.php';
$soap_base = new SOAP_Base();

$params = array(
    'age'          => 45,
    'height'       => 5.9,
    'displacement' => -450,
    'color'        => 'Blue'
);

$v   = new SOAP_Value('inputString', 'Struct', $params);
$val = $v->serialize($soap_base);
$val = $soap_base->_decode($v);
var_dump($val);
?>
--EXPECT--
object(stdClass)#4 (4) {
  ["age"]=>
  string(2) "45"
  ["height"]=>
  string(3) "5.9"
  ["displacement"]=>
  string(4) "-450"
  ["color"]=>
  string(4) "Blue"
}