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
require_once("SOAP/globals.php");

/**
*  SOAP::WSDL
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
class WSDL {
    // define internal arrays of bindings, ports, operations, messages, etc.
    var $complexTypes = array();
    var $messages = array();
    var $currentMessage;
    var $currentOperation;
    var $portTypes = array();
    var $currentPortType;
    var $bindings = array();
    var $currentBinding;
    var $ports = array();
    var $imports = array();
    var $currentPort;
    // debug switch
    var $debug_flag = false;
    // parser vars
    var $position;
    var $depth;
    var $depth_array = array();
    var $tns = NULL;
    var $soapns = "soap";

    // constructor
    function WSDL($wsdl=false) {
        $this->parse($wsdl);
    }
    
    function parse($wsdl) {
        // Check whether content has been read.
        if ($wsdl) {
            $wsdl_string = join("",file($wsdl));
            // Create an XML parser.
            $parser = xml_parser_create();
            // Set the options for parsing the XML data.
            //xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            // Set the object for the parser.
            xml_set_object($parser, $this);
            // Set the element handlers for the parser.
            xml_set_element_handler($parser, "start_element","end_element");
            xml_set_character_data_handler($parser,"character_data");
            //xml_set_default_handler($this->parser, "default_handler");
        
            // Parse the XML file.
            if (!xml_parse($parser,$wsdl_string,true)) {
                // Display an error message.
                $this->debug(sprintf("XML error on line %d: %s",
                xml_get_current_line_number($parser),
                xml_error_string(xml_get_error_code($parser))));
                $this->fault = true;
            }
            xml_parser_free($parser);
        }
    }
    
    // start-element handler
    function start_element($parser, $name, $attrs) {
        global $SOAPSchema;
        // position in the total number of elements, starting from 0
        
        $pos = $this->position++;
        $depth = $this->depth++;
        // set self as current value for this depth
        $this->depth_array[$depth] = $pos;
        
        // get element prefix
        if (strstr($name,":")) {
            $s = split(":",$name);
            $ns = $s[0];
            if ($ns && ((!$this->tns && strcasecmp($s[1],"definitions")==0) || $ns == $this->tns)) {
                $name = $s[1];
            }
        }
        
        // find status, register data
        switch($this->status) {
        case "types":
            switch($name) {
            case "schema":
                $this->schema = true;
            break;
            case "complexType":
                $this->currentElement = $attrs["name"];
                $this->schemaStatus = "complexType";
            break;
            case "element":
                $this->complexTypes[$this->currentElement]["elements"][$attrs["name"]] = $attrs;
            break;
            case "complexContent":
                    
            break;
            case "restriction":
                $this->complexTypes[$this->currentElement]["restrictionBase"] = $attrs["base"];
            break;
            case "sequence":
                $this->complexTypes[$this->currentElement]["order"] = "sequence";
            break;
            case "all":
                $this->complexTypes[$this->currentElement]["order"] = "all";
            break;
            case "attribute":
                if ($attrs["ref"]) {
                    $this->complexTypes[$this->currentElement]["attrs"][$attrs["ref"]] = $attrs;
                } elseif ($attrs["name"]) {
                    $this->complexTypes[$this->currentElement]["attrs"][$attrs["name"]] = $attrs;
                }
            break;
            }
        break;
        case "message":
            if ($name == "part") {
                $this->messages[$this->currentMessage][$attrs["name"]] = $attrs["type"];
            }
        break;
        case "portType":
            switch($name) {
            case "operation":
                $this->currentOperation = $attrs["name"];
                $this->portTypes[$this->currentPortType][$attrs["name"]]["parameterOrder"] = $attrs["parameterOrder"];
            break;
            default:
                $this->portTypes[$this->currentPortType][$this->currentOperation][$name]= $attrs;
            break;
            }
        break;
        case "binding":
            switch($name) {
                case $this->soapns.":binding":
                    $this->bindings[$this->currentBinding] = array_merge($this->bindings[$this->currentBinding],$attrs);
                break;
                case "operation":
                    $this->currentOperation = $attrs["name"];
                    $this->bindings[$this->currentBinding]["operations"][$attrs["name"]] = array();
                break;
                case $this->soapns.":operation":
                    $this->bindings[$this->currentBinding]["operations"][$this->currentOperation]["soapAction"] = $attrs["soapAction"];
                break;
                case "input":
                    $this->opStatus = "input";
                case $this->soapns.":body":
                    $this->bindings[$this->currentBinding]["operations"][$this->currentOperation][$this->opStatus] = $attrs;
                break;
                case "output":
                    $this->opStatus = "output";
                break;
            }
        break;
        case "service":
            switch($name) {
            case "port":
                $this->currentPort = $attrs["name"];
                $this->ports[$attrs["name"]] = $attrs;
            break;
            case $this->soapns.":address":
                $this->ports[$this->currentPort]["location"] = $attrs["location"];
            break;
            }
        case "import":
            switch($name) {
            case "documentation":
                $this->imports[$attrs["namespace"]]["documentation"] = $attrs;
            default:
                $this->debug("ERROR, only documentation allowed inside IMPORT\n");
            }
        break;
        }
        // set status
        switch($name) {
        case "import":
            //XXX
            $import = "";
            if ($attrs["location"]) {
                $this->parse($attrs["location"]);
            }
            
            $this->imports[$attrs["namespace"]] = array(
                        "location" => $attrs["location"],
                        "namespace" => $attrs["namespace"]);
            $this->currentImport = $attrs["namespace"];
            $this->status = "import";
        case "types":
            $this->status = "types";
        break;
        case "message":
            $this->status = "message";
            $this->messages[$attrs["name"]] = array();
            $this->currentMessage = $attrs["name"];
        break;
        case "portType":
            $this->status = "portType";
            $this->portTypes[$attrs["name"]] = array();
            $this->currentPortType = $attrs["name"];
        break;
        case "binding":
            $this->status = "binding";
            $this->currentBinding = $attrs["name"];
            $this->bindings[$attrs["name"]]["type"] = $attrs["type"];
        break;
        case "service":
            $this->serviceName = $attrs["name"];
            $this->status = "service";
        break;
        case "definitions":
            $this->wsdl_info = $attrs;
            foreach ($attrs as $name=>$value) {
                if (strcasecmp($value,$SOAPSchema)==0) {
                    $s = split(":",$name);
                    $this->soapns = $s[1];
                    break;
                }
            }
            if ($ns) {
                $namespace = "xmlns:".$ns;
                if (!$this->wsdl_info[$namespace]) {
                    $this->debug("WSDL Parse Error, no namespace for $namespace\n");
                    return;
                }
                $this->tns = $ns;
            }
        break;
        }
    }
    
    function getEndpoint($portName)
    {
        if ($endpoint = $this->ports[$portName]["location"]) {
            return $endpoint;
        }
        return false;
    }
    
    // find the name of the first port that contains an operation of name $operation
    function getPortName($operation)
    {
        foreach($this->ports as $port => $portAttrs) {
            $binding = substr($portAttrs["binding"],4);
            if ($this->bindings[$binding]["operations"][$operation] != "") {
                return $port;
            }
        }
    }
    
    function getOperationData($portName,$operation)
    {
        if ($binding = substr($this->ports[$portName]["binding"],4)) {
            // get operation data from binding
            if (is_array($this->bindings[$binding]["operations"][$operation])) {
                $opData = $this->bindings[$binding]["operations"][$operation];
            }
            // get operation data from porttype
            $portType = substr(strstr($this->bindings[$binding]["type"],":"),1);
            if (is_array($this->portTypes[$portType][$operation])) {
                $opData["parameterOrder"] = $this->portTypes[$portType][$operation]["parameterOrder"];
                $opData["input"] = array_merge($opData["input"],$this->portTypes[$portType][$operation]["input"]);
                $opData["output"] = array_merge($opData["output"],$this->portTypes[$portType][$operation]["output"]);
            }
            // message data from messages
            $inputMsg = substr(strstr($opData["input"]["message"],":"),1);
            $opData["input"]["parts"] = $this->messages[$inputMsg];
            $outputMsg = substr(strstr($opData["output"]["message"],":"),1);
            $opData["output"]["parts"] = $this->messages[$outputMsg];
        }
        return $opData;
    }
    
    function getSoapAction($portName,$operation)
    {
        if ($binding = substr($this->ports[$portName]["binding"],4)) {
            if ($soapAction = $this->bindings[$binding]["operations"][$operation]["soapAction"]) {
                return $soapAction;
            }
            return false;
        }
        return false;
    }
    
    function getNamespace($portName,$operation)
    {
        if ($binding = substr($this->ports[$portName]["binding"],4)) {
            //$this->debug("looking for namespace using binding '$binding', port '$portName', operation '$operation'");
            if ($namespace = $this->bindings[$binding]["operations"][$operation]["input"]["namespace"]) {
                return $namespace;
            }
            return false;
        }
        return false;
    }
    
    // end-element handler
    function end_element($parser, $name)
    {
        // position of current element is equal to the last value left in depth_array for my depth
        $pos = $this->depth_array[$this->depth];
        // bring depth down a notch
        $this->depth--;
    }
    
    // element content handler
    function character_data($parser, $data)
    {
        $pos = $this->depth_array[$this->depth];
        $this->message[$pos]["cdata"] .= $data;
    }
    
    function debug($string)
    {
        if ($this->debug_flag) {
            $this->debug_str .= "wsdl: $string\n";
        }
    }
}

#$wsdl = new wsdl("http://www.whitemesa.net/wsdl/std/interop.wsdl");
#print "end";
?>