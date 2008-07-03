--TEST--
5.2 Simple Types : Serialize a SOAP_Value with unknown type
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

$v = new SOAP_Value('inputString', 'Struct', $params);
$val = $v->serialize($soap_base);
var_dump($val);
?>
--EXPECT--
string(203) "
<inputString>
<age xsi:type="xsd:int">45</age>
<height xsi:type="xsd:float">5.9</height>
<displacement xsi:type="xsd:int">-450</displacement>
<color xsi:type="xsd:string">Blue</color></inputString>"