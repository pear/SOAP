<?php
require_once 'util.php';
require_once 'NamespaceRegistry.php';

interface SchemaTypeInfo {
    /* returns a Schema URI for this object
     * string getTypeNamespace()
     */
    public static function getTypeName();
    public static function getTypeNamespace();
}

interface SchemaSerializable {
    /* returns a domnode of the serialized object
     * domelement schemaSerialize() 
     */
    public function schemaSerialize();
    /* returns a php instance of the deserialized object
     * mixed schemaDeserialize() 
     */
    public function schemaDeserialize();
}

class SchemaException extends Exception {}

class SchemaSimple {
    /* this is the type mapping from XMLSchema types to PHP types */
    static public $schemaTypeMap = array(
        'http://www.w3.org/2001/XMLSchema' => array(
            'string' => 'string',
            'boolean' => 'boolean',
            'float' => 'float',
            'double' => 'double',
            'decimal' => 'float',
            'duration' => 'string',
            'dateTime' => 'string',
            'time' => 'string',
            'date' => 'string',
            'gYearMonth' => 'string',
            'gYear' => 'string',
            'gMonthDay' => 'string',
            'gDay' => 'string',
            'gMonth' => 'string',
            'hexBinary' => 'string',
            'base64Binary' => 'string',
            // derived datatypes
            'normalizedString' => 'string',
            'token' => 'string',
            'language' => 'string',
            'NMTOKEN' => 'string',
            'NMTOKENS' => 'string',
            'Name' => 'string',
            'NCName' => 'string',
            'ID' => 'string',
            'IDREF' => 'string',
            'IDREFS' => 'string',
            'ENTITY' => 'string',
            'ENTITIES' => 'string',
            'integer' => 'integer',
            'nonPositiveInteger' => 'integer',
            'negativeInteger' => 'integer',
            'long' => 'integer',
            'int' => 'integer',
            'short' => 'integer',
            'byte' => 'string',
            'nonNegativeInteger' => 'integer',
            'unsignedLong' => 'integer',
            'unsignedInt' => 'integer',
            'unsignedShort' => 'integer',
            'unsignedByte' => 'integer',
            'positiveInteger'  => 'integer',
            'anyType' => 'string',
            'anyURI' => 'string',
            'QName' => 'string'
        ),
        'http://www.w3.org/1999/XMLSchema' => array(
            'i4' => 'integer',
            'int' => 'integer',
            'boolean' => 'boolean',
            'string' => 'string',
            'double' => 'float',
            'float' => 'float',
            'dateTime' => 'string',
            'timeInstant' => 'string',
            'base64Binary' => 'string',
            'base64' => 'string',
            'ur-type' => 'string'
        ),
    );

    static public $phpTypeMap = array(
            'object' => array('xsd','Struct'),
            'array' => array('soap-enc','Array'),
            'string' => array('xsd','string'),
            'boolean' => array('xsd','boolean'),
            'double' => array('xsd','double'),
            'float' => array('xsd','float'),
            'integer' => array('xsd','int'),
            'long' => array('xsd','int'),
            'map' => array('apachens','Map'),
            'anyType' => array('xsd','anyType'),
            'NULL' => array('',''),
    );
    
    /* gives us a PHP type for the schema namespace and type */
    static public function getPHPTypeFromSimpleType($namespace, $name) {
        if (array_key_exists($namespace, self::$schemaTypeMap) &&
            array_key_exists($name, self::$schemaTypeMap[$namespace])) {
                return self::$schemaTypeMap[$namespace][$name];
        }
        return NULL;
        // throw SchemaException("{$namespace}$name is not a base Schema type");
    }
    
    static public function getSimpleTypeFromPHPType($var, $XMLSchemaVersion='http://www.w3.org/2001/XMLSchema') {
        $type = gettype($var);
        $schemaTypes = array_flip(self::$schemaTypeMap[$XMLSchemaVersion]);
        
        if (array_key_exists($type, $schemaTypes)) {
                return $schemaTypes[$type];
        }
        return NULL;
    }

