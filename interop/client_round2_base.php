<?php
// this script is usefull for quickly testing stuff, use the 'pretty' file for html output
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more   |
// | Authors: Dietrich Ayala <dietrich@ganx4.com> Original Author         |
// +----------------------------------------------------------------------+
//
// $Id$
//
set_time_limit(0);
require_once("SOAP/Client.php");
require_once("client_params.php");
require_once("SOAP/test/test.utility.php");
require_once("SOAP/interop/client_library.php");

error_reporting(E_ALL ^ E_NOTICE);

$localonly = 0; // set to 1 to test only your local server
$usebuiltin = 1; // use builtin list of endpoints
$usewsdl = 1;
$test = 'base';  // which test to do: base, GroupB, GroupC
$parm = 'php'; // use base types: php, soapval
$show = 1;
$debug = 0;
$numservers = 1; // zero for all of them
$testfunc = 'echoStructArray'; // test a single function
$specificendpoint = 'http://nagoya.apache.org:5049/soap/servlet/rpcrouter'; //"http://63.142.188.184:1122/"; // endpoint url
// slow or unavailable sites in interop list
$skip = array();

if ($localonly) {
    # define your test servers endpointURL here
    $endpoints[$SOAP_LibraryName] = array(
            'endpointURL' => 'http://127.0.0.1/soap/interop.php',
            'name' => $SOAP_LibraryName);
} elseif ($usebuiltin) {
    $endpoints['4s4c'] = array(
            'endpointURL' => 'http://soap.4s4c.com/ilab/soap.asp',
            'wsdlURL' => 'http://www.pocketsoap.com/services/ilab.wsdl',
            'name' => '4s4c');
    $endpoints['Apache Axis'] = array(
            'endpointURL' => 'http://nagoya.apache.org:5049/axis/services/echo',
            'wsdlURL' => 'http://nagoya.apache.org:5049/axis/services/echo?wsdl',
            'name' => 'Apache Axis');
    $endpoints['Apache SOAP 2.2'] = array(
            'endpointURL' => 'http://nagoya.apache.org:5049/soap/servlet/rpcrouter',
            'wsdlURL' => 'http://www.apache.org/~rubys/ApacheSoap.wsdl',
            'name' => 'Apache SOAP 2.2');
    #$endpoints["GLUE"] = array(
    #        "endpointURL" => "http://www.themindelectric.net:8005/glue/round2",
    #        "name" => "GLUE");
    #$endpoints["HP SOAP"] = array(
    #        "endpointURL" => "http://soap.bluestone.com/hpws/soap/EchoService",
    #        "name" => "HP SOAP");
    #$endpoints["IONA XMLBus"] = array(
    #        "endpointURL" => "http://interop.xmlbus.com:7002/xmlbus/container/InteropTest/BaseService/BasePort",
    #        "name" => "IONA XMLBus");
}

/********************************************************************
* you don't need to do anything below here
*/

if ($localonly || $usebuiltin ||
    getInteropEndpoints($test)
    ) {
    print "Got ".count($endpoints)." endpoints\n";
    do_interopTest($method_params[$test][$parm]);
}

?>
