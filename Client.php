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

require_once 'SOAP/Value.php';
require_once 'SOAP/Base.php';
require_once 'SOAP/Transport.php';
require_once 'SOAP/Message.php';
require_once 'SOAP/WSDL.php';
require_once 'SOAP/Fault.php';

/**
*  SOAP Client Class
* this class is the main interface for making soap requests
*
* basic usage: 
*   $soapclient = new SOAP_Client( string path [ , boolean wsdl] );
*   echo $soapclient->call( string methodname [ , array parameters] );
*
* originaly based on SOAPx4 by Dietrich Ayala http://dietrich.ganx4.com/soapx4
*
* @access   public
* @version  $Id$
* @package  SOAP::Client
* @author   Shane Caraveo <shane@php.net> Conversion to PEAR and updates
* @author   Stig Bakken <ssb@fast.no> Conversion to PEAR
* @author   Dietrich Ayala <dietrich@ganx4.com> Original Author
*/
class SOAP_Client extends SOAP_Base
{
    /**
    * Communication endpoint.
    *
    * Currently the following transport formats are supported:
    *  - HTTP
    *  - SMTP
    * 
    * Example endpoints:
    *   http://www.example.com/soap/server.php
    *   https://www.example.com/soap/server.php
    *   mailto:soap@example.com
    *
    * @var  string
    * @see  SOAP_Client()
    */
    var $endpoint = '';
    
    /**
    * 
    */
    var $portName = '';
    
    
    /**
    * Endpoint type 
    *
    * @var  string  e.g. wdsl
    */
    var $endpointType = '';
    
    /**
    * WDSL object
    *
    * @var  object  SOAP_WDSL
    */
    var $wsdl = NULL;
    
    var $wire = NULL;
    
    var $soapmsg = NULL;
    
    var $encoding = SOAP_DEFAULT_ENCODING;
    /**
    * SOAP_Client constructor
    *
    * @param string endpoint (URL)
    * @param boolean wsdl (true if endpoint is a wsdl file)
    * @param string portName
    * @access public
    */
    function SOAP_Client($endpoint, $wsdl = false, $portName = false)
    {
        parent::SOAP_Base('Client');
        $this->endpoint = $endpoint;
        $this->portName = $portName;
        
        // make values
        if ($wsdl) {
            $this->endpointType = 'wsdl';
            // instantiate wsdl class
            $this->wsdl = new SOAP_WSDL($this->endpoint);
            if ($this->wsdl->fault) {
                $this->raiseSoapFault($this->wsdl->fault);
            }
        }
    }
    
    function setEncoding($encoding)
    {
        global $SOAP_Encodings;
        if (in_array($encoding, $SOAP_Encodings)) {
            $this->encoding = $encoding;
            return NULL;
        }
        return $this->raiseSoapFault('Invalid Encoding');
    }
    
    function addHeader($soap_value)
    {
        if (!$this->soapmsg) {
            soap_reset_namespaces();
            $this->soapmsg = new SOAP_Message(NULL, $this->wsdl);
        }
        # add a new header to the message
        if (get_class($soap_value) == 'soap_header') {
            $this->soapmsg->addHeader($soap_value);
        } else if (gettype($soap_value) == 'array') {
            // name, value, namespace, mustunderstand, actor
            $h = new SOAP_Header($soap_value[0], NULL, $soap_value[1], $soap_value[2], $soap_value[3], $soap_value[4]);
            $this->soapmsg->addHeader($h);
        } else {
            $this->raiseSoapFault("Don't understand the header info you provided.  Must be array or SOAP_Header.");
        }
    }
    
