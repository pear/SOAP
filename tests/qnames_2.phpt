--TEST--
QNames: URN QName
--FILE--
<?php
require_once 'SOAP/Base.php';
require_once dirname(__FILE__) . '/test.utility.php';

$qname = new QName('urn:some:api:bar');
var_dump($qname->name, $qname->prefix);
?>
--EXPECT--
string(16) "urn:some:api:bar"
string(0) ""
