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
// | Authors: Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more   |
// | Authors: Dietrich Ayala <dietrich@ganx4.com> Original Author         |
// +----------------------------------------------------------------------+
//
// $Id$
//

// 1. include server.php
include("SOAP/Server.php");

// 2. instantiate server object
$server = new SOAP_Server;

/* 3. call the add_to_map() method for each service (function) you want to expose:

$server->add_to_map(
	"echoString",		// function name
	array("string"),	// array of input types
	array("string")		// array of output types
);

function echoString($string){
	return $string;
}

*/
include("server_soapinterop_base.php");

// 4. call the service method to initiate transaction
// and send response
$server->service($HTTP_RAW_POST_DATA, TRUE);

?>