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
require_once 'SOAP/Value.php';
require_once 'SOAP/Fault.php';


class SOAPStruct {
    var $varString;
    var $varInt;
    var $varFloat;
    function SOAPStruct($s=NULL, $i=NULL, $f=NULL) {
        $this->varString = $s;
        $this->varInt = $i;
        $this->varFloat = $f;
    }
}

// create a class for your soap functions
class SOAP_Example_Server {
    /**
     * The dispactch map does not need to be used, but aids
     * the server class in knowing what parameters are used
     * with the functions.  This is the ONLY way to have
     * multiple OUT parameters
     */
    var $__dispatch_map = array();

    function SOAP_Example_Server() {
        // the one function here has multiple out parameters
	$this->__dispatch_map['echoStructAsSimpleTypes'] =
		array('in' => array('inputStruct' => 'SOAPStruct'),
		      'out' => array('outputString' => 'string', 'outputInteger' => 'int', 'outputFloat' => 'float')
		      );
    }

    /* this private function is called on by SOAP_Server to determine any
        special dispatch information that might be necessary.  This, for example,
        can be used to set up a dispatch map for functions that return multiple
        OUT parameters */
    function __dispatch($methodname) {
        if (isset($this->__dispatch_map[$methodname]))
            return $this->__dispatch_map[$methodname];
        return NULL;
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

    // a method that might return a fault
    function divide($dividend, $divisor)
    {
        if ($divisor == 0)
            return new SOAP_Fault('You cannot divide by zero', 'Client');
        else
            return $dividend / $divisor;
    }

    function echoStruct($inputStruct)
    {
	return new SOAP_Value('outputStruct','{http://soapinterop.org/xsd}SOAPStruct',$inputStruct);
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
	    new SOAP_Value('outputString','string',$struct->varString),
	    new SOAP_Value('outputInteger','int',$struct->varInt),
	    new SOAP_Value('outputFloat','float',$struct->varFloat)
	    );
    }    
}

?>