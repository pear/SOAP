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
// | Authors: Shane Caraveo <Shane@Caraveo.com>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//
require_once 'SOAP/interop/client_round2_interop.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<style>
TD { background-color: Red; }
TD.BLANK { background-color: White; }
TD.OK { background-color: Lime; }
TD.RESULT { background-color: Green; }
TD.untested { background-color: White; }
TD.CONNECT { background-color: Yellow; }
TD.TRANSPORT { background-color: Yellow; }
TD.WSDL { background-color: Yellow; }
TD.WSDLCACHE { background-color: Yellow; }
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
WSDLCACHE errors mean we cannot retreive the WSDL file specified for the endpoint.
</p>
<p>
More information on Round 2 Interopability is available at
<a href="http://www.whitemesa.com/interop.htm">http://www.whitemesa.com/interop.htm</a>.
</p>

<?php
$iop = new Interop_Client();

if ($_GET['detail'] == 1) $iop->showFaults = 1;

if ($_GET['wire']) {
    $iop->showWire($_GET['wire']);
} else {
    $iop->getEndpoints();
    $iop->getResults();
    
    if ($_GET['test']) {
        $iop->currentTest = $_GET['test'];
        $iop->useWSDL = $_GET['wsdl']?$_GET['wsdl']:0;
        $iop->paramType = $_GET['type']?$_GET['type']:'php';
        $iop->outputTable();
    } else {
        $iop->outputTables();
    }
}
?>
</body>
</html>
