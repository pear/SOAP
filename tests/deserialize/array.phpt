--TEST--
Deserialize nested arrays
--FILE--
<?php

require_once dirname(__FILE__) . '/../test.utility.php';

$msg = '
<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
  <SOAP-ENV:Body xmlns:ns1="urn:something">
    <ns1:test SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
      <anArray soapenc:arrayType="xsd:anyType[4]" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="soapenc:Array">
        <item soapenc:arrayType="xsd:string[0]" xsi:type="soapenc:Array" />
        <item soapenc:arrayType="xsd:double[4]" xsi:type="soapenc:Array">
          <item xsi:type="xsd:double">1</item>
          <item xsi:type="xsd:double">5</item>
          <item xsi:type="xsd:double">0</item>
          <item xsi:type="xsd:double">6</item>
        </item>
        <item soapenc:arrayType="xsd:double[][3]" xsi:type="soapenc:Array">
          <item soapenc:arrayType="xsd:double[3]" xsi:type="soapenc:Array">
            <item xsi:type="xsd:double">8</item>
            <item xsi:type="xsd:double">9</item>
            <item xsi:type="xsd:double">1</item>
          </item>
          <item soapenc:arrayType="xsd:double[0]" xsi:type="soapenc:Array" />
          <item soapenc:arrayType="xsd:double[6]" xsi:type="soapenc:Array">
            <item xsi:type="xsd:double">5</item>
            <item xsi:type="xsd:double">7</item>
            <item xsi:type="xsd:double">654</item>
            <item xsi:type="xsd:double">8</item>
            <item xsi:type="xsd:double">1</item>
            <item xsi:type="xsd:double">32</item>
          </item>
        </item>
        <item soapenc:arrayType="xsd:double[2]" xsi:type="soapenc:Array">
          <item xsi:type="xsd:double">54</item>
          <item xsi:type="xsd:double">57</item>
        </item>
      </anArray>
    </ns1:test>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

$val = parseMessage($msg);
var_dump($val);

?>
--EXPECT--
array(4) {
  [0]=>
  string(0) ""
  [1]=>
  array(4) {
    [0]=>
    float(1)
    [1]=>
    float(5)
    [2]=>
    float(0)
    [3]=>
    float(6)
  }
  [2]=>
  array(3) {
    [0]=>
    array(3) {
      [0]=>
      float(8)
      [1]=>
      float(9)
      [2]=>
      float(1)
    }
    [1]=>
    float(0)
    [2]=>
    array(6) {
      [0]=>
      float(5)
      [1]=>
      float(7)
      [2]=>
      float(654)
      [3]=>
      float(8)
      [4]=>
      float(1)
      [5]=>
      float(32)
    }
  }
  [3]=>
  array(2) {
    [0]=>
    float(54)
    [1]=>
    float(57)
  }
}
