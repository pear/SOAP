<?php
require_once 'util.php';
require_once 'WSDL.php';

class SOAPClientException extends Exception {}

class SOAP_Configuration {
    static public $encodings = array('ISO-8859-1','US-ASCII','UTF-8');
    
    static public function supportedEncoding($encoding) {
        return in_array(strtoupper($encoding), SOAP_Configuration::$encodings);
    }
}

class SOAP_Envelope extends DOMDocument {
    public $wsdl = NULL;
    public $body = NULL;
    public $headers = NULL;
    public $soapAction = NULL;
    
    public function __construct() {
        parent::__construct();
        $this->appendChild($this->createElement('soap-env:Envelope'));
        $this->documentElement->setAttribute('xmlns:soap-env',Namespace_Registry::$namespaces['soap-env']);
        $this->body = $this->createElement('soap-env:Body');
        $this->documentElement->appendChild($this->body);
    }
    
    static public function request($wsdl = NULL) {
        $env = new SOAP_Envelope;
        $env->wsdl = $wsdl;
        return $env;
    }

    static public function parse($source, $opts=array()) {
        $env = new SOAP_Envelope;
        if (strncasecmp($source,'<?xml',5)==0) {
            $env->loadXML($source);
        } else {
            $context = stream_context_create($opts);
            libxml_set_streams_context($context);
            $env->load($source);
        }
        $p = $env->documentElement->getElementsByTagNameNS(Namespace_Registry::$namespaces['soap-env'],'Body');
        $env->body = $p[0];
        $p = $env->documentElement->getElementsByTagNameNS(Namespace_Registry::$namespaces['soap-env'],'Header');
        if ($p)
            $env->headers = $p[0];
        return $env;
    }
    
    private function serializeOperation($name, $data, $messageType = 'input') {
        if ($this->wsdl) {
            $node = $this->wsdl->serializeOperation($this, $name, $data, $messageType);
        } else {
            $method = new QName($name);
            if ($messageType == 'output') {
                $method->name = $method->name.'Response';
            } else if ($messageType == 'fault') {
                $method->name = $method->name.'Fault';
            }
            $node = $this->createElementNS($method->namespace,$method->name);
            if ($data) {
                foreach ($data as $argName => $argVal) {
                    $argNode = SchemaSimple::domSerialize($this,$argName,NULL,$argVal,false);
                    $node->appendChild($argNode);
                }
            }
        }
        return $node;
    }
    
    public function addHeader($header, $data) {
        if (!$this->headers) {
            $this->headers = $this->createElement('soap-env:Header');
            $this->documentElement->insertChildBefore($this->headers, $this->body);
        }
        $node = $this->serializeOperation($method, $args, 'header');
        $this->headers->appendChild($node);
    }
    
    public function addMethod($method, $args) {
        $node = $this->serializeOperation($method, $args, 'input');
        $this->body->appendChild($node);
    }

    public function addResponse($method, $args) {
        $node = $this->serializeOperation($method, $args, 'output');
        $this->body->appendChild($node);
    }

    private function deserializePart($part) {
        $children = array();
        foreach ($part->childNodes as $node) {
            if ($node instanceof domelement) {
                $children[$node->localName] = SchemaSimple::domDeserialize($node);
            }
        }
        return $children;
    }
    
    public function deserializeHeaders() {
        return $this->deserializePart($this->headers);
    }

    public function deserializeBody() {
        $return =  $this->deserializePart($this->body);
        #print $result->saveXML();
        return array_shift(array_shift($return));
    }
}

class SOAP_Client {
    public $wsdl;
    public $endpoint;
    private $_encoding = 'UTF-8';
    
    /**
     * SOAP_Client constructor
     *
     * @param string endpoint (URL)
     * @param boolean wsdl (true if endpoint is a wsdl file)
     * @param string portName
     * @param array  contains options for HTTP_Request class (see HTTP/Request.php)
     * @access public
     */
    public function __construct($endpoint, $wsdl = 0) {
        if (!$wsdl)
            $wsdl = strcasecmp('.wsdl',substr($endpoint,strlen($endpoint)-4))==0;
        if ($wsdl) {
            $this->wsdl = WSDLManager::get($endpoint);
        } else {
            $this->endpoint = $endpoint;
        }
    }
    
    public function __call($method, $args) {
        #print "call: $method\n";
        #print_r($args);
        $port = $this->wsdl->getPortForOperation($method);
        $this->endpoint = $this->wsdl->getPortEndpoint($port);
        
        $qrn = $this->wsdl->getResultNameForMethod($method);
        $resultName = $qrn->name;

        $url = parse_url($this->endpoint);

        $request = SOAP_Envelope::request($this->wsdl);
        $request->addMethod($method, $args);
        $data = $request->saveXML();
        
        $headers = "User-Agent: PEAR-SOAP 0.7.2-devel\r\n".
            "Content-Type: text/xml; charset={$this->_encoding}\r\n".
            "Content-Length: ".strlen($data)."\r\n".
            "SOAPAction: \"{$request->soapAction}\"\r\n";

        $opts = array(
          $url['scheme'] => array(
            'method' => 'POST',
            'header' => $headers,
            'content' => $data
          )
        );

        #print "Send to {$this->endpoint}\n";
        #print_r($opts);
        
        // XXX using domdocument::load here causes dom to crash
        return SOAP_Envelope::parse($this->endpoint, $opts);
    }

    public function __set($property, $value) {
        $setter = "__set_$property";
        if (in_array($setter,get_class_methods($this))) {
            $this->$setter($value);
            return true;
        }
        throw new Exception("$property is not a settable property of ".get_class($this));
    }

    public function __get($property) {
        $getter = "__get_$property";
        if (in_array($getter,get_class_methods($this))) {
            return $this->$getter($value);
        }
        $getter = "_$property";
        if (isset($this->$getter)) {
            return $this->$getter;
        }
        throw new Exception("$property is not a gettable property of ".get_class($this));
    }
    
    private function __set_encoding($value) {
        if (SOAP_Configuration::supportedEncoding($value)) {
            $this->_encoding = $encoding;
            return NULL;
        }
        throw new Exception('Invalid Encoding');
    }
}

/*
class SOAPStruct implements SchemaTypeInfo {
    public $varInt = 123;
    public $varFloat = 123.123;
    public $varString = 'hello world';

    public static function getTypeName() { return 'SOAPStruct'; }
    public static function getTypeNamespace() { return 'http://soapinterop.org/xsd'; }
}

try {
    $t = new SOAP_Client('http://localhost/soap_interop/wsdl/interop.wsdl.php',true);
    $ret = $t->echoString('arg1');
    var_dump($ret);
    $ret = $t->echoStruct(new SOAPStruct);
    var_dump($ret);
} catch(Exception $e) {
    print_r($e);
}
*/
?>