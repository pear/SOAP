<?php
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
require_once 'SOAP/Base.php';
require_once 'SOAP/globals.php';

/**
*  SOAP_WSDL
*  this class parses wsdl files, and can be used by SOAP::Client to properly register
* soap values for services
*
* originaly based on SOAPx4 by Dietrich Ayala http://dietrich.ganx4.com/soapx4
*
* TODO:
*    add wsdl caching
*    implement IDL type syntax declaration so we can generate WSDL
*
* @access public
* @version $Id$
* @package SOAP::Client
* @author Shane Caraveo <shane@php.net> Conversion to PEAR and updates
* @author Dietrich Ayala <dietrich@ganx4.com> Original Author
*/
class SOAP_WSDL extends SOAP_Base
{
    var $tns = NULL;
    var $definition = array();
    var $namespaces = array();
    var $complexTypes = array();
    var $messages = array();
    var $portTypes = array();
    var $bindings = array();
    var $ports = array();
    var $imports = array();
    
    function SOAP_WSDL($uri=false) {
        parent::SOAP_Base('WSDL');
        $this->uri = $uri;
        $this->parse($uri);
    }

    function parse($uri) {
        $parser = new SOAP_WSDL_Parser($uri, $this);
        if ($parser->fault) {
            $this->raiseSoapFault($parser->fault);
        }
    }
    
    function getEndpoint($portName)
    {
        if ($endpoint = $this->ports[$portName]['location']) {
            return $endpoint;
        }
        return $this->raiseSoapFault("no endpoint for port for $portName",$this->uri);
    }
    
    // find the name of the first port that contains an operation of name $operation
    function getPortName($operation)
    {
        foreach($this->ports as $port => $portAttrs) {
            if ($this->bindings[$portAttrs['binding']]['operations'][$operation] != '') {
                return $port;
            }
        }
        return $this->raiseSoapFault("no operation $operation in wsdl", $this->uri);
    }
    
    function getOperationData($portName,$operation)
    {
        if ($binding = $this->ports[$portName]['binding']) {
            // get operation data from binding
            if (is_array($this->bindings[$binding]['operations'][$operation])) {
                $opData = $this->bindings[$binding]['operations'][$operation];
            }
            // get operation data from porttype
            $portType = $this->bindings[$binding]['type'];
            if (!$portType) {
                return $this->raiseSoapFault("no port type for binding $binding in wsdl ".$this->uri);
            }
            if (is_array($this->portTypes[$portType][$operation])) {
                $opData['parameterOrder'] = $this->portTypes[$portType][$operation]['parameterOrder'];
                $opData['input'] = array_merge($opData['input'],$this->portTypes[$portType][$operation]['input']);
                $opData['output'] = array_merge($opData['output'],$this->portTypes[$portType][$operation]['output']);
            }
            // message data from messages
            $inputMsg = $opData['input']['message'];
            $opData['input']['parts'] = $this->messages[$inputMsg];
            $outputMsg = $opData['output']['message'];
            $opData['output']['parts'] = $this->messages[$outputMsg];
            return $opData;
        }
        return $this->raiseSoapFault("no binding for port $portName in wsdl", $this->uri);
    }
    
    function getSoapAction($portName,$operation)
    {
        if ($soapAction = $this->bindings[$this->ports[$portName]['binding']]['operations'][$operation]['soapAction']) {
            return $soapAction;
        }
        return false;
    }
    
    function getNamespace($portName,$operation)
    {
        if ($namespace = $this->bindings[$this->ports[$portName]['binding']]['operations'][$operation]['input']['namespace']) {
            return $namespace;
        }
        return false;
    }
}

class SOAP_WSDL_Parser extends SOAP_Base
{
    // define internal arrays of bindings, ports, operations, messages, etc.
    var $currentMessage;
    var $currentOperation;
    var $currentPortType;
    var $currentBinding;
    var $currentPort;
    // parser vars
    var $position;
    var $depth;
    var $depth_array = array();
    var $tns = NULL;
    var $soapns = 'soap';
    var $uri = '';
    
    
    // constructor
    function SOAP_WSDL_Parser($uri, &$wsdl) {
        parent::SOAP_Base('WSDLPARSER');
        $this->uri = $uri;
        $this->wsdl = &$wsdl;
        $this->parse($uri);
    }
    
