<?php
require_once 'util.php';
require_once 'NamespaceRegistry.php';
require_once 'Schema.php';

class WSDLException extends Exception {}

class WSDL {
    public $xpath;
    public $document;
    public $targetNamespace;
    public $targetNamespacePrefix;
    public $uri;

    private $imports = array();
    private $schema = array();
    
    public function __construct($uri) {
        $this->uri = $uri;
        $this->document = new domDocument;
        if (!$this->document->load($uri)) {
            throw new WSDLException("Failed to load dom $uri");
        }
        $this->targetNamespace = $this->document->documentElement->getAttribute('targetNamespace');
        $this->targetNamespacePrefix = $this->document->documentElement->lookupPrefix($this->targetNamespace);
        $this->xpath = new domXpath($this->document);
        foreach(Namespace_Registry::$namespaces as $ns => $nsuri) {
            $this->xpath->register_namespace($ns,$nsuri);
        }
    }
    
    public function addImport($wsdl) {
        $this->imports[] = $wsdl;
    }

    public function addSchema($schema) {
        $this->schema[] = $schema;
    }

    public function getSchema($recursive = true) {
        $schemas = $this->schema;
        foreach ($this->imports as $import) {
            $schemas = array_merge($schemas, $import->getSchema($recursive));
        }
        return $schemas;
    }
    
    public function lookupNamespaceUri($prefix) {
        $p = $this->document->documentElement->lookupNamespaceUri($prefix);
        if (!$p) {
            foreach ($this->imports as $import) {
                $p = $import->lookupNamespaceUri($prefix);
                if ($p) break;
            }
        }
        return $p;
    }
    
    // recurse into imported wsdls until we find something
    // if $all, then recurse until we've searched everything
    public function queryRecursive($query, $all = false) {
        $el = $this->xpath->query($query);
        if ($el && !$all) return $el;
        foreach ($this->imports as $wsdl) {
            $ele = $wsdl->queryRecursive($query);
            if ($ele && !$all)
                return $ele;
            if (!$el)
                $el = $ele;
            else
                $el = array_merge($el, $ele);
        }
        if ($el) return $el;
        return array();
        throw new WSDLException("XPath Query failure: $query");
    }

    public function getMessageParts($messageName) {
        return $this->queryRecursive("//wsdl:message[@name='$messageName']/wsdl:part");
    }

    public function getOperation($operation) {
        $opnodes = $this->queryRecursive("//wsdl:binding/wsdl:operation[@name='$operation']");
        return $opnodes[0];
    }

    public function getResultNameForMethod($method) {
        /* necessary for result name */
        $operation = $this->getOperation($method);
        $opdata = $this->getOperationData($operation);
        $output = $opdata->getElementsByTagNameNS(Namespace_Registry::$namespaces['wsdl'],'output');
        return new QName($output[0]->getAttribute('message'));
    }
    
    public function getOperationData($opnode) {
        $binding = $opnode->parentNode;
        $opname = $opnode->attributes['name']->nodeValue;
        $qname = new QName($binding->getAttribute('type'));
        $opdata = $this->queryRecursive("//wsdl:portType[@name='$qname->name']/wsdl:operation[@name='$opname']");
        return $opdata[0];
    }
    
    public function getPortTypeForBinding($binding) {
        $qname = new QName($binding->getAttribute('type'));
        $porttypes = $this->queryRecursive("//wsdl:portType[@name='$qname->name']");
        return $porttypes[0];
    }
    
    public function getBindingForPort($port) {
        $bindingName = new QName($port->getAttribute('binding'));
        $bindings = $this->queryRecursive("//wsdl:binding[@name='{$bindingName->name}']");
        return $bindings[0];
    }
    
    public function getPortForBinding($binding) {
        $bn = $binding->getAttribute('name');
        $ports = $this->queryRecursive("//wsdl:service/wsdl:port[@binding='{$this->targetNamespacePrefix}:{$bn}']");
        return $ports[0];
    }
    
