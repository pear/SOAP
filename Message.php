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

require_once 'SOAP/globals.php';
require_once 'SOAP/Base.php';
require_once 'SOAP/Parser.php';
require_once 'SOAP/Value.php';
require_once 'SOAP/Header.php';

/**
*  SOAP Message Class
* this class serializes and deserializes soap messages for transport (see SOAP::Transport)
*
* originaly based on SOAPx4 by Dietrich Ayala http://dietrich.ganx4.com/soapx4
*
* @access   public
* @version  $Id$
* @package  SOAP::Message
* @author   Shane Caraveo <shane@php.net> Conversion to PEAR and updates
* @author   Dietrich Ayala <dietrich@ganx4.com> Original Author
*/
class SOAP_Message extends SOAP_Base
{
    
    /**
    * XML payload
    *
    * @var  string
    */
    var $payload = '';
    
    /**
    * List of namespaced
    *
    * @var  array
    */
    var $namespaces;
    
    /**
    * SOAP value
    * 
    * @var  object  SOAP_value
    */
    var $value = '';
    
    var $headers = array();
    
    /**
    * SOAP::Message constructor
    *
    * initializes a soap structure containing the method signature and parameters
    *
    * @param string $method                     soap data (in xml)
    * @param array(SOAP::Value) $params         soap data (in xml)
    * @param string $method_namespace           soap data (in xml)
    * @param array of string $new_namespaces    soap data (in xml)
    *
    * @access public
    */
    function SOAP_Message($new_namespaces = NULL, $wsdl = NULL)
    {
        parent::SOAP_Base('Message');
        $this->wsdl = $wsdl;
        if (is_array($new_namespaces)) {
            global $SOAP_namespaces;
            $i = count($SOAP_namespaces);
            foreach ($new_namespaces as $v) {
                $SOAP_namespaces[$v] = 'ns' . $i++;
            }
            $this->namespaces = $SOAP_namespaces;
        }
    }

    function addHeader($soap_value)
    {
	$this->headers[] = $soap_value;
    }
    
    function method($method, $params, $method_namespace = NULL)
    {
        // make method struct
        $this->value = new SOAP_Value($method, 'Struct', $params, $method_namespace, NULL, $this->wsdl);
    }
    
    /**
    * wraps the soap payload with the soap envelop data
    *
    * @param string $payload       soap data (in xml)
    * @return string xml_soap_data
    * @access private
    */
    function _makeEnvelope($header, $body)
    {
        global $SOAP_namespaces;

        $ns_string = '';

        foreach ($SOAP_namespaces as $k => $v) {
            $ns_string .= " xmlns:$v=\"$k\"\r\n ";
        }
        return "<?xml version=\"1.0\"?>\r\n\r\n<SOAP-ENV:Envelope $ns_string SOAP-ENV:encodingStyle=\"" . SOAP_SCHEMA_ENCODING . "\">\r\n$header$body</SOAP-ENV:Envelope>\r\n";
    }

    /**
    * wraps the soap Header
    *
    * @param string $payload       soap data (in xml)
    *
    * @return string xml_soap_data
    * @access private
    */
    function _makeHeader()
    {
	$payload = '';
	foreach ($this->headers as $header) {
	    $payload .= $header->serialize();
	}
	if (!$payload) return NULL;
        return "<SOAP-ENV:Header>\r\n$payload</SOAP-ENV:Header>\r\n";
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
        return "<SOAP-ENV:Body>\r\n$payload</SOAP-ENV:Body>\r\n";
    }
    
    /**
    * creates an xml string representation of the soap message data
    *
    * @access private
    */
    function _createPayload()
    {
	$body = $this->value?$this->_makeBody($this->value->serialize()):NULL;
	$header = count($this->headers)?$this->_makeHeader():NULL;
        $this->payload = $this->_makeEnvelope($header, $body);
    }
    
    /**
    * serializes this classes data into xml
    *
    * @return string xml_soap_data
    * @access public
    */
    function serialize()
    {
        if ($this->payload == '') {
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
        // parse response
        $response = new SOAP_Parser($data);
        // return array of parameters
        $ret = $response->getResponse();
        return $ret;
    }
    
}
?>
