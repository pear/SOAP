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
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'SOAP/Server.php';

$server = new SOAP_Server;

require_once 'server_round2_base.php';
require_once 'server_round2_groupB.php';
require_once 'server_round2_groupC.php';

$test = '<?xml version="1.0"?>

<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 xmlns:si="http://soapinterop.org/xsd"
 xmlns:ns6="http://soapinterop.org/echoheader/"
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Header>

<ns6:echoMeStringRequest xsi:type="xsd:string" SOAP-ENV:actor="http://schemas.xmlsoap.org/soap/actor/next" SOAP-ENV:mustUnderstand="0">hello world</ns6:echoMeStringRequest>
</SOAP-ENV:Header>
<SOAP-ENV:Body>

<echoVoid></echoVoid>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

$test = '<?xml version="1.0"?>

<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 xmlns:si="http://soapinterop.org/xsd"
 xmlns:ns6="http://soapinterop.org/echoheader/"
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Header>

<ns6:echoMeStructRequest xsi:type="si:SOAPStruct"
 SOAP-ENV:actor="http://schemas.xmlsoap.org/soap/actor/next"
 SOAP-ENV:mustUnderstand="1">
<varString xsi:type="xsd:string">arg</varString>

<varInt xsi:type="xsd:int">34</varInt>

<varFloat xsi:type="xsd:float">325.325</varFloat>
</ns6:echoMeStructRequest>
</SOAP-ENV:Header>
<SOAP-ENV:Body>

<echoVoid></echoVoid>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

$test = '<?xml version="1.0"?>

<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 xmlns:si="http://soapinterop.org/xsd"
 xmlns:ns6="http://soapinterop.org/echoheader/"
 xmlns:ns7="http://soapinterop.org/"
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>

<ns7:echoString>
<inputString xsi:type="xsd:string">blah</inputString>
</ns7:echoString>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

$server->service($test, '',TRUE);

?>