--TEST--
Bug #14756/#14854: Parse multipart/related messages
--FILE--
<?php

require_once 'SOAP/Transport/HTTP.php';

$response = <<<FILE
HTTP/1.1 200
Date: Thu, 09 Oct 2008 15:16:48 GMT
Server: Server
EmbeddedSOAPServer: WASP-C++ Vespa/4.6, build 2162 (Linux i686 2.6.18-8.el5a2xen #1 SMP Tue Apr 3 16:48:05 PDT 2007)
MIME-Version: 1.0
Connection: close
Content-Type: multipart/related; boundary="xxx-WASP-CPP-MIME-Boundary-xxx-yyyyyyyyy-zzzzzzzz-xxx-END-xxx"; type="text/xml"

--xxx-WASP-CPP-MIME-Boundary-xxx-yyyyyyyyy-zzzzzzzz-xxx-END-xxx
Content-Type: text/xml; charset="UTF-8"

<SOAP-ENV:Envelope
xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:SE="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns0:string_Response
xsi:type="xsd:string"
xmlns:ns0="http://systinet.com/xsd/SchemaTypes/"><snip></snip></ns0:string_Response><ns1:doc
href="cid:0xad86d30-0xae618b8-0xae037c0-0xaffa268-0xae62400"
xmlns:ns1="http://systinet.com/xsd/SchemaTypes/"/></SOAP-ENV:Body></SOAP-ENV:Envelope>
--xxx-WASP-CPP-MIME-Boundary-xxx-yyyyyyyyy-zzzzzzzz-xxx-END-xxx
Content-ID: <0xad86d30-0xae618b8-0xae037c0-0xaffa268-0xae62400>
Content-Type: application/binary

<?xml version="1.0"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
<snip></snip>
</AmazonEnvelope>

--xxx-WASP-CPP-MIME-Boundary-xxx-yyyyyyyyy-zzzzzzzz-xxx-END-xxx--
FILE;

$sth = new SOAP_Transport_HTTP('http://example.com/');
$sth->incoming_payload = $response;
$result = $sth->_parseResponse();
if ($result) {
    echo "OK\n\n";
}
var_dump($sth->headers);
echo "\n" . $sth->response . "\n";
var_dump($sth->attachments);

?>
--EXPECT--
OK

array(6) {
  ["date"]=>
  string(29) "Thu, 09 Oct 2008 15:16:48 GMT"
  ["server"]=>
  string(6) "Server"
  ["embeddedsoapserver"]=>
  string(96) "WASP-C++ Vespa/4.6, build 2162 (Linux i686 2.6.18-8.el5a2xen #1 SMP Tue Apr 3 16:48:05 PDT 2007)"
  ["mime-version"]=>
  string(3) "1.0"
  ["connection"]=>
  string(5) "close"
  ["content-type"]=>
  string(25) "text/xml; charset="UTF-8""
}

<SOAP-ENV:Envelope
xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:SE="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns0:string_Response
xsi:type="xsd:string"
xmlns:ns0="http://systinet.com/xsd/SchemaTypes/"><snip></snip></ns0:string_Response><ns1:doc
href="cid:0xad86d30-0xae618b8-0xae037c0-0xaffa268-0xae62400"
xmlns:ns1="http://systinet.com/xsd/SchemaTypes/"/></SOAP-ENV:Body></SOAP-ENV:Envelope>

array(1) {
  ["cid:0xad86d30-0xae618b8-0xae037c0-0xaffa268-0xae62400"]=>
  string(176) "<?xml version="1.0"?>
<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
<snip></snip>
</AmazonEnvelope>

"
}