    public function getPortForOperation($operation) {
        $opnode = $this->getOperation($operation);
        if (!$opnode) {
            throw new WSDLException("No bindings found for operation [$operation]");
        }
        $port = $this->getPortForBinding($opnode->parentNode);
        if (!$port) {
            throw new WSDLException("No port found for binding [{$opnode->parentNode->attributes['type']->value}]");
        }
        return $port;
    }
    
    public function getPortName($operation, $service = null) {
        $port = $this->getPortForOperation($operation);
        return $port->getAttribute('name');
    }
    
    public function getPortEndpoint($port)
    {
        $soapAddress = $port->getElementsByTagNameNS(Namespace_Registry::$namespaces['soap'],'address');
        return $soapAddress[0]->getAttribute('location');
    }
    
    public function serializeOperation($doc, $method, $args, $messageName = 'input') {
        $operation = $this->getOperation($method);

        $binding = $operation->parentNode;
        $bindingName = $binding->getAttribute('name');
        $bindingXPath = new domXpath($binding->ownerDocument);
        $bindingXPath->register_namespace('wsdl',Namespace_Registry::$namespaces['wsdl']);
        $bindingXPath->register_namespace('soap',Namespace_Registry::$namespaces['soap']);

        $soapOperation = $bindingXPath->query("//wsdl:binding[@name='$bindingName']/wsdl:operation[@name='$method']/soap:operation");
        $doc->soapAction = $soapOperation[0]->getAttribute('soapAction');
        
        $messageBody = $bindingXPath->query("//wsdl:binding[@name='$bindingName']/wsdl:operation[@name='$method']/wsdl:$messageName/soap:body");
        $messageEncoded = $messageBody[0]->getAttribute('use') == 'encoded';
        if ($messageEncoded) {
            $doc->documentElement->setAttribute('xmlns:xsd',Namespace_Registry::$namespaces['xsd']);
            $doc->documentElement->setAttribute('xmlns:xsi',Namespace_Registry::$namespaces['xsi']);
            $doc->documentElement->setAttribute('xmlns:soap-enc',Namespace_Registry::$namespaces['soap-enc']);
            $doc->documentElement->setAttribute('soap-env:encodingStyle',Namespace_Registry::$namespaces['soap-enc']);
        }
        
        #$portType = $this->getPortTypeForBinding($binding);
        #$portTypeXPath = new domXpath($portType->ownerDocument);
        $opdata = $this->getOperationData($operation);

        if ($messageName == 'input' &&
            $opdata->hasAttribute('parameterOrder')) {
            $parameterOrder = split(' ',$opdata->attributes['parameterOrder']->nodeValue);
        }
        #DOMDump::printNode($opdata);
        #print_r($parameterOrder);
        $message = $opdata->getElementsByTagNameNS(
                                Namespace_Registry::$namespaces['wsdl'],
                                $messageName);
        #DOMDump::printNode($input[0]);
        $opNS = new QName($message[0]->getAttribute('message'));
        if (!$opNS->namespace) {
            $opNS->namespace = $this->lookupNamespaceUri($opNS->prefix);
        }
        $docNSPrefix = $doc->lookupPrefix($opNS->namespace);
        if ($messageName != 'input') {
            $methodName = $opNS->name;
        } else {
            $methodName = $method;
        }
        if ($docNSPrefix) {
            $opNS->prefix = $docNSPrefix;
            $mNode = $doc->createElement("{$opNS->prefix}:$methodName");
        } else {
            $mNode = $doc->createElementNS($opNS->namespace,$methodName);
        }
        
        // get the matching message now
        $messageParts = $this->getMessageParts($opNS->name);
        if (isset($parameterOrder)) {
            foreach ($messageParts as $part) {
                $messageParts[$part->getAttribute('name')] = $part;
            }
        }
        
        $argCount = count($args);
        for ($i = 0; $i < $argCount; ++$i) {
            if (isset($parameterOrder)) {
                $paramName = $parameterOrder[$i];
                $part = $messageParts[$paramName];
            } else {
                $part = $messageParts[$i];
                $paramName = $part->getAttribute('name');
            }
            $paramType = $part->getAttribute('type');
            if (isset($args[$paramName])) {
                $paramVal = $args[$paramName];
            } else {
                $paramVal = $args[$i];
            }
            $argNode = SchemaSimple::domSerialize($doc, $paramName, $paramType, $paramVal, $messageEncoded);
            $mNode->appendChild($argNode);
        }
        return $mNode;
    }
    
