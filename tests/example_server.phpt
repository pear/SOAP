--TEST--
Example server test (PHP 4)
--SKIPIF--
<?php if (version_compare(zend_version(), 2, '>=')) echo 'skip PHP 4 only'; ?>
--FILE--
<?php

/* Load files. */
require_once 'SOAP/Client.php';
require_once 'SOAP/Server.php';
require_once 'PEAR/Config.php';
$config = &PEAR_Config::singleton();
require_once dirname(dirname(__FILE__)) . '/example/example_server.php';

/* Create example server. */
$_SERVER['SERVER_NAME'] = null;
$server = new SOAP_Server;
$server->_auto_translation = true;
$server->addObjectMap(new SOAP_Example_Server(), 'urn:SOAP_Example_Server');

/* Create example client. */
$client = new SOAP_Client('test://foo/');
$client->setOpt('server', $server);
$client->_auto_translation = true;
$client->setTypeTranslation('{http://soapinterop.org/xsd}SOAPStruct',
                            'SOAPStruct');
$options = array('namespace' => 'urn:SOAP_Example_Server',
                 'trace' => true);

/* Create test list. */
$struct = new SOAPStruct('test string', 123, 123.123);
$calls = array(
    array('echoStringSimple',
          array('inputStringSimple' => 'this is a test string')),
    array('echoString',
          array('inputString' => 'this is a test string')),
    array('divide',
          array('dividend' => 22, 'divisor' => 7)),
    array('divide',
          array('dividend' => 22, 'divisor' => 0)),
    array('echoStruct',
          array('inputStruct' => $struct->__to_soap())),
    array('echoStructAsSimpleTypes',
          array('inputStruct' => $struct->__to_soap())),
);

/* Run tests. */
ob_start();
foreach ($calls as $call) {
    $result = $client->call($call[0], $call[1], $options);
    if (is_a($result, 'PEAR_Error')) {
        echo $result->getMessage();
    } else {
        var_export($result);
    }
    echo "\n";
}
ob_end_flush();

?>
--EXPECT--
'this is a test string'
'this is a test string'
3.1428571428571
You cannot divide by zero
class soapstruct {
  var $varString = 'test string';
  var $varInt = 123;
  var $varFloat = 123.123;
}
array (
  'outputString' => 'test string',
  'outputInteger' => 123,
  'outputFloat' => 123.123,
)
