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
<inputString xsi:type="xsd:string"></inputString>
</ns7:echoString>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';
$test = '<?xml version="1.0" encoding="US-ASCII"?>

<SOAP-ENV:Envelope  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
  xmlns:si="http://soapinterop.org/xsd"
  xmlns:ns6="http://soapinterop.org/"
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>
<ns6:echoVoid/>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

$test = '<?xml version="1.0" encoding="US-ASCII"?>

<SOAP-ENV:Envelope  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
  xmlns:si="http://soapinterop.org/xsd"
  xmlns:ns6="http://soapinterop.org/"
  SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>
<ns6:echoIntegerArray><inputIntegerArray xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:int[3]" SOAP-ENC:offset="[0]"><item xsi:type="xsd:int">1</item>
<item xsi:type="xsd:int">234324324</item>
<item xsi:type="xsd:int">2</item>
</inputIntegerArray>
</ns6:echoIntegerArray>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

$test = "<S:Envelope
S:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'
xmlns:Enc='http://schemas.xmlsoap.org/soap/encoding/'
xmlns:S='http://schemas.xmlsoap.org/soap/envelope/'
xmlns:a='http://soapinterop.org/'
xmlns:b='http://soapinterop.org/xsd'
xmlns:XS='http://www.w3.org/2001/XMLSchema'
xmlns:XI='http://www.w3.org/2001/XMLSchema-instance'>
<S:Body>
<b:SOAPStruct Enc:root='0' id='21b56c4' XI:type='b:SOAPStruct'>
<varInt XI:type='XS:int'>1</varInt>
<varFloat XI:type='XS:float'>2</varFloat>
<varString XI:type='XS:string'>wilma</varString>
</b:SOAPStruct>
<a:echoStructArray>
<inputStructArray XI:type='Enc:Array' Enc:arrayType='XS:anyType[3]'>
<fred href='#21b56c4'/>
<i href='#21b56c4'/>
<i href='#21b56c4'/>
</inputStructArray>
</a:echoStructArray>
</S:Body></S:Envelope>";

#$test = "<S:Envelope S:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/' xmlns:Enc='http://schemas.xmlsoap.org/soap/encoding/' xmlns:S='http://schemas.xmlsoap.org/soap/envelope/' xmlns:a='http://soapinterop.org/' xmlns:b='http://soapinterop.org/xsd' xmlns:XS='http://www.w3.org/2001/XMLSchema' xmlns:XI='http://www.w3.org/2001/XMLSchema-instance'> <S:Body><a:echoStructArray><inputStructArray XI:type='Enc:Array' Enc:arrayType='b:SOAPStruct[2]'><inputStruct href='#213e654'/> <inputStruct href='#21b8c4c'/> </inputStructArray> </a:echoStructArray> <b:SOAPStruct Enc:root='0' id='21b8c4c' XI:type='b:SOAPStruct'><varInt XI:type='XS:int'>-1</varInt> <varFloat XI:type='XS:float'>-1</varFloat> <varString XI:type='XS:string'>lean on into the groove y'all</varString> </b:SOAPStruct> <b:SOAPStruct Enc:root='0' id='213e654' XI:type='b:SOAPStruct'><varInt XI:type='XS:int'>1073741824</varInt> <varFloat XI:type='XS:float'>-42.24</varFloat> <varString XI:type='XS:string'>pocketSOAP rocks!&lt;g&gt;</varString> </b:SOAPStruct> </S:Body></S:Envelope>";

$test = "<S:Envelope S:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/' xmlns:S='http://schemas.xmlsoap.org/soap/envelope/' xmlns:b='http://soapinterop.org/' xmlns:a='http://soapinterop.org/headers/' xmlns:XS='http://www.w3.org/2001/XMLSchema' xmlns:XI='http://www.w3.org/2001/XMLSchema-instance'> <S:Header> <a:Transaction S:mustUnderstand='1' XI:type='XS:short'>5</a:Transaction> </S:Header> <S:Body><b:echoString><inputString XI:type='XS:string'>Opps, should never see me</inputString> </b:echoString> </S:Body></S:Envelope>";
$test = "<S:Envelope S:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/' xmlns:S='http://schemas.xmlsoap.org/soap/envelope/' xmlns:b='http://soapinterop.org/' xmlns:a='http://soapinterop.org/headers/' xmlns:XS='http://www.w3.org/2001/XMLSchema' xmlns:XI='http://www.w3.org/2001/XMLSchema-instance'> <S:Header> <a:Transaction XI:type='XS:short'>5</a:Transaction> </S:Header> <S:Body><b:echoString><inputString XI:type='XS:string'>Opps, should never see me</inputString> </b:echoString> </S:Body></S:Envelope>";
$test = "<S:Envelope S:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/' xmlns:Enc='http://schemas.xmlsoap.org/soap/encoding/' xmlns:S='http://schemas.xmlsoap.org/soap/envelope/' xmlns:a='http://soapinterop.org/' xmlns:b='http://soapinterop.org/xsd' xmlns:XS='http://www.w3.org/2001/XMLSchema' xmlns:XI='http://www.w3.org/2001/XMLSchema-instance'> <S:Body><a:echoStructAsSimpleTypes><inputStruct href='#213e59c'/> </a:echoStructAsSimpleTypes> <b:SOAPStruct Enc:root='0' id='213e59c' XI:type='b:SOAPStruct'><varInt XI:type='XS:int'>42</varInt> <varString XI:type='XS:string'>Orbital</varString> <varFloat XI:type='XS:float'>-42.42</varFloat> </b:SOAPStruct> </S:Body></S:Envelope>";
$server->service($test, '',TRUE);

?>