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
$usewsdl = 0;
$test = 'base';  // which test to do: base, GroupB, GroupC
$parm = 'php'; // use base types: php, soapval
$show = 1;
$debug = 0;
$numservers = 0; // zero for all of them
#$testfunc = 'echoStruct'; // test a single function
#$specificendpoint = '4s4c v2.0'; //"http://63.142.188.184:1122/"; // endpoint url
// slow or unavailable sites in interop list
$skip = array();

if ($localonly) {
    # define your test servers endpointURL here
    $endpoints[$SOAP_LibraryName] = array(
            'endpointURL' => 'http://127.0.0.1/soap/interop.php',
            'name' => $SOAP_LibraryName);
} elseif ($usebuiltin) {
    # NOTE: run endpoints_generate.php to generate 'builtin' files. 
    include_once 'SOAP/interop/endpoints_'.$test.'.php';
    
    # overrides for when whitemesa is simply wrong
    if ($test == 'base') {
        $endpoints['MS SOAP ToolKit 2.0'] = array(
                'endpointURL' => 'http://mssoapinterop.org/stk/Interop.wsdl',
                'wsdlURL' => 'http://mssoapinterop.org/stk/Interop.wsdl',
                'endpointName' => 'MS SOAP ToolKit 2.0');
    }

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
