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
require_once('SOAP/Server.php');

$server->addToMap('echoStructAsSimpleTypes',
		  array('inputStruct' => 'struct'),
		  array('outputString' => 'string', 'outputInteger' => 'int', 'outputFloat' => 'float'));
function echoStructAsSimpleTypes ($struct)
{
    # convert a SOAPStruct to an array
    return array_values($struct);
}

$server->addToMap('echoSimpleTypesAsStruct',
		  array('outputString' => 'string', 'outputInteger' => 'int', 'outputFloat' => 'float'),
		  array('return' => 'struct'));
function echoSimpleTypesAsStruct($string, $int, $float)
{
    # convert a input into struct
    /*$ret = new SOAP_Value("return","struct",
            array( #push struct elements into one soap value
                new SOAP_Value("varString","string",$string),
                new SOAP_Value("varInt","int",$int),
                new SOAP_Value("varFloat","float",$float)
            )
        );*/
    $ret = array(
        "varString"=>$string,
        "varInt"=>$int,
        "varFloat"=>$float
    );
    return $ret;
}

function echoNestedStruct($struct)
{
    return $struct;
}

function echo2DStringArray($ary)
{
    return $ary;
}

function echoNestedArray($ary)
{
    return $ary;
}

?>