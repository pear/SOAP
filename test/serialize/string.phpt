--TEST--
Serialize string
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';
$soap_base = new SOAP_Base();

$v = new SOAP_Value('inputString', 'string', 'hello world');
echo $v->serialize($soap_base);
echo "\n";

$v = new SOAP_Value('inputString', '', 'hello world');
echo $v->serialize($soap_base);

?>
--EXPECT--
<inputString xsi:type="xsd:string">hello world</inputString>

<inputString xsi:type="xsd:string">hello world</inputString>