    static public function domDeserialize($node) {
        #DOMDump::printNode($node);
        $type = $node->hasAttribute('type')?new QName($node->getAttribute('type')):'';
        $name = $node->localName;
        # this is lame, we have different types of nodes
        # but we're only interested in elements
        $children = array();
        foreach ($node->childNodes as $cnode) {
            if ($cnode instanceof domelement) {
                $children[] = $cnode;
            }
        }
        $numChildren = count($children);
        if ($numChildren > 0) {
            // is this a map value?
            if ($numChildren == 2 &&
                $children[0]->localName == 'key' &&
                $children[1]->localName == 'value') {
                    $val = self::domDeserialize($children[1]);
            } else {
                // it's an array or an object.  if it is an array,
                // then all children will have the same localName
                // as the first node, lets figure that out first.
                // we have to figure out what to do if there is
                // a single child, that could be either an array
                // or an object, just don't know
                $tagName = $children[0]->tagName;
                $localName = $children[0]->localName;
                $childNodes = $node->getElementsByTagName($tagName);
                if (count($childNodes) == $numChildren) {
                    // well, it's an array!
                    $val = array();
                    foreach ($children as $cnode) {
                        $value = self::domDeserialize($cnode);
                        if ($cnode->localName == $localName) {
                            $val[] = $value;
                        } else {
                            // it's actually a map array
                            $val[$cnode->localName] = $value;
                        }
                    }
                } else {
                    // lets make it an object
                    if ($type && class_exists($type->name)) {
                        $val = new $type->name;
                    } else {
                        $val = new stdClass;
                    }
                    foreach ($children as $cnode) {
                        $value = self::domDeserialize($cnode);
                        $n = $cnode->localName;
                        $val->$n = $value;
                    }
                }
            }
        } else {
            $val = $node->nodeValue;
            if ($type) {
                if (!$type->namespace) {
                    $type->namespace = $node->lookupNamespaceUri($type->prefix);
                }
                $phpType = self::getPHPTypeFromSimpleType($type->namespace, $type->name);
                if ($phpType) {
                    settype($val, $phpType);
                }
            }
        }
        return $val;
    }
    
    static public function domSerialize($doc, $varname, $vartype, $var, $encoded = false) {
        $q = new QName($varname);
        $type = gettype($var);
        
        if ($q->namespace) {
            #if (!$q->prefix) {
            #    $q->prefix = Namespace_Registry::register($q->namespace);
            #}
            #if (!$doc->documentElement->hasAttribute("xmlns:{$q->prefix}"))
            #    $doc->documentElement->setAttribute("xmlns:{$q->prefix}",$q->namespace);
            #$val = $doc->createElement("{$q->prefix}:{$q->name}");
            $val = $doc->createElementNS($q->namespace ,$q->name);
        } else {
            $val = $doc->createElement($q->name);
        }
        
        switch ($type) {
        case 'object':
            $classname = get_class($var);
            // does the class implement it's own serialization?
            if ($var instanceof SchemaSerializable) {
                $val = $var->schemaSerialize();
                break;
            }
            if ($encoded && $var instanceof SchemaTypeInfo) {
                $type = $var->getTypeName();
                $typeNS = $var->getTypeNamespace();
                $typePrefix = Namespace_Registry::register($typeNS);
                if ($doc->documentElement) {
                    $doc->documentElement->setAttribute("xmlns:$typePrefix",$typeNS);
                } else {
                    $val->setAttribute("xmlns:$typePrefix",$typeNS);
                }
                $vartype = "$typePrefix:$type";
            }
            if (method_exists($var, 'toString')) {
                if ($encoded && $type == 'object') {
                    $type = 'string';
                }
                $text = $doc->createTextNode((string)$var);
                $val->appendChild($text);
                break;
            }
            // XXX is get_class_vars public vars only?  That's what I hope!
            $classvars = get_class_vars($classname);
            foreach ($classvars as $classvarname => $classvar) {
                $node = self::domSerialize($doc, $classvarname, NULL, $var->$classvarname, $encoded);
                $val->appendChild($node);
            }
            break;
        case 'array':
            if (self::isHash($var)) {
                // regular array
                $type = 'map';
                foreach ($var as $key => $value) {
                    $item = $doc->createElement('item');
                    $text = $doc->createTextNode($key);
                    $keyval = $doc->createElement('key');
                    $keyval->appendChild($text);
                    $node = self::domSerialize($doc, 'value', NULL, $value, $encoded);
                    $item->appendChild($keyval);
                    $item->appendChild($node);
                    $val->appendChild($item);
                }
            } else {
                // regular array
                $arraySize = count($var);
                $arrayType = null;
                foreach ($var as $item) {
                    if ($encoded) {
                        $itemType = gettype($item);
                        if ($itemType != $arrayType) {
                            if ($arrayType) {
                                $arrayType = 'anyType';
                            } else {
                                $arrayType = $itemType;
                            }
                        }
                    }
                    $node = self::domSerialize($doc, 'item', NULL, $item, $encoded);
                    $val->appendChild($node);
                }
                if ($encoded) {
                    $typeinfo = self::$phpTypeMap[$arrayType];
                    $val->setAttribute('soap-enc:arrayType',"$typeinfo[0]:$typeinfo[1]"."[$arraySize]");
                    $val->setAttribute('soap-enc:offset','[0]');
                }
            }
            break;
        default:
            $text = $doc->createTextNode($var);
            $val->appendChild($text);
            break;
        }
        if ($encoded) {
            if ($vartype) {
                $val->setAttribute('xsi:type',$vartype);
            } else {
                $typeinfo = self::$phpTypeMap[$type];
                $val->setAttribute('xsi:type',"$typeinfo[0]:$typeinfo[1]");
            }
        }
        return $val;
    }
    
