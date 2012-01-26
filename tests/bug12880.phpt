--TEST--
Bug #12800: No return value if using overloading
--SKIPIF--
<?php if (version_compare(zend_version(), 2, '<')) echo 'skip Test requires PHP5'; ?>
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
$client->setOpt('namespace', 'urn:SOAP_Example_Server');
ob_start();
var_export($client->call('echoString', array('hello world')));
echo "\n";
var_export($client->echoString('hello world'));
ob_end_flush();

?>
--EXPECT--
'hello world'
'hello world'
