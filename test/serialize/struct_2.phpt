--TEST--
Serialize untyped struct
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';
$soap_base = new SOAP_Base();

$v = new SOAP_Value(
    'inputStruct', 'Struct',
    array('age'          => 45,
          'height'       => 5.9,
          'displacement' => -450,
          'color'        => 'Blue'));
echo $v->serialize($soap_base);

?>
--EXPECT--
<inputStruct>
<age xsi:type="xsd:int">45</age>
<height xsi:type="xsd:float">5.9</height>
<displacement xsi:type="xsd:int">-450</displacement>
<color xsi:type="xsd:string">Blue</color></inputStruct>
