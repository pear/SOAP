--TEST--
Bug #12099: Client of .Net SOAP server is missing first two rows of data?
Bug #12615: Decoded arrays missing some values
Bug #12774: I am Missing Response Information.
--FILE--
<?php
require_once 'SOAP/Parser.php';

// Special cases for no string and a single string
$stringarray = array();
$decoded = parseXml(getXml($stringarray));
printassert (($decoded == ''), '0');

$stringarray[] = 'a1';
$decoded = parseXml(getXml($stringarray));
// I would expect a string in this case, since the parser
// can't distinguish between a string and an array containing
// a single string.

// Actually an object with string property is returned.

printassert (is_object($decoded) && ($decoded->string == 'a1'), '1');


for ($i = 2; $i < 6; $i++) {
    $stringarray[] = 'a' . $i;
    $xml = getXml($stringarray);
    //echo $xml . "\n\n";
    $decoded = parseXml($xml);
    //print_r($decoded);
    printassert (($decoded == $stringarray), $i);
}


// Helper functions

/**
 * If flag is true, echo OK - otherwise echo Error
 *
 * @param bool $flag
 * @param string $message
 * @return void
 */
function printassert($flag, $message)
{
    if ($flag) {
        echo "OK $message.\n";
    } else {
        echo "Error $message.\n";
    }
}

/**
 * Parse the XML string using SOAP_Parser.
 *
 * @param string $xml The xml string to be parsed
 * @return mixed Parsed result for the xml string.
 */
function parseXml($xml)
{
    $response = new SOAP_Parser($xml, 'UTF-8', $v = null);

    // This still looks normal.
    $return = $response->getResponse();
    // print_r($return);

    // This loses the first two items for $i > 3 and
    // has an unexpected key 'string' for $i == 3.
    $decoded = $response->_decode($return);
    //print_r($decoded);

    return $decoded->return;
}

/**
 * Generate a simple SOAP response XML packing the $stringarray
 *
 * @param array $stringarray
 * @return string SOAP xml response
 */
function getXml($stringarray)
{
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope><soap:Body><trg:SomeResponse><return>';

    foreach($stringarray as $string) {
        $xml .= "\n	<string>$string</string>";
    }

    $xml .= '</return></trg:SomeResponse></soap:Body></soap:Envelope>';
    return $xml;
}

?>
--EXPECT--
OK 0.
OK 1.
OK 2.
OK 3.
OK 4.
OK 5.
