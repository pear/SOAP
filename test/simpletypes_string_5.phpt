--TEST--
5.2.1 Simple Types String : Deserialize Unknown SOAP_Value
--FILE--
<?php
require_once dirname(__FILE__) . '/test.utility.php';
require_once 'SOAP/Base.php';
$soap_base = new SOAP_Base();

$v = new SOAP_Value("inputString","","hello world");
$val = $soap_base->_decode($v);
var_dump($val);
?>
--EXPECT--
string(11) "hello world"