    /**
    * SOAP_Client::call
    *
    * @param string method
    * @param array  params
    * @param string namespace  (not required if using wsdl)
    * @param string soapAction   (not required if using wsdl)
    *
    * @return array of results
    * @access public
    */
    function call($method, $params = array(), $namespace = false, $soapAction = false)
    {
        $this->fault = null;

        // make message
        if (!$this->soapmsg) {
            soap_reset_namespaces();
            $this->soapmsg = new SOAP_Message(NULL, $this->wsdl);
        }

        if ($this->endpointType == 'wsdl') {
            $this->setSchemaVersion($this->wsdl->xsd);
            // get portName
            if (!$this->portName) {
                $this->portName = $this->wsdl->getPortName($method);
                if (PEAR::isError($this->portName)) {
                    return $this->raiseSoapFault($this->portName);
                }
            }
            $namespace = $this->wsdl->getNamespace($this->portName, $method);

            // get endpoint
            $this->endpoint = $this->wsdl->getEndpoint($this->portName);
            if (PEAR::isError($this->endpoint)) {
                return $this->raiseSoapFault($this->endpoint);
            }

            // get operation data
            $opData = $this->wsdl->getOperationData($this->portName, $method);
            if (PEAR::isError($opData)) {
                return $this->raiseSoapFault($opData);
            }
            $soapAction = $opData['soapAction'];

            // set input params
            $nparams = array();
            if (count($opData['input']['parts']) > 0) {
                $i = 0;
                reset($params);
                foreach ($opData['input']['parts'] as $name => $type) {
                    if (isset($params[$name])) {
                        $nparams[$name] = $params[$name];
                    } else {
                        // XXX assuming it's an array, not a hash
                        // XXX this is pretty pethetic, but "fixes" a problem where
                        // paremeter names do not match correctly
                        $nparams[$name] = current($params);
                        next($params);
                    }
                    if (gettype($nparams[$name]) != 'object' &&
                        get_class($nparams[$name]) != 'soap_value') {
                        // type is a qname likely, split it apart, and get the type namespace from wsdl
                        $qname = new QName($type);
                        if ($qname->ns) 
                            $type_namespace = $this->wsdl->namespaces[$qname->ns];
                        else
                            $type_namespace = NULL;
                        $type = $qname->name;
                        $nparams[$name] = new SOAP_Value($name, $qname->name, $nparams[$name], NULL, $type_namespace, $this->wsdl);
                    }
                }
            }
            $params = $nparams;
        } else {
            $this->setSchemaVersion(SOAP_XML_SCHEMA_VERSION);
        }
        
        $this->soapmsg->method($method, $params, $namespace);
        if ($this->soapmsg->fault) {
            return $this->raiseSoapFault($this->soapmsg->fault);
        }

        // serialize the message
        $soap_data = $this->soapmsg->serialize($this->encoding);

        if (PEAR::isError($soap_data)) {
            return $this->raiseSoapFault($soap_data);
        }
        
        // instantiate client
        $dbg = "calling server at '$this->endpoint'...";
        
        $soap_transport = new SOAP_Transport($this->endpoint, $this->encoding);
        
        if ($soap_transport->fault) {
            return $this->raiseSoapFault($soap_transport->fault);
        }
        
        // send the message
        $this->response = $soap_transport->send($soap_data, $soapAction);

        // save the wire information for debugging
        $this->wire = "OUTGOING:\n\n".
            $soap_transport->transport->outgoing_payload.
            "\n\nINCOMING\n\n".
            preg_replace("/>/",">\n",$soap_transport->transport->incoming_payload);
        // store the incoming xml for easy retreival by clients that want their own parsing
        $this->xml = $soap_transport->transport->incoming_payload;
        
        if ($soap_transport->fault) {
            return $this->raiseSoapFault($this->response);
        }

        // parse the response
        $this->response = new SOAP_Parser($this->response, $soap_transport->result_encoding);
        if ($this->response->fault) {
            return $this->raiseSoapFault($this->response->fault);
        }
        // return array of parameters
        $return = $this->response->getResponse();
        $headers = $this->response->getHeaders();
        if ($headers) {
            $this->headers = $headers->decode();
        }
        
        $this->soapmsg = NULL;        
        
        // check for valid response
        if (PEAR::isError($return)) {
            return $this->raiseSoapFault($return);
        } else if (strcasecmp(get_class($return), 'SOAP_Value') != 0) {
            return $this->raiseSoapFault("didn't get SOAP_Value object back from client");
        }

        // decode to native php datatype
        $returnArray = $return->decode();
        // fault?
        if (PEAR::isError($returnArray)) {
            return $this->raiseSoapFault($returnArray);
        }
        if (is_array($returnArray)) {
            if (isset($returnArray['faultcode']) || isset($returnArray['SOAP-ENV:faultcode'])) {
                foreach ($returnArray as $k => $v) {
                    if (stristr($k,'faultcode')) $faultcode = $v;
                    if (stristr($k,'faultstring')) $faultstring = $v;
                    if (stristr($k,'faultdetail')) $faultdetail = $v;
                    if (stristr($k,'faultactor')) $faultactor = $v;
                }
                return $this->raiseSoapFault($faultstring, $faultdetail, $faultactor, $faultcode);
            }
            // return array of return values
            if (count($returnArray) == 1) {
                return array_shift($returnArray);
            }
            return $returnArray;
        }
        return $returnArray;
    }

    function setSchemaVersion($schemaVersion)
    {
        global $SOAP_namespaces;
        $this->XMLSchemaVersion = $schemaVersion;
        $tmpNS = array_flip($SOAP_namespaces);
        $tmpNS['xsd'] = $this->XMLSchemaVersion;
        $tmpNS['xsi'] = $this->XMLSchemaVersion.'-instance';
        $SOAP_namespaces = array_flip($tmpNS);
    }
    
    /**
    * SOAP_Client::__call
    *
    * Overload extension support
    * if the overload extension is loaded, you can call the client class
    * with a soap method name
    * $soap = new SOAP_Client(....);
    * $value = $soap->getStockQuote('MSFT');
    *
    * @param string method
    * @param array  args
    * @param string retur_value
    *
    * @return boolean
    * @access public
    */
    function __call($method, $args, &$return_value)
    {
        if ($this->wsdl) $this->wsdl->matchMethod($method);
        $return_value = $this->call($method, $args);
        return TRUE;
    }
    
}

if (extension_loaded('overload')) {
    overload('SOAP_Client');
}
?>