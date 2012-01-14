--TEST--
SOAP_Type_dateTime test
--FILE--
<?php

require 'SOAP/Type/dateTime.php';

$orig = '2001-04-25T09:31:41-0700';
$dt = new SOAP_Type_dateTime();
$utc = $dt->toUTC($orig);
$ts1 = $dt->toUnixtime($orig);
$ts2 = $dt->toUnixtime($utc);
$b1 = $dt->toUTC(988216301);

echo "$utc\n$ts1\n$ts2\n$b1";

?>
--EXPECT--
2001-04-25T16:31:41Z
988216301
988216301
2001-04-25T16:31:41Z
