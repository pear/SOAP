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
// include soap client class
include("SOAP/Client.php");

print "<html><body><br>\n<strong>wsdl:</strong>";
$soapclient = new SOAP_Client("http://services.xmethods.net/soap/urn:xmethods-delayed-quotes.wsdl","wsdl");
echo $soapclient->call("getQuote",array("symbol"=>"ibm"));
unset($soapclient);

print "\n<br><strong>non wsdl:</strong>";
$soapclient = new SOAP_Client("http://services.xmethods.net:80/soap");
$ret = $soapclient->call("getQuote",array("symbol"=>"ibm"),"urn:xmethods-delayed-quotes","urn:xmethods-delayed-quotes#getQuote");
print $ret."\n\n";
print_r($ret);
unset($soapclient);

/* low level api
// create message
$soapmsg = new soapmsg("getQuote",array("symbol"=>"ibm"),"urn:xmethods-delayed-quotes");
// invoke the client
$client = new soap_client("http://services.xmethods.net:80/soap");
// send message and get response
if($return = $client->send($soapmsg,"")){
	if($return->name != "fault"){
		echo array_shift($return->decode());
	} else {
		print "got fault<br>";
		print "<b>Client Debug:</b><br>";
		print "<xmp>$client->debug_str</xmp>";
		die();
	}
} else {
	print "send failed - could not get list of servers from xmethods<br>";
}
*/


?>

