<?php
// NOTE: do not run this directly under a web server, as it will take a very long
// time to execute.  Run from a command line or something, and redirect output
// to an html file.
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
$usebuiltin = $localonly || 0; // use builtin list of endpoints
$usewsdl = 0;
$testtype = array(0,1); // with or without wsdl
$tests = array_keys($method_params);
$parms = array_keys($method_params[$tests[0]]);
$show = 0;
$debug = 0;
$numservers = 0; // zero for all of them
$testfunc = ''; // test a single function
$specificendpoint = ''; //"http://63.142.188.184:1122/"; // endpoint url
// slow or unavailable sites in interop list
$skip = array(); //endpoints to skip

if ($localonly) {
    # define your test servers endpointURL here
    $endpoints[$SOAP_LibraryName] = array(
            'endpointURL' => 'http://127.0.0.1/soap/interop.php',
            'name' => $SOAP_LibraryName);
} elseif ($usebuiltin) {
    # for doing short tests
    $endpoints['SilverStream'] = array(
            'endpointURL' => 'http://explorer.ne.mediaone.net/app/interop/interop',
            'wsdlURL' => 'http://www.xmethods.net/soapbuilders/silverstream/InteropTest.wsdl',
            'name' => 'SilverStream');
    #$endpoints['SIM'] = array(
    #        'endpointURL' => 'http://soapinterop.simdb.com/round2',
    #        'wsdlURL' => 'http://soapinterop.simdb.com/round2?WSDL',
    #        'name' => 'SIM');
    #$endpoints['4s4c'] = array(
    #        'endpointURL' => 'http://soap.4s4c.com/ilab/soap.asp',
    #        'wsdlURL' => 'http://www.pocketsoap.com/services/ilab.wsdl',
    #        'name' => '4s4c');
    #$endpoints['Apache Axis'] = array(
    #        'endpointURL' => 'http://nagoya.apache.org:5049/axis/services/echo',
    #        'wsdlURL' => 'http://nagoya.apache.org:5049/axis/services/echo?wsdl',
    #        'name' => 'Apache Axis');
    #$endpoints['Apache SOAP 2.2'] = array(
    #        'endpointURL' => 'http://nagoya.apache.org:5049/soap/servlet/rpcrouter',
    #        'wsdlURL' => 'http://www.apache.org/~rubys/ApacheSoap.wsdl',
    #        'name' => 'Apache SOAP 2.2');
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<style>
TD { background-color: Red; }
TD.BLANK { background-color: White; }
TD.OK { background-color: Lime; }
TD.CONNECT { background-color: Yellow; }
TD.TRANSPORT { background-color: Yellow; }
TD.WSDL { background-color: Yellow; }
TD.WSDLPARSER { background-color: Yellow; }
TD.HTTP { background-color: Yellow; }
TD.SMTP { background-color: Yellow; }
</style>
	<title>PEAR-PHP SOAP Interop Tests</title>
</head>

<body bgcolor="White" text="Black">
<h2 align="center">SOAP Client Interop Test Results: Round2</h2>
<p>
Notes:
Tests are done both "Direct" and with "WSDL".  WSDL tests use the supplied interop WSDL
to run the tests against.  The Direct method uses an internal prebuilt list of methods and parameters
for the test.</p>
<p>
Tests are also run against two methods of generating method parameters.  The first, 'php', attempts
to directly serialize PHP variables into soap values.  The second method, 'soapval', uses a SOAP_Value
class to define what the type of the value is.  The second method is more interopable than the first
by nature.
</p>
<p>
More detail about errors (marked yellow or red) will follow each table.  If we have an HTTP error
attempting to connect to the endpoint, we will mark all consecutive attempts as errors, and skip
testing that endpoint.  This reduces the time it takes to run the tests if a server is unavailable.
</p>
<p>
More information on Round 2 Interopability is available at
<a href="http://www.whitemesa.com/interop.htm">http://www.whitemesa.com/interop.htm</a>.
</p>
<?php
foreach ($tests as $test) {
    foreach ($parms as $parm) {
        if (!$usebuiltin) getInteropEndpoints($test);
        foreach ($testtype as $usewsdl) {
            echo "<!-- DEBUG OUTPUT\n";
            do_interopTest($method_params[$test][$parm]);
            echo "#-->\n";
            outputTables($test, $parm, $usewsdl, $endpoints, array_keys($method_params[$test][$parm]));
        }
    }
}
?>
</body>
</html>
