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
require_once("SOAP/Parser.php");
require_once("SOAP/Value.php");

/**
*  SOAP Message Class
* this class serializes and deserializes soap messages for transport (see SOAP::Transport)
*
* originaly based on SOAPx4 by Dietrich Ayala http://dietrich.ganx4.com/soapx4
*
* @access public
* @version $Id$
* @package SOAP::Message
* @author Shane Caraveo <shane@php.net> Conversion to PEAR and updates
* @author Dietrich Ayala <dietrich@ganx4.com> Original Author
*/
class SOAP_Message
{
    /**
    * SOAP::Message constructor
    *
    * initializes a soap structure containing the method signature and parameters
    *
    * @param string $method       soap data (in xml)
    * @param array(SOAP::Value) $params       soap data (in xml)
    * @param string $method_namespace       soap data (in xml)
    * @param array of string $new_namespaces       soap data (in xml)
    *
    * @access public
    */
    function SOAP_Message($method,$params,$method_namespace="http://testuri.org",$new_namespaces=NULL)
    {
        // globalize method namespace
        global $methodNamespace;
        $methodNamespace = $method_namespace;
        // make method struct
        $this->value = new SOAP_Value($method,"struct",$params,$method_namespace);
        if (is_array($new_namespaces)) {
            global $SOAP_namespaces;
            $i = count($SOAP_namespaces);
            foreach ($new_namespaces as $v) {
                $SOAP_namespaces[$v] = "ns".$i++;
            }
            $this->namespaces = $SOAP_namespaces;
        }
        $this->payload = "";
        $this->debug_flag = false;
        $this->debug_str = "entering SOAP_Message() with SOAP_Value ".$this->value->name."\n";
    }
    
    /**
    * wraps the soap payload with the soap envelop data
    *
    * @param string $payload       soap data (in xml)
    *
    * @return string xml_soap_data
    * @access private
    */
    function _makeEnvelope($payload)
    {
        global $SOAP_namespaces;
        $ns_string = "";
        foreach ($SOAP_namespaces as $k => $v) {
            $ns_string .= "xmlns:$v=\"$k\"\n ";
        }
        return "<SOAP-ENV:Envelope $ns_string SOAP-ENV:encodingStyle=\"".SOAP_SCHEMA_ENCODING."\">\n".
                   $payload.
                   "</SOAP-ENV:Envelope>\n";
    }
    
    /**
    * wraps the soap body
    *
    * @param string $payload       soap data (in xml)
    *
    * @return string xml_soap_data
    * @access private
    */
    function _makeBody($payload)
    {
        return "<SOAP-ENV:Body>\n".$payload."</SOAP-ENV:Body>\n";
    }
    
    /**
    * creates an xml string representation of the soap message data
    *
    * @access private
    */
    function _createPayload()
    {
        $value = $this->value;
        $payload = $this->_makeEnvelope($this->_makeBody($value->serialize()));
        $this->debug($value->debug_str);
        $payload = "<?xml version=\"1.0\"?>\n\n".$payload;
        if ($this->debug_flag) {
            $payload .= $this->serializeDebug();
        }
        $this->payload = str_replace("\n","\r\n", $payload);
    }
    
    /**
    * serializes this classes data into xml
    *
    * @return string xml_soap_data
    * @access public
    */
    function serialize()
    {
        if ($this->payload == "") {
            $this->_createPayload();
            return $this->payload;
        }
        return $this->payload;
    }
    
    /**
    * parses a soap message
    *
    * @param string $data       soap message (in xml)
    *
    * @return SOAP::Value
    * @access public
    */
    function parseResponse($data)
    {
        $this->debug("Entering parseResponse()");
        $this->debug(" w/ data $data");
        $this->debug("about to create parser instance w/ data");
        // parse response
        $response = new SOAP_Parser($data);
        // return array of parameters
        $ret = $response->getResponse();
        $this->debug($response->debug_str);
        return $ret;
    }
    
    /**
    * maintains a string of debug data
    *
    * @params string data
    * @access private
    */
    function debug($string)
    {
        if ($this->debug_flag) {
            $this->debug_str .= "SOAP_Message: ".preg_replace("/>/","/>\r\n/",$string)."\n";
        }
    }
    
    /**
    * preps debug data for encoding into SOAP::Message
    *
    * @return string
    * @access private
    */
    function serializeDebug()
    {
        if ($this->debug_flag) {
            return "<!-- DEBUG INFO:\n".$this->debug_str."-->\n";
        }
        return "";
    }
}
?>