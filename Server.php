<?php
require_once 'util.php';
require_once 'WSDL.php';
require_once 'Client.php';

interface SOAP_Service {
    public static function getSOAPServiceNamespace();
    public static function getSOAPServiceName();
    public static function getSOAPServiceDescription();
    public static function getWSDLURI();
}

class SOAPServerException extends Exception {}

class SOAP_Server {
    private $dispatch_objects = array();
    
    public function service($source) {
        $request = SOAP_Envelope::parse($source);
        print $this->handleRequest($request)->saveXML();
    }
    
    public function handleRequest($request) {
        $children = array();
        foreach ($request->body->childNodes as $node) {
            if ($node instanceof domelement) {
                $methodNamespace = $node->namespaceURI;
                $methodName = $node->localName;
                $args = SchemaSimple::domDeserialize($node);
                $result = $this->callMethod($methodNamespace, $methodName, $args);
                break;
            }
        }

        return $this->generateResponse($methodNamespace, $methodName, $result);
    }
    
    public function generateResponse($methodNamespace, $methodName, $args) {
        $service = $this->getService($methodNamespace);
        $wsdlUri = $service->getWSDLURI();
        $wsdl = WSDLManager::get($wsdlUri);
        $request = SOAP_Envelope::request($wsdl);

        if (!$wsdl)
            $methodName = "\{$methodNamespace}{$methodName}";

        $request->addResponse($methodName, $args);
        return $request;
    }
    
    public function callMethod($methodNamespace, $methodName, $args) {
        $service = $this->getService($methodNamespace);
        if (!$service) {
            throw new SOAPServerException("no service for namespace $methodNamespace");
        }
        if (!method_exists($service, $methodName)) {
            throw new SOAPServerException("no method $methodName for service");
        }
        if ($args) {
            $ret = @call_user_func_array(array($service, $methodName),$args);
        } else {
            $ret = @call_user_func(array($service, $methodName));
        }
        return $ret;
    }
    
    public function getService($namespace) {
        return $this->dispatch_objects[$namespace];
    }
    
    public function addService($obj)
    {
        if (!($obj instanceof SOAP_Service)) {
            throw SOAPServerException('service object does not implement SOAP_Service interface!');
        }
        $namespace = $obj->getSOAPServiceNamespace();
        $this->dispatch_objects[$namespace] = $obj;
    }
}

?>
