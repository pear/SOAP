<?
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
require_once("SOAP/Server.php");

function generateFault($short, $long)
{
    $params = array(
        "faultcode" => "Server",
        "faultstring" => $short,
        "detail" => $long
    );

    $faultmsg  = new SOAP_Message("Fault",$params,"http://schemas.xmlsoap.org/soap/envelope/");
    return $faultmsg;
}

function echoString($inputString)
{
    if (!$inputString) {
        return generateFault("Empty Input", "No string detected.");
    }
    $returnSoapVal = new SOAP_Value("return","string",$inputString);
    return $returnSoapVal;
}


function echoStringArray($inputStringArray)
{
    return $inputStringArray;
}


function echoInteger($inputInteger)
{
    return (integer)$inputInteger;
}


function echoIntegerArray($inputIntegerArray)
{
    return $inputIntegerArray;
}


function echoFloat($inputFloat)
{
    return (FLOAT)$inputFloat;
}


function echoFloatArray($inputFloatArray)
{
    return $inputFloatArray;
}

function echoStruct($inputStruct)
{
    return $inputStruct;
}

function echoStructArray($inputStructArray)
{
    return $inputStructArray;
}

$server->addToMap("echoVoid",array(),array());
function echoVoid()
{
    return NULL;
}

$server->addToMap("echoBase64",array("base64Binary"),array("base64Binary"));
function echoBase64($b_encoded)
{
	return base64_encode(base64_decode($b_encoded));
}

$server->addToMap("echoDate",array("dateTime"),array("dateTime"));
function echoDate($timeInstant)
{
	return $timeInstant;
}

function hex2bin($data)
{
    $len = strlen($data);
    return pack("H" . $len, $data);
}

$server->addToMap("echoHexBinary",array("hexBinary"),array("hexBinary"));
function echoHexBinary($hb)
{
	return bin2hex(hex2bin($hb));
}

$server->addToMap("echoDecimal",array("decimal"),array("decimal"));
function echoDecimal($dec)
{
	return (FLOAT)$dec;
}

$server->addToMap("echoBoolean",array("boolean"),array("boolean"));
function echoBoolean($boolean)
{
	if($boolean == 1){
		return "true";
	}
	return "false";
}

?>