    /**
     * generateProxyCode
     * generates stub code from the wsdl that can be saved to a file, or eval'd into existence
     */
    public function generateProxyCode($port = NULL, $classname = NULL)
    {
        $dataTypes = '';
        $schemas = $this->getSchema();
        foreach ($schemas as $schema) {
            $dataTypes .= $schema->generateDataTypes();
        }
        
        if (!$port) {
            $ports = $this->queryRecursive('//wsdl:service/wsdl:port');
            $port = $ports[0];
        }
        
        $serviceName = $port->parentNode->getAttribute('name');
        
        // XXX currentPort is BAD
        $clienturl = $this->getPortEndpoint($port);
        if (!$classname) {
            $classname = 'WebService_'.$serviceName.'_'.$port->getAttribute('name');
            $classname = str_replace('.','_',$classname);
        }

        $class =    "class $classname extends SOAP_Client\n{\n".
                    "    public function __construct()\n    {\n".
                    "        parent::__construct(\"$clienturl\", 0);\n".
                    "    }\n";

        // get the binding, from that get the port type
        $binding = $this->getBindingForPort($port);
        $bindingName = $binding->getAttribute('name');
        $temp = $binding->getElementsByTagNameNS(Namespace_Registry::$namespaces['soap'],'binding');
        $soapBinding = $temp[0];
        
        $bindingXPath = new domXpath($binding->ownerDocument);
        $bindingXPath->register_namespace('wsdl',Namespace_Registry::$namespaces['wsdl']);
        $bindingXPath->register_namespace('soap',Namespace_Registry::$namespaces['soap']);
        
        $portType = $this->getPortTypeForBinding($binding);
        $portTypeName = $portType->getAttribute('name');
        $operations = $portType->getElementsByTagNameNS(Namespace_Registry::$namespaces['wsdl'],'operation');
        $portTypeXPath = new domXpath($portType->ownerDocument);
        $portTypeXPath->register_namespace('wsdl',Namespace_Registry::$namespaces['wsdl']);
        $portTypeXPath->register_namespace('soap',Namespace_Registry::$namespaces['soap']);

        #$soapBinding = $bindingXPath->query("//wsdl:binding[@name='$bindingName']/soap:binding");
        #$operations = $portTypeXPath->query("//wsdl:portType[@name='$portTypeName']/wsdl:operation");

        // XXX currentPortType is BAD
        foreach ($operations as $operation) {
            $operationName = $operation->getAttribute('name');
            $soapOperation = $bindingXPath->query("//wsdl:binding[@name='$bindingName']/wsdl:operation[@name='$operationName']/soap:operation");
            $soapAction = $soapOperation[0]->getAttribute('soapAction');
        
            $operationStyle = $soapBinding->getAttribute('style');
            $inputBody = $bindingXPath->query("//wsdl:binding[@name='$bindingName']/wsdl:operation[@name='$operationName']/wsdl:input/soap:body");
            $inputUse = $inputBody[0]->getAttribute('use');
            $inputMessage = $portTypeXPath->query("//wsdl:portType[@name='$portTypeName']/wsdl:operation[@name='$operationName']/wsdl:input");
            if ($inputUse == 'encoded') {
                $namespace = $inputBody[0]->getAttribute('namespace');
            } else {
                $ns = new Qname($inputMessage[0]->getAttribute('message'));
                $namespace = $inputMessage[0]->lookupNamespaceUri($ns->prefix);
            }

            foreach ($inputMessage as $input) {
                $argArray = array();
                $inputType = new QName($input->getAttribute('message'));
                // get the matching message now
                $inputParts = $this->getMessageParts($inputType->name);
                foreach ($inputParts as $inputPart) {
                    $comments = '';
                    $argName = $inputPart->getAttribute('name');
                    if ($inputPart->hasAttribute('type')) {
                        $argType = new QName($inputPart->getAttribute('type'));
                    } else
                    if ($inputPart->getAttribute('element')) {
                        $argType = new QName($inputPart->getAttribute('element'));
                    }
                    $argType->namespace = $inputPart->lookupNamespaceUri($argType->prefix);
                    
                    if ($operationStyle == 'document' &&
                        $inputUse == 'literal' &&
                        $argName == 'parameters') {
                            
                        // find the element in the schema
                        $el = SchemaManager::findElement($argType->namespace,$argType->name);
                        $paramElements = $el->getElementsByTagNameNS(Namespace_Registry::$namespaces['xsd'],'element');
                        foreach ($paramElements as $paramEl) {
                            $paramName = $paramEl->getAttribute('name');
                            $paramType = new QName($paramEl->getAttribute('type'));
                            $paramType->namespace = $paramEl->lookupNamespaceUri($paramType->prefix);
                            $argArray[$paramName] = $paramType;
                        }
                    } else {
                        $argArray[$argName] = $argType;
                    }
                }
            }

            $operationArgs = array();
            $soapArgArray = array();
            foreach ($argArray as $argName => $argType) {
                $operationArgs[] = "/* {{$argType->namespace}}{$argType->name} */ \$$argName ";
                $soapArgArray[] = "'{{$argType->namespace}}{$argType->name}:$argName'=>\$$argName";
            }
            $operationArgsString = join($operationArgs,",\n        ");
            $soapArguments = join($soapArgArray,",\n                            ");

            $class .= "    function $operationName($operationArgsString) {\n".
            "        return \$this->call(\"$operationName\", \n".
            "                        \$v = array($soapArguments), \n".
            "                        array('namespace'=>'$namespace',\n".
            "                            'soapaction'=>'$soapAction',\n".
            "                            'style'=>'$operationStyle',\n".
            "                            'use'=>'$inputUse'".
            ($this->trace?",'trace'=>1":"")." ));\n".
            "    }\n";
        }
        $class .= "}\n";
        return "$dataTypes\n\n$class";
    }    
}

