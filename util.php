<?php

class DOMDump {
    public function printNode($node)
    {
        print "PHP Class Type:     " . get_class($node) . "\n";
        $classname = get_class($node);
        switch($classname) {
        case 'domdocument':
            print "Doc type:           $node->doctype\n";
            print "Doc implementation: $node->implementation\n";
            print "Doc actualEncoding: $node->actualEncoding\n";
            print "Doc encoding:       $node->encoding\n";
            print "Doc standalone:     $node->standalone\n";
            print "Doc version:        $node->version\n";
            print "Doc strictErrorChecking: $node->strictErrorChecking\n";
            print "Doc documentURI:    $node->documentURI\n";
            print "Doc config:         $node->config\n";
        case 'domelement':
            print "Element tagName:    $node->tagName\n";
        case 'domnode':
            print "Node Name:          $node->nodeName\n";
            print "Node Type:          $node->nodeType\n";
            print "Node localName:     $node->localName\n";
            print "Node prefix:        $node->prefix\n";
            print "Node namespaceURI:  $node->namespaceURI\n";
            print "Node baseURI:       $node->baseURI\n";
            $child_count = count($node->childNodes);
            print "Num Children:       $child_count\n";
            if($child_count <= 1){
                print "Node Value:         " . trim($node->nodeValue) . "\n";
                print "Node textContent:   " . trim($node->textContent) . "\n";
            }
            foreach ($node->attributes as $name => $attr) {
                print "  attr $name = $attr->nodeValue\n";
            }
        }
        
        print "\n\n";
    }
    
    public function printNodeList($nodelist)
    {
        foreach($nodelist as $node)
        {
            self::printNode($node);
        }
    }    
}

class URI {
    // $parsed is a parse_url() resulting array
    public function mergeURI($parsed,$path) {
        $parsed_path = parse_url($path);
        if (isset($parsed_path['scheme']))
            return $path;
        
        if (! is_array($parsed)) {
            $parsed = parse_url($parsed);
        }

        if (isset($parsed['scheme'])) {
            $sep = (strtolower($parsed['scheme']) == 'mailto' ? ':' : '://');
            $uri = $parsed['scheme'] . $sep;
        } else {
            $uri = '';
        }

        if (isset($parsed['pass'])) {
            $uri .= "$parsed[user]:$parsed[pass]@";
        } elseif (isset($parsed['user'])) {
            $uri .= "$parsed[user]@";
        }

        if (isset($parsed['host']))     $uri .= $parsed['host'];
        if (isset($parsed['port']))     $uri .= ":$parsed[port]";
        if ($path[0]!='/' && isset($parsed['path'])) {
            if ($parsed['path'][strlen($parsed['path'])-1] != '/') {
                $path = dirname($parsed['path']).'/'.$path;
            } else {
                $path = $parsed['path'].$path;
            }
            $path = URI::normalizeURI($path);
        }
        $sep = $path[0]=='/'?'':'/';
        $uri .= $sep.$path;

        return $uri;
    }

    public function normalizeURI($path_str){
        $pwd='';
        $strArr=preg_split("/(\/)/",$path_str,-1,PREG_SPLIT_NO_EMPTY);
        $pwdArr="";
        $j=0;
        for($i=0;$i<count($strArr);$i++){
            if($strArr[$i]!=".."){
                if($strArr[$i]!="."){
                $pwdArr[$j]=$strArr[$i];
                $j++;
                }
            }else{
                array_pop($pwdArr);
                $j--;
            }
        }
        $pStr=implode("/",$pwdArr);
        $pwd=(strlen($pStr)>0) ? ("/".$pStr) : "/";
        return $pwd;
    }
}

/**
*  QName
* class used to handle QNAME values in XML
*
* @access   public
* @version  $Id$
* @package  SOAP::Client
* @author   Shane Caraveo <shane@php.net> Conversion to PEAR and updates
*/
class QName
{
    public $name = '';
    public $prefix = '';
    public $namespace='';
    #var $arrayInfo = '';

    public function __construct($name, $namespace = '') {
        if ($name && $name[0] == '{') {
            preg_match('/\{(.*?)\}(.*)/',$name, $m);
            $this->name = $m[2];
            $this->namespace = $m[1];
        } else if (strpos($name, ':') != FALSE) {
            $s = split(':',$name);
            $s = array_reverse($s);
            $this->name = $s[0];
            $this->prefix = $s[1];
            $this->namespace = $namespace;
        } else {
            $this->name = $name;
            $this->namespace = $namespace;
        }
        
        # a little more magic than should be in a qname
        $p = strpos($this->name, '[');
        if ($p) {
            # XXX need to re-examine this logic later
            # chop off []
            $this->arraySize = split(',',substr($this->name,$p+1, strlen($this->name)-$p-2));
            $this->arrayInfo = substr($this->name, $p);
            $this->name = substr($this->name, 0, $p);
        }
    }

    public function longname() {
        return "{{$this->namespace}}{$this->name}";
    }

    public function fqn()
    {
        if ($this->prefix) {
            return $this->prefix.':'.$this->name;
        }
        return $this->name;
    }

}


?>