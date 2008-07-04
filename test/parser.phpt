--TEST--
SOAP_Parser tests.
--FILE--
<?php

require_once 'SOAP/Parser.php';

$foo = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>
<namesp1:echoStringArrayResponse xmlns:namesp1="http://soapinterop.org/">
<return xsi:type="SOAP-ENC:Array" SOAP-ENC:arrayType="xsd:string[2]">
<item xsi:type="xsd:string">good</item>
<item xsi:type="xsd:string">bad</item>
</return>
</namesp1:echoStringArrayResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOF;

$stringArray = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>

<SOAP-ENV:Envelope  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 xmlns:ns4="http://soapinterop.org/"
 SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>

<ns4:echoStringArrayResponse>
<outputStringArray>
<item xsi:type="xsd:string">good</item>
<item xsi:type="xsd:string">bad</item>
</outputStringArray>
</ns4:echoStringArrayResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOS;

$stringArrayOne = <<<EOO
<?xml version="1.0" encoding="UTF-8"?>

<SOAP-ENV:Envelope  xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 xmlns:ns4="http://soapinterop.org/"
 SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>

<ns4:echoStringArrayResponse>
<outputStringArray>
<item xsi:type="xsd:string">good</item>
</outputStringArray>
</ns4:echoStringArrayResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOO;

$parser = new SOAP_Parser($foo);
var_dump($parser->getResponse());
$parser = new SOAP_Parser($stringArray);
var_dump($parser->getResponse());
$parser = new SOAP_Parser($stringArrayOne);
var_dump($parser->getResponse());

