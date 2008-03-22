--TEST--
QNames : URN QName
--FILE--
<?php
require_once 'SOAP/Base.php';
require_once dirname(__FILE__) . '/test.utility.php';

$qname = &new QName('urn:some:api:bar');
$val = array('name' => $qname->name, 'ns' => $qname->ns);
var_dump($val);
?>
--EXPECT--
array(2) {
  ["name"]=>
  string(16) "urn:some:api:bar"
  ["ns"]=>
  string(0) ""
}