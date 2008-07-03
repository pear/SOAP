--TEST--
5.2 Simple Types : Serialize a SOAP_Value
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

$val = $v->serialize($soap_base);
var_dump($val);
?>
--EXPECT--
string(215) "
<inputStruct>
<age xsi:type="xsd:int">45</age>
<height xsi:type="xsd:float">5.9</height>
<displacement xsi:type="xsd:negativeInteger">-450</displacement>
<color xsi:type="xsd:string">Blue</color></inputStruct>"