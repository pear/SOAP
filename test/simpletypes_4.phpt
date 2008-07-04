--TEST--
5.2 Simple Types : Deserialize a known SOAP_Value
--FILE--
<?php
require_once 'SOAP/Base.php';
require_once 'test.utility.php';
require_once 'SOAP/Fault.php';
$soap_base = new SOAP_Base();

$v = new SOAP_Value('inputStruct', 'Struct', array(
         new SOAP_Value('age', 'int', 45),
         new SOAP_Value('height', 'float', 5.9),
         new SOAP_Value('displacement', 'negativeInteger', -450),
         new SOAP_Value('color', 'string', 'Blue')
         ));

$v->serialize($soap_base);
$val = $soap_base->_decode($v);
var_dump($val);
?>
--EXPECTF--
object(stdClass)%s4) {
  ["age"]=>
 %sint(45)
  ["height"]=>
 %sfloat(5.9)
  ["displacement"]=>
 %sint(-450)
  ["color"]=>
 %sstring(4) "Blue"
}