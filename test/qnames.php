<?php

require_once 'SOAP/Base.php';
require_once 'test.utility.php';

$prefix = 'QNames';

$expect = array('name' => 'elementName', 'ns' => 'ns');
$qname = &new QName('ns:elementName');
$val = array('name' => $qname->name, 'ns' => $qname->ns);
if (array_compare($expect, $val)) {
    print "$prefix Standard QName OK\n";
} else {
    print "$prefix Standard QName FAILED\n";
}


$expect = array('name' => 'urn:some:api:bar', 'ns' => '');
$qname = &new QName('urn:some:api:bar');
$val = array('name' => $qname->name, 'ns' => $qname->ns);
if (array_compare($expect, $val)) {
    print "$prefix URN QName OK\n";
} else {
    print "$prefix URN QName FAILED\n";
}
