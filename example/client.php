<?php
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
include("SOAP/Client.php");
/**
 * this client runs against the example server in SOAP/example/server.php
 */
$soapclient = new SOAP_Client("http://localhost/SOAP/example/server.php");
// this namespace is the same as declared in server.php
$namespace = 'urn:SOAP_Example_Server';

$ret = $soapclient->call("echoStringSimple",array("inputString"=>"this is a test string"),$namespace);
print_r($ret);echo "<br>\n";

$ret = $soapclient->call("echoString",array("inputString"=>"this is a test string"),$namespace);
print_r($ret);echo "<br>\n";

class SOAPStruct {
    var $varString = 'This is a test';
    var $varInt = 1234;
    var $varFloat = 123.456;
}

$SOAPStruct = new SOAPStruct;

/* send an object, get an object back */
$ret = $soapclient->call("echoStruct",array(new SOAP_Value('inputStruct','',$SOAPStruct)),$namespace);
print_r($ret);

/**
 * PHP doesn't support multiple OUT parameters in function calls, so we
 * must do a little work to make it happen here.  This requires knowledge on the
 * developers part to figure out how they want to deal with it.
 */
list($string, $int, $float) = array_values($soapclient->call("echoStructAsSimpleTypes",$SOAPStruct,$namespace));
echo "varString: $string<br>\nvarInt: $int<br>\nvarFloat: $float<br>\n";

?>