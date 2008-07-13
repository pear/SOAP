--TEST--
Example server test
--FILE--
<?php

/* Load files. */
require_once 'SOAP/Client.php';
require_once 'SOAP/Server.php';
require_once 'PEAR/Config.php';
$config = &PEAR_Config::singleton();
require_once $config->get('doc_dir') . '/SOAP/example/example_server.php';

/* Create example server. */
$_SERVER['SERVER_NAME'] = null;
$server = new SOAP_Server;
$server->_auto_translation = true;
$server->addObjectMap(new SOAP_Example_Server(), 'urn:SOAP_Example_Server');

/* Create example client. */
$client = new SOAP_Client('test://foo/');
$client->setOpt('server', $server);
$options = array('namespace' => 'urn:SOAP_Example_Server',
                 'trace' => true);

/* Run tests. */
var_export($client->call('echoStringSimple',
                         $p = array('inputStringSimple' => 'this is a test string'),
                         $options));
echo "\n";

?>
--EXPECT--
<?xml version="1.0" encoding="UTF-8"?>

<SOAP-ENV:Envelope  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 xmlns:ns4="urn:SOAP_Example_Server"
 SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>

<ns4:echoStringSimpleResponse>
<return xsi:type="xsd:string">this is a test string</return></ns4:echoStringSimpleResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
'this is a test string'
