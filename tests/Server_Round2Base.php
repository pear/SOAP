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

$server = new SOAP_Server;

require_once 'ServerHandler_Round2Base.php';

$soapclass = new ServerHandler_Round2Base();
$server->addService($soapclass);

if (isset($_SERVER['REQUEST_METHOD']) &&
    $_SERVER['REQUEST_METHOD']=='POST') {
    $server->service('php://input');
} else {
    $xml = '<?xml version="1.0"?><soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" soap-env:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><soap-env:Body><echoString xmlns="http://soapinterop.org/"><inputString xsi:type="xsd:string">arg1</inputString></echoString></soap-env:Body></soap-env:Envelope>';
    $xml = '<?xml version="1.0"?><soap-env:Envelope xmlns:soap-env="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" soap-env:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="http://soapinterop.org/xsd"><soap-env:Body><echoStruct xmlns="http://soapinterop.org/"><inputStruct xsi:type="ns1:SOAPStruct"><varInt xsi:type="xsd:int">123</varInt><varFloat xsi:type="xsd:double">123.123</varFloat><varString xsi:type="xsd:string">hello world</varString></inputStruct></echoStruct></soap-env:Body></soap-env:Envelope>';
    $server->service($xml);
}

?>