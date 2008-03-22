--TEST--
5.2.1 Simple Types String : Serialize Type
--FILE--
<?php
require_once dirname(__FILE__) . '/test.utility.php';
require_once 'SOAP/Base.php';
$soap_base = new SOAP_Base();

$v = new SOAP_Value("inputString","string","hello world");
$val = $v->serialize($soap_base);
var_dump($val);
?>
--EXPECT--
string(62) "
<inputString xsi:type="xsd:string">hello world</inputString>"