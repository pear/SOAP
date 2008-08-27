--TEST--
QNames: Standard QName
--FILE--
<?php
require_once 'SOAP/Base.php';
require_once dirname(__FILE__) . '/test.utility.php';

$qname = new QName('ns:elementName');
var_dump($qname->name, $qname->prefix);
?>
--EXPECT--
string(11) "elementName"
string(2) "ns"