class WSDLManager {
    static $_cache = array();
    
    public function fetch($uri) {
        return new WSDL($uri);
    }
    
    public function add($wsdl) {
        $uri = $wsdl->document->documentURI;
        WSDLManager::parseImports($wsdl);
        WSDLManager::$_cache[$uri] = $wsdl;
    }
    
    public function get($uri) {
        if (array_key_exists($uri, WSDLManager::$_cache)) {
            return WSDLManager::$_cache[$uri];
        }
        $wsdl = WSDLManager::fetch($uri);
        $el = $wsdl->document->documentElement;
        if ($el->namespaceURI == Namespace_Registry::$namespaces['wsdl'] &&
            $el->tagName == 'definitions') {
            //$el->prefix = 'wsdl'; // force the prefix to what we want
            
            //$dom->createAttributeNS(Namespace_Registry::$namespaces['wsdl'],'wsdl');
            WSDLManager::add($wsdl);
        } else if ($el->namespaceURI == Namespace_Registry::$namespaces['xsd'] &&
            $el->localName == 'schema') {
            SchemaManager::add(new Schema($wsdl->document->documentElement));
            return null;
        }
        return $wsdl;
    }
    
    private function parseImports($wsdlObj) {
        $schemas = $wsdlObj->document->documentElement->getElementsByTagNameNS(Namespace_Registry::$namespaces['xsd'],'schema');
        foreach($schemas as $schema) {
            $s = new Schema($schema);
            SchemaManager::add($s);
            $wsdlObj->addSchema($s);
        }
        $imports = $wsdlObj->document->documentElement->getElementsByTagNameNS(Namespace_Registry::$namespaces['wsdl'],'import');
        $uri = $wsdlObj->document->documentURI;
        foreach ($imports as $import) {
            $iwsdl = WSDLManager::get(URI::mergeURI($uri,$import->getAttribute('location')));
            if ($iwsdl) {
                $wsdlObj->addImport($iwsdl);
            }
        }
    }
    
    
}


?>