?>
--EXPECTF--
object(soap_value)(11) {
  ["value"]=>
  array(1) {
    [0]=>
    object(soap_value)(11) {
      ["value"]=>
      array(2) {
        [0]=>
        object(soap_value)(11) {
          ["value"]=>
          string(4) "good"
          ["name"]=>
          string(4) "item"
          ["type"]=>
          string(6) "string"
          ["namespace"]=>
          string(23) "http://soapinterop.org/"
          ["type_namespace"]=>
          string(32) "http://www.w3.org/2001/XMLSchema"
          ["attributes"]=>
          array(0) {
          }
          ["arrayType"]=>
          string(0) ""
          ["options"]=>
          array(0) {
          }
          ["nqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(4) "item"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(23) "http://soapinterop.org/"
          }
          ["tqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(6) "string"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(32) "http://www.w3.org/2001/XMLSchema"
          }
          ["type_prefix"]=>
          string(0) ""
        }
        [1]=>
        object(soap_value)(11) {
          ["value"]=>
          string(3) "bad"
          ["name"]=>
          string(4) "item"
          ["type"]=>
          string(6) "string"
          ["namespace"]=>
          string(23) "http://soapinterop.org/"
          ["type_namespace"]=>
          string(32) "http://www.w3.org/2001/XMLSchema"
          ["attributes"]=>
          array(0) {
          }
          ["arrayType"]=>
          string(0) ""
          ["options"]=>
          array(0) {
          }
          ["nqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(4) "item"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(23) "http://soapinterop.org/"
          }
          ["tqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(6) "string"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(32) "http://www.w3.org/2001/XMLSchema"
          }
          ["type_prefix"]=>
          string(0) ""
        }
      }
      ["name"]=>
      string(6) "return"
      ["type"]=>
      string(5) "Array"
      ["namespace"]=>
      string(23) "http://soapinterop.org/"
      ["type_namespace"]=>
      string(41) "http://schemas.xmlsoap.org/soap/encoding/"
      ["attributes"]=>
      array(0) {
      }
      ["arrayType"]=>
      string(6) "string"
      ["options"]=>
      array(0) {
      }
      ["nqn"]=>
      object(qname)(3) {
        ["name"]=>
        string(6) "return"
        ["ns"]=>
        string(0) ""
        ["namespace"]=>
        string(23) "http://soapinterop.org/"
      }
      ["tqn"]=>
      object(qname)(3) {
        ["name"]=>
        string(5) "Array"
        ["ns"]=>
        string(0) ""
        ["namespace"]=>
        string(41) "http://schemas.xmlsoap.org/soap/encoding/"
      }
      ["type_prefix"]=>
      string(0) ""
    }
  }
  ["name"]=>
  string(23) "echoStringArrayResponse"
  ["type"]=>
  string(6) "Struct"
  ["namespace"]=>
  string(23) "http://soapinterop.org/"
  ["type_namespace"]=>
  string(0) ""
  ["attributes"]=>
  array(0) {
  }
  ["arrayType"]=>
  string(0) ""
  ["options"]=>
  array(0) {
  }
  ["nqn"]=>
  object(qname)(3) {
    ["name"]=>
    string(23) "echoStringArrayResponse"
    ["ns"]=>
    string(0) ""
    ["namespace"]=>
    string(23) "http://soapinterop.org/"
  }
  ["tqn"]=>
  object(qname)(3) {
    ["name"]=>
    string(6) "Struct"
    ["ns"]=>
    string(0) ""
    ["namespace"]=>
    string(0) ""
  }
  ["type_prefix"]=>
  string(0) ""
}
object(soap_value)(11) {
  ["value"]=>
  array(1) {
    [0]=>
    object(soap_value)(11) {
      ["value"]=>
      array(2) {
        [0]=>
        object(soap_value)(11) {
          ["value"]=>
          string(4) "good"
          ["name"]=>
          string(4) "item"
          ["type"]=>
          string(6) "string"
          ["namespace"]=>
          string(23) "http://soapinterop.org/"
          ["type_namespace"]=>
          string(32) "http://www.w3.org/2001/XMLSchema"
          ["attributes"]=>
          array(0) {
          }
          ["arrayType"]=>
          string(0) ""
          ["options"]=>
          array(0) {
          }
          ["nqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(4) "item"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(23) "http://soapinterop.org/"
          }
          ["tqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(6) "string"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(32) "http://www.w3.org/2001/XMLSchema"
          }
          ["type_prefix"]=>
          string(0) ""
        }
        [1]=>
        object(soap_value)(11) {
          ["value"]=>
          string(3) "bad"
          ["name"]=>
          string(4) "item"
          ["type"]=>
          string(6) "string"
          ["namespace"]=>
          string(23) "http://soapinterop.org/"
          ["type_namespace"]=>
          string(32) "http://www.w3.org/2001/XMLSchema"
          ["attributes"]=>
          array(0) {
          }
          ["arrayType"]=>
          string(0) ""
          ["options"]=>
          array(0) {
          }
          ["nqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(4) "item"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(23) "http://soapinterop.org/"
          }
          ["tqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(6) "string"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(32) "http://www.w3.org/2001/XMLSchema"
          }
          ["type_prefix"]=>
          string(0) ""
        }
      }
      ["name"]=>
      string(17) "outputStringArray"
      ["type"]=>
      string(6) "Struct"
      ["namespace"]=>
      string(23) "http://soapinterop.org/"
      ["type_namespace"]=>
      string(0) ""
      ["attributes"]=>
      array(0) {
      }
      ["arrayType"]=>
      string(0) ""
      ["options"]=>
      array(0) {
      }
      ["nqn"]=>
      object(qname)(3) {
        ["name"]=>
        string(17) "outputStringArray"
        ["ns"]=>
        string(0) ""
        ["namespace"]=>
        string(23) "http://soapinterop.org/"
      }
      ["tqn"]=>
      object(qname)(3) {
        ["name"]=>
        string(6) "Struct"
        ["ns"]=>
        string(0) ""
        ["namespace"]=>
        string(0) ""
      }
      ["type_prefix"]=>
      string(0) ""
    }
  }
  ["name"]=>
  string(23) "echoStringArrayResponse"
  ["type"]=>
  string(6) "Struct"
  ["namespace"]=>
  string(23) "http://soapinterop.org/"
  ["type_namespace"]=>
  string(0) ""
  ["attributes"]=>
  array(0) {
  }
  ["arrayType"]=>
  string(0) ""
  ["options"]=>
  array(0) {
  }
  ["nqn"]=>
  object(qname)(3) {
    ["name"]=>
    string(23) "echoStringArrayResponse"
    ["ns"]=>
    string(0) ""
    ["namespace"]=>
    string(23) "http://soapinterop.org/"
  }
  ["tqn"]=>
  object(qname)(3) {
    ["name"]=>
    string(6) "Struct"
    ["ns"]=>
    string(0) ""
    ["namespace"]=>
    string(0) ""
  }
  ["type_prefix"]=>
  string(0) ""
}
object(soap_value)(11) {
  ["value"]=>
  array(1) {
    [0]=>
    object(soap_value)(11) {
      ["value"]=>
      array(1) {
        [0]=>
        object(soap_value)(11) {
          ["value"]=>
          string(4) "good"
          ["name"]=>
          string(4) "item"
          ["type"]=>
          string(6) "string"
          ["namespace"]=>
          string(23) "http://soapinterop.org/"
          ["type_namespace"]=>
          string(32) "http://www.w3.org/2001/XMLSchema"
          ["attributes"]=>
          array(0) {
          }
          ["arrayType"]=>
          string(0) ""
          ["options"]=>
          array(0) {
          }
          ["nqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(4) "item"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(23) "http://soapinterop.org/"
          }
          ["tqn"]=>
          object(qname)(3) {
            ["name"]=>
            string(6) "string"
            ["ns"]=>
            string(0) ""
            ["namespace"]=>
            string(32) "http://www.w3.org/2001/XMLSchema"
          }
          ["type_prefix"]=>
          string(0) ""
        }
      }
      ["name"]=>
      string(17) "outputStringArray"
      ["type"]=>
      string(6) "Struct"
      ["namespace"]=>
      string(23) "http://soapinterop.org/"
      ["type_namespace"]=>
      string(0) ""
      ["attributes"]=>
      array(0) {
      }
      ["arrayType"]=>
      string(0) ""
      ["options"]=>
      array(0) {
      }
      ["nqn"]=>
      object(qname)(3) {
        ["name"]=>
        string(17) "outputStringArray"
        ["ns"]=>
        string(0) ""
        ["namespace"]=>
        string(23) "http://soapinterop.org/"
      }
      ["tqn"]=>
      object(qname)(3) {
        ["name"]=>
        string(6) "Struct"
        ["ns"]=>
        string(0) ""
        ["namespace"]=>
        string(0) ""
      }
      ["type_prefix"]=>
      string(0) ""
    }
  }
  ["name"]=>
  string(23) "echoStringArrayResponse"
  ["type"]=>
  string(6) "Struct"
  ["namespace"]=>
  string(23) "http://soapinterop.org/"
  ["type_namespace"]=>
  string(0) ""
  ["attributes"]=>
  array(0) {
  }
  ["arrayType"]=>
  string(0) ""
  ["options"]=>
  array(0) {
  }
  ["nqn"]=>
  object(qname)(3) {
    ["name"]=>
    string(23) "echoStringArrayResponse"
    ["ns"]=>
    string(0) ""
    ["namespace"]=>
    string(23) "http://soapinterop.org/"
  }
  ["tqn"]=>
  object(qname)(3) {
    ["name"]=>
    string(6) "Struct"
    ["ns"]=>
    string(0) ""
    ["namespace"]=>
    string(0) ""
  }
  ["type_prefix"]=>
  string(0) ""
}
