<?php

class Namespace_Registry {
        
    /* permanent namespaces defined in various specs */
    static public $namespaces = array (
        'xsd'       => 'http://www.w3.org/2001/XMLSchema',
        'xsi'       => 'http://www.w3.org/2001/XMLSchema-instance',
        'xsd-99'    => 'http://www.w3.org/1999/XMLSchema',
        
        'soap'      => 'http://schemas.xmlsoap.org/wsdl/soap/',
        'soap-enc'  => 'http://schemas.xmlsoap.org/soap/encoding/',
        'soap-env'  => 'http://schemas.xmlsoap.org/soap/envelope/',
        'soap-http' => 'http://schemas.xmlsoap.org/soap/http',

        'wsdl'      => 'http://schemas.xmlsoap.org/wsdl/',
        'wsdl-http' => 'http://schemas.xmlsoap.org/wsdl/http/',

        'disco'     => 'http://schemas.xmlsoap.org/disco/',
        'disco-scl' => 'http://schemas.xmlsoap.org/disco/scl/',
        
        'mime'      => 'http://schemas.xmlsoap.org/wsdl/mime/',
        'dime'      => 'http://schemas.xmlsoap.org/ws/2002/04/dime/wsdl/',

        'content'   => 'http://schemas.xmlsoap.org/ws/2002/04/content-type/',
        'ref'       => 'http://schemas.xmlsoap.org/ws/2002/04/reference/',
        
        'apachens'  => 'http://xml.apache.org/xml-soap',
        );

    static public $user_namespaces = array();
    static private $pcount = 1;
    
    static public function register($uri, $prefix = '') {
        if ($prefix &&
            array_key_exists($prefix, Namespace_Registry::$namespaces) &&
            Namespace_Registry::$namespaces[$prefix] == $uri)
            return $prefix;
        
        if (!$prefix) {
            $prefix = 'ns'.Namespace_Registry::$pcount++;
        }
        Namespace_Registry::$user_namespaces[$prefix] = $uri;
        return $prefix;
    }
    
    static public function getURI($prefix) {
        if (array_key_exists($prefix, Namespace_Registry::$namespaces))
            return Namespace_Registry::$namespaces[$prefix];
        if (array_key_exists($prefix, Namespace_Registry::$user_namespaces))
            return Namespace_Registry::$user_namespaces[$prefix];
        throw new Exception("$prefix is not a registered namespace");
    }

    static public function getPrefix($uri) {
        if (in_array($uri, array_values(Namespace_Registry::$namespaces))) {
            $ar = array_flip(Namespace_Registry::$namespaces);
            return $ar[$uri];
        }
        if (in_array($uri, array_values(Namespace_Registry::$user_namespaces))) {
            $ar = array_flip(Namespace_Registry::$user_namespaces);
            return $ar[$uri];
        }
        throw new Exception("$uri is not a registered namespace");
    }    
}

class Namespace {
    var $uri;
    var $prefix;
    
    public function __construct($uri, $prefix='') {
        $this->uri = $uri;
        $this->prefix = $prefix;
        $prefix = Namespace_Registry::register($uri, $prefix);
        if (!$this->prefix)
            $this->prefix = $prefix;
    }
    
    public function __toString() {
        return $this->uri;
    }
}


?>