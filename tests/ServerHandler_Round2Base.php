<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
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
require_once '../Server.php';

// SOAPStruct is defined in the following file
require_once 'InteropTypes.php';

// create a class for your soap functions
class ServerHandler_Round2Base implements SOAP_Service {
    public static function getSOAPServiceNamespace()
	{ return 'http://soapinterop.org/'; }
    public static function getSOAPServiceName()
	{ return 'ExampleService'; }
    public static function getSOAPServiceDescription()
	{ return 'Just a simple example.'; }
    public static function getWSDLURI()
	{ return 'http://localhost/soap/tests/interop.wsdl'; }

    // an explicit echostring function
    public function echoString($inputString)
    {
	return array('return' => (string)$inputString);
    }

    public function echoStringArray($inputStringArray)
    {
	return array('return' => $inputStringArray);
    }

    public function echoInteger($in)
    {
	return array('return' => (int)$in);
    }

    public function echoIntegerArray($in)
    {
	return array('return' => $in);
    }

    public function echoFloat($in)
    {
	return array('return' => (float)$in);
    }

    public function echoFloatArray($in)
    {
	return array('return' => $in);
    }

    public function echoStruct($inputStruct)
    {
        return array('return' => $inputStruct);
    }
 
    public function echoStructArray($in)
    {
	return array('return' => $in);
    }

    public function echoVoid()
    {
	return NULL;
    }

    public function echoBase64($in)
    {
	return array('return' => $in);
    }

    public function echoDate($in)
    {
	return array('return' => $in);
    }

    public function echoHexBinary($in)
    {
	return array('return' => $in);
    }

    public function echoDecimal($in)
    {
	return array('return' => $in);
    }

    public function echoBoolean($in)
    {
	return array('return' => $in);
    }

    
}

?>