    function parse($uri) {
        // Check whether content has been read.
        // XXX implement caching
        $fd = @file($uri);
        if (!$fd) {
            return $this->raiseSoapFault('Unable to retreive WSDL file', $uri);
        }
        $wsdl_string = join('',$fd);
        // Create an XML parser.
        $parser = xml_parser_create();
        // Set the options for parsing the XML data.
        //xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        // Set the object for the parser.
        xml_set_object($parser, $this);
        // Set the element handlers for the parser.
        xml_set_element_handler($parser, 'startElement','endElement');
        xml_set_character_data_handler($parser,'characterData');
        //xml_set_default_handler($this->parser, 'defaultHandler');
    
        // Parse the XML file.
        if (!xml_parse($parser,$wsdl_string,true)) {
            $detail = sprintf('XML error on line %d: %s',
                                    xml_get_current_line_number($parser),
                                    xml_error_string(xml_get_error_code($parser)));
            return $this->raiseSoapFault("Unable to parse WSDL file $uri",$detail);
        }
        xml_parser_free($parser);
        return TRUE;
    }
    
    // start-element handler
    function startElement($parser, $name, $attrs) {
        // position in the total number of elements, starting from 0
        
        $pos = $this->position++;
        $depth = $this->depth++;
        // set self as current value for this depth
        $this->depth_array[$depth] = $pos;
        
        // get element prefix
        if (strstr($name,':')) {
            $s = split(':',$name);
            $ns = $s[0];
            if ($ns && ((!$this->tns && strcasecmp($s[1],'definitions')==0) || $ns == $this->tns)) {
                $name = $s[1];
            }
        }
        
        // find status, register data
        switch($this->status) {
        case 'types':
            switch($name) {
            case 'schema':
                $this->schema = true;
            break;
            case 'complexType':
                $this->currentElement = $attrs['name'];
                $this->schemaStatus = 'complexType';
            break;
            case 'element':
                $this->wsdl->complexTypes[$this->currentElement]['elements'][$attrs['name']] = $attrs;
            break;
            case 'complexContent':
                    
            break;
            case 'restriction':
                $this->wsdl->complexTypes[$this->currentElement]['restrictionBase'] = $attrs['base'];
            break;
            case 'sequence':
                $this->wsdl->complexTypes[$this->currentElement]['order'] = 'sequence';
            break;
            case 'all':
                $this->wsdl->complexTypes[$this->currentElement]['order'] = 'all';
            break;
            case 'attribute':
                if ($attrs['ref']) {
                    $this->wsdl->complexTypes[$this->currentElement]['attrs'][$attrs['ref']] = $attrs;
                } elseif ($attrs['name']) {
                    $this->wsdl->complexTypes[$this->currentElement]['attrs'][$attrs['name']] = $attrs;
                }
            break;
            }
        break;
        case 'message':
            if ($name == 'part') {
                $this->wsdl->messages[$this->currentMessage][$attrs['name']] = $attrs['type'];
            }
        break;
        case 'portType':
            switch($name) {
            case 'operation':
                $this->currentOperation = $attrs['name'];
                $this->wsdl->portTypes[$this->currentPortType][$attrs['name']]['parameterOrder'] = $attrs['parameterOrder'];
            break;
            default:
                $this->wsdl->portTypes[$this->currentPortType][$this->currentOperation][$name]= $attrs;
                $qname = split(':',$attrs['message']);
                $qname = array_reverse($qname); // this way, the type will always be zero
                $this->wsdl->portTypes[$this->currentPortType][$this->currentOperation][$name]['message'] = $qname[0];
                $this->wsdl->portTypes[$this->currentPortType][$this->currentOperation][$name]['namespace'] = $qname[1];
            break;
            }
        break;
        case 'binding':
            switch($name) {
                case $this->soapns.':binding':
                    $this->wsdl->bindings[$this->currentBinding] = array_merge($this->wsdl->bindings[$this->currentBinding],$attrs);
                break;
                case 'operation':
                    $this->currentOperation = $attrs['name'];
                    $this->wsdl->bindings[$this->currentBinding]['operations'][$attrs['name']] = array();
                break;
                case $this->soapns.':operation':
                    $this->wsdl->bindings[$this->currentBinding]['operations'][$this->currentOperation]['soapAction'] = $attrs['soapAction'];
                break;
                case 'input':
                    $this->opStatus = 'input';
                case $this->soapns.':body':
                    $this->wsdl->bindings[$this->currentBinding]['operations'][$this->currentOperation][$this->opStatus] = $attrs;
                break;
                case 'output':
                    $this->opStatus = 'output';
                break;
            }
        break;
        case 'service':
            switch($name) {
            case 'port':
                $this->currentPort = $attrs['name'];
                $this->wsdl->ports[$attrs['name']] = $attrs;
                // XXX hack to deal with binding namespaces
                $qname = split(':',$attrs['binding']);
                $qname = array_reverse($qname); // this way, the type will always be zero
                $this->wsdl->ports[$attrs['name']]['binding'] = $qname[0];
                $this->wsdl->ports[$attrs['name']]['namespace'] = $qname[1];
            break;
            case $this->soapns.':address':
                $this->wsdl->ports[$this->currentPort]['location'] = $attrs['location'];
            break;
            }
        case 'import':
            switch($name) {
            case 'documentation':
                $this->wsdl->imports[$attrs['namespace']]['documentation'] = $attrs;
            default:
                $this->debug('ERROR, only documentation allowed inside IMPORT\n');
            }
        break;
        }
        // set status
        switch($name) {
        case 'import':
            //XXX
            $import = '';
            if ($attrs['location']) {
                $result = $this->parse($attrs['location'], $this->wsdl);
                if (PEAR::isError($result)) {
                    return $result;
                }
            }
            
            $this->wsdl->imports[$attrs['namespace']] = array(
                        'location' => $attrs['location'],
                        'namespace' => $attrs['namespace']);
            $this->currentImport = $attrs['namespace'];
            $this->status = 'import';
        case 'types':
            $this->status = 'types';
        break;
        case 'message':
            $this->status = 'message';
            $this->wsdl->messages[$attrs['name']] = array();
            $this->currentMessage = $attrs['name'];
        break;
        case 'portType':
            $this->status = 'portType';
            $this->wsdl->portTypes[$attrs['name']] = array();
            $this->currentPortType = $attrs['name'];
        break;
        case 'binding':
            $this->status = 'binding';
            $this->currentBinding = $attrs['name'];
            $qname = split(':',$attrs['type']);
            $qname = array_reverse($qname); // this way, the type will always be zero
            $this->wsdl->bindings[$this->currentBinding]['type'] = $qname[0];
            $this->wsdl->bindings[$this->currentBinding]['namespace'] = $qname[1];
        break;
        case 'service':
            $this->wsdl->serviceName = $attrs['name'];
            $this->status = 'service';
        break;
        case 'definitions':
            $this->wsdl->definition = $attrs;
            foreach ($attrs as $name=>$value) {
                if (strcasecmp($value,SOAP_SCHEMA)==0) {
                    $s = split(':',$name);
                    $this->soapns = $s[1];
                    break;
                }
            }
            if ($ns) {
                $namespace = 'xmlns:'.$ns;
                if (!$this->wsdl->definition[$namespace]) {
                    return $this->raiseSoapFault("parse error, no namespace for $namespace",$this->uri);
                }
                $this->tns = $ns;
            }
        break;
        }
    }
    
    
    // end-element handler
    function endElement($parser, $name)
    {
        // position of current element is equal to the last value left in depth_array for my depth
        $pos = $this->depth_array[$this->depth];
        // bring depth down a notch
        $this->depth--;
    }
    
    // element content handler
    function characterData($parser, $data)
    {
        #$pos = $this->depth_array[$this->depth];
        #$this->wsdl->messages[$pos]['cdata'] .= $data;
    }
}

#$wsdl = new SOAP_WSDL("http://www.apache.org/~rubys/ApacheSoap.wsdl");
#print "end";
?>