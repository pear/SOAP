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

// first, include the SOAP/Server class
require_once 'SOAP/Server.php';

$server = new SOAP_Server;

// create a class for your soap functions
class SOAP_Example_Server {
    /**
     * method namespace can be whatever you desire, the client
     * must match it when it makes calls to you
     *
     * example namespaces
     * urn:SOAP_Example_Server
     * http://myserver.com/SOAP_Example_Server
     */
    var $method_namespace = 'urn:SOAP_Example_Server';
    
    /**
     * The dispactch map does not need to be used, but aids
     * the server class in knowing what parameters are used
     * with the functions.  This is the ONLY way to have
     * multiple OUT parameters
     */
    var $dispatch_map = array();

    function SOAP_Interop_GroupB() {
        // the one function here has multiple out parameters
	$this->dispatch_map['echoStructAsSimpleTypes'] =
		array('in' => array('inputStruct' => 'SOAPStruct'),
		      'out' => array('outputString' => 'string', 'outputInteger' => 'int', 'outputFloat' => 'float')
		      );
    }

    // a simple echoString function
    function echoStringSimple($inputString)
    {
	return $inputString;
    }
    
    // an explicit echostring function
    function echoString($inputString)
    {
	return new SOAP_Value('outputString','string',$inputString);
    }
    
    /**
     * echoStructAsSimpleTypes
     * takes a SOAPStruct as input, and returns each of its elements
     * as OUT parameters
     *
     * SOAPStruct is defined as:
     *
     * struct SOAPStruct:
     *    string varString
     *    integer varInt
     *    float varFloat
     *
     */
    function echoStructAsSimpleTypes ($struct)
    {
	# convert a SOAPStruct to an array
	return array(
	    new SOAP_Value('outputString','string',$struct['varString']),
	    new SOAP_Value('outputInteger','int',$struct['varInt']),
	    new SOAP_Value('outputFloat','float',$struct['varFloat'])
	    );
    }    
}

$soapclass = new SOAP_Example_Server();
$server->addObjectMap($soapclass);
$server->service($HTTP_RAW_POST_DATA);
?>