    /**
    *
    * @param    mixed
    * @return   boolean
    */
    static public function isHash(&$a) {
        # XXX I realy dislike having to loop through this in php code,
        # realy large arrays will be slow.  We need a C function to do this.
        $it = 0;
        foreach ($a as $k => $v) {
            # checking the type is faster than regexp.
            $t = gettype($k);
            if ($t != 'integer') {
                return TRUE;
            }
            // if someone has a large hash they should realy be defining the type
            if ($it++ > 10) return FALSE;
        }
        return FALSE;
    }    
}

class Schema {
    public $xpath;
    public $node;
    public $targetNamespace;
    
    /* these are the supported XML Schema versions */
    static private $XMLSchemaSupported
                = array('http://www.w3.org/2001/XMLSchema',
                        'http://www.w3.org/1999/XMLSchema');
    private $XMLSchemaVersion = 'http://www.w3.org/2001/XMLSchema';

    public function __construct(domelement $domnode) {
        if (!in_array($domnode->namespaceURI,self::$XMLSchemaSupported)) {
            throw SchemaException('Node is not a supported schema version!');
        }
        $this->node = $domnode;
        $this->XMLSchemaVersion = $domnode->namespaceURI;
        $this->targetNamespace = $domnode->getAttribute('targetNamespace');
        $this->xpath = new domXpath($this->node->ownerDocument);
        $this->xpath->register_namespace('xsd',$this->XMLSchemaVersion);
    }
    
    public function generateDataType(domelement $type) {
        $elements = array();
        $typeNodes = $type->childNodes;
        foreach ($typeNodes as $typeNode) {
            switch ($typeNode->localName) {
            case 'all':
                $nodes = $typeNode->childNodes;
                foreach ($nodes as $node) {
                    if ($node->localName == '#text') continue;
                    $dType = new QName($node->getAttribute('type'));
                    if ($dType->prefix) {
                        $dType->namespace = $type->lookupNamespaceUri($dType->prefix);
                    } else {
                        $dType->prefix = 'xsd';
                        $dType->namespace = $this->XMLSchemaVersion;
                    }
                    $elements[$node->getAttribute('name')] = $dType;
                }
            default:
                continue;
            }
        }
        $name = $type->getAttribute('name');
        $dtText = "class $name implements SchemaTypeInfo {\n\n";
        foreach ($elements as $dtName => $dtType) {
            $dtText .= "    /* {{$dtType->namespace}}{$dtType->name} */\n";
            $dtText .= "    public \$$dtName;\n\n";
        }
        $dtText .= "    public function getTypeName() { return '$name'; }\n\n";
        $dtText .= "    public function getTypeNamespace() { return '{$this->targetNamespace}'; }\n\n";
        $dtText .= "}\n\n";
        return $dtText;
    }
    
    public function domSerialize($doc, $varname, $vartype, $var, $encoded = false) {
        $node = SchemaSimple::serialize($doc, $varname, $vartype, $var, $encoded);
        //$this->validate($node);
    }
    
    public function generateDataTypes() {
        $types = $this->xpath->query('//xsd:schema/xsd:complexType');
        $dataTypes = '';
        foreach ($types as $type) {
            $dataTypes .= $this->generateDataType($type);
        }
        return $dataTypes;
    }
    
    public function validate($node)
    {
        // we have to make two new documents, one an actual domdocument
        // which starts the tree at $node, and another being a dump
        // of xml from the schema, which may or may not be it's own
        // document.
        
        // first get a new document and clone the node
        if ($node->isSameNode($node->ownerDocument->documentElement)) {
            $xmldoc = $this->node->ownerDocument;
        } else {
            $xmldoc = new domdocument;
            $xmldoc->appendChild($xmldoc->importNode($node->cloneNode(true), true));
            $xmldoc->normalize();
        }
        
        // next get the schema document
        if ($this->node->isSameNode($this->node->ownerDocument->documentElement)) {
            $schemaXML = $this->node->ownerDocument->saveXML();
        } else {
            $schemadoc = new domdocument;
            $schemadoc->appendChild($schemadoc->importNode($this->node->cloneNode(true), true));
            $ns = $this->node->lookupPrefix(Namespace_Registry::$namespaces['soap-enc']);
            $schemadoc->documentElement->setAttribute("xmlns:$ns",Namespace_Registry::$namespaces['soap-enc']);
            $schemadoc->documentElement->setAttribute("xmlns:xsd",Namespace_Registry::$namespaces['xsd']);
            $schemadoc->normalize();
            $schemaXML = $schemadoc->saveXML();
        }
        print $schemaXML;
        return $xmldoc->schemaValidateSource($schemaXML);
    }
}

class SchemaManager {
    static $_cache = array();
    
    static public function fetch($uri) {
        $schemaObj = new domDocument;
        if (!$schemaObj->load($uri)) {
            throw new SchemaException("Unable to load schema $uri");
        }
        
        SchemaManager::add(new Schema($schemaObj->documentElement));
        return $schemaObj;
    }
    
    static public function add(Schema $schemaObj) {
        SchemaManager::$_cache[$schemaObj->targetNamespace] = $schemaObj;
    }
    
    static public function get($namespace) {
        return SchemaManager::$_cache[$namespace];
    }
    
    public function findElement($namespace,$name) {
        if (array_key_exists($namespace,self::$_cache)) {
            $elements = self::$_cache[$namespace]->xpath->query("//xsd:schema/xsd:element[@name='$name']");
            return $elements[0]; // should never have more than one!
        }
        return NULL;
    }
}
/*
$xml = '<?xml version="1.0"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<SOAP-ENV:Body>
<m:echoStringResponse xmlns:m="http://soapinterop.org/">
<outputString xsi:type="xsd:string">Hello World</outputString>
</m:echoStringResponse>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
$doc = new DOMDocument;
$cod->preserveWhiteSpace = 0;
$doc->loadXML($xml);
$xpath = new domXpath($doc);
$xpath->register_namespace('soap-env',Namespace_Registry::$namespaces['soap-env']);
$node = $xpath->query('//soap-env:Envelope/soap-env:Body/*');
unset($xpath);
$value = SchemaSimple::domDeserialize($node[0]);
print "\n\nAnd the value is...\n";
print_r($value);
*/
?>