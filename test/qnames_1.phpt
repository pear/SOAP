--TEST--
QNames : Standard QName
--FILE--
<?php
require_once 'SOAP/Base.php';
require_once dirname(__FILE__) . '/test.utility.php';

$qname = &new QName('ns:elementName');
$val = array('name' => $qname->name, 'ns' => $qname->ns);
var_dump($val);
?>
--EXPECT--
array(2) {
  ["name"]=>
  string(11) "elementName"
  ["ns"]=>
  string(2) "ns"
}