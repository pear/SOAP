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
require_once 'SOAP/WSDL.php';
require_once 'SOAP/Fault.php';
require_once 'SOAP/Parser.php';
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
    * portname
    *
    * @var string contains the SOAP PORT name that is used by the client
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
    
    /**
    * wire
    *
    * @var  string  contains outoing and incoming data stream for debugging.
    */
    var $wire = NULL;
    
    /**
    * soapmsg
    *
    * @var  SOAP_Message  The soap message class that is used for outgoing calls.
    */
    var $soapmsg = NULL;
    
    /**
    * encoding
    *
    * @var  string  Contains the character encoding used for XML parser, etc.
    */
    var $encoding = SOAP_DEFAULT_ENCODING;
    
    
    /**
    * headersOut
    *
    * @var  array  contains an array of SOAP_Headers that we are sending
    */
    var $headersOut = NULL;
    /**
    * headersOut
    *
    * @var  array  contains an array headers we recieved back in the response
    */
    var $headersIn = NULL;
    
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
    
    /**
    * setEncoding
    *
    * set the character encoding, limited to 'UTF-8', 'US_ASCII' and 'ISO-8859-1'
    *
    * @param string encoding
    *
    * @return mixed returns NULL or SOAP_Fault
    * @access public
    */
    function setEncoding($encoding)
    {
        if (in_array($encoding, $this->_encodings)) {
            $this->encoding = $encoding;
            return NULL;
        }
        return $this->raiseSoapFault('Invalid Encoding');
    }
    
    /**
    * addHeader
    *
    * To add headers to the envelop, you use this function, sending it a
    * SOAP_Header class instance.
    *
    * @param SOAP_Header a soap value to send as a header
    *
    * @access public
    */
    function addHeader($soap_value)
    {
        # add a new header to the message
        if (is_a($soap_value,'soap_header')) {
            #$this->soapmsg->addHeader($soap_value);
            $this->headersOut[] = $soap_value;
        } else if (gettype($soap_value) == 'array') {
            // name, value, namespace, mustunderstand, actor
            $h = new SOAP_Header($soap_value[0], NULL, $soap_value[1], $soap_value[2], $soap_value[3]);
            $this->headersOut[] = $h;
        } else {
            $this->raiseSoapFault("Don't understand the header info you provided.  Must be array or SOAP_Header.");
        }
    }

    /**
    * SOAP_Client::call
    *
    * the namespace parameter is overloaded to accept an array of
    * options that can contain data necessary for various transports
    * if it is used as an array, it MAY contain a namespace value and a
    * soapaction value.  If it is overloaded, the soapaction parameter is
    * ignored and MUST be placed in the options array.  This is done
    * to provide backwards compatibility with current clients, but
    * may be removed in the future.
    *
    * @param string method
    * @param array  params
    * @param array options (hash with namespace, soapaction, timeout, from, subject, etc.)
    *
    * The options parameter can have a variety of values added.  The currently supported
    * values are:
    *   namespace
    *   soapaction
    *   timeout (http socket timeout)
    *   from (smtp)
    *   transfer-encoding (smtp, sets the Content-Transfer-Encoding header)
    *   subject (smtp, subject header)
    *   headers (smtp, array-hash of extra smtp headers)
    *
    * @return array of results
    * @access public
    */
    function call($method, $params = array(), $namespace = false, $soapAction = false)
    {
        $this->fault = null;
        $options = array('input'=>'parse','result'=>'parse');
        if ($params && gettype($params) != 'array') {
            $params = array($params);
        }
        if (gettype($namespace) == 'array') {
            $options = array_merge($options,$namespace);
            if (isset($options['namespace'])) $namespace = $options['namespace'];
            else $namespace = false;
        } else {
            // we'll place soapaction into our array for usage in the transport
            $options['soapaction'] = $soapAction;
            $options['namespace'] = $namespace;
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
            $options['style'] = $opData['style'];
            $options['use'] = $opData['input']['use'];
            $options['soapaction'] = $opData['soapAction'];

            // set input params
            if ($options['input'] == 'parse') {
            $nparams = array();
            if (isset($opData['input']['parts']) && count($opData['input']['parts']) > 0) {
                $i = 0;
                reset($params);
                foreach ($opData['input']['parts'] as $name => $part) {
                    $xmlns = '';
                    $attrs = array();
                    // is the name actually a complex type?
                    if (isset($part['element'])) {
                        $xmlns = $this->wsdl->namespaces[$part['namespace']];
                        $part = $this->wsdl->elements[$part['namespace']][$part['type']];
                        $name = $part['name'];
                    }
                    if (isset($params[$name])) {
                        $nparams[$name] = $params[$name];
                    } else {
                        // XXX assuming it's an array, not a hash
                        // XXX this is pretty pethetic, but "fixes" a problem where
                        // paremeter names do not match correctly
                        
                        $nparams[$name] = current($params);
                        next($params);
                    }
                    if (gettype($nparams[$name]) != 'object' ||
                        !is_a($nparams[$name],'soap_value')) {
                        // type is a qname likely, split it apart, and get the type namespace from wsdl
                        $qname = new QName($part['type']);
                        if ($qname->ns) 
                            $type_namespace = $this->wsdl->namespaces[$qname->ns];
                        else if (isset($part['namespace']))
                            $type_namespace = $this->wsdl->namespaces[$part['namespace']];
                        else
                            $type_namespace = NULL;
                        $qname->namespace = $type_namespace;
                        $type = $qname->name;
                        $pqname = $name;
                        if ($xmlns) $pqname = '{'.$xmlns.'}'.$name;
                        $nparams[$name] = new SOAP_Value($pqname, $qname->fqn(), $nparams[$name],$attrs);
                    } else {
                        // wsdl fixups to the soap value
                    }
                }
            }
            $params = $nparams;
            }
        } else {
            $this->setSchemaVersion(SOAP_XML_SCHEMA_VERSION);
        }
        
        // serialize the message
        $this->section5 = TRUE; // assume we encode with section 5
        if (isset($options['use']) && $options['use']=='literal') $this->section5 = FALSE;
        
        if (!isset($options['style']) || $options['style'] == 'rpc') {
            $options['style'] = 'rpc';
            $this->docparams = true;
            $mqname = new QName($method, $namespace);
            $methodValue = new SOAP_Value($mqname->fqn(), 'Struct', $params);
            $soap_msg = $this->_makeEnvelope($methodValue, $this->headersOut, $this->encoding,$options);
        } else {
            if ($options['input'] == 'parse') {
                if (is_array($params)) {
                    $nparams = array();
                    foreach ($params as $n => $v) {
                        if (gettype($v) != 'object') {
                            $nparams[] = new SOAP_Value($n, false, $v);
                        } else {
                            $nparams[] = $v;
                        }
                    }
                    $params = $nparams;
                }
            }
            $soap_msg = $this->_makeEnvelope($params, $this->headersOut, $this->encoding,$options);
        }

        if (PEAR::isError($soap_msg)) {
            return $this->raiseSoapFault($soap_msg);
        }
        
        // handle Mime or DIME encoding
        // XXX DIME Encoding should move to the transport, do it here for now
        // and for ease of getting it done
        if (count($this->attachments)) {
            if ((isset($options['Attachments']) && $options['Attachments'] == 'Mime') || isset($options['Mime'])) {
                $soap_msg = $this->_makeMimeMessage($soap_msg, $this->encoding);
            } else {
                // default is dime
                $soap_msg = $this->_makeDIMEMessage($soap_msg, $this->encoding);
                $options['headers']['Content-Type'] = 'application/dime';
            }
            if (PEAR::isError($soap_msg)) {
                return $this->raiseSoapFault($soap_msg);
            }
        }
        
        // instantiate client
        if (is_array($soap_msg)) {
            $soap_data = $soap_msg['body'];
            if (count($soap_msg['headers'])) {
                if (isset($options['headers'])) {
                    $options['headers'] = array_merge($options['headers'],$soap_msg['headers']);
                } else {
                    $options['headers'] = $soap_msg['headers'];
                }
            }
        } else {
            $soap_data = $soap_msg;
        }
        #$f = fopen('/tmp/dime.data','wb+');
        #if ($f) {
        #    fwrite($f,$soap_data);
        #    fclose($f);
        #    return;
        #}
        $soap_transport = new SOAP_Transport($this->endpoint, $this->encoding);
        
        if ($soap_transport->fault) {
            return $this->raiseSoapFault($soap_transport->fault);
        }
        
        // send the message
        $this->xml = $soap_transport->send($soap_data, $options);

        // save the wire information for debugging
        $this->wire = "OUTGOING:\n\n".
            $soap_transport->transport->outgoing_payload.
            "\n\nINCOMING\n\n".
            preg_replace("/></",">\r\n<",$soap_transport->transport->incoming_payload);
        
        if (isset($options['result']) && $options['result'] != 'parse') return $this->xml;
        
        if ($soap_transport->fault) {
            return $this->raiseSoapFault($this->xml);
        }
        return $this->parseResponse($this->xml, $soap_transport->result_encoding,$soap_transport->transport->attachments);
    }
    
    function parseResponse($response, $encoding, $attachments)
    {
        // parse the response
        $this->response = new SOAP_Parser($response, $encoding, $attachments);
        if ($this->response->fault) {
            return $this->raiseSoapFault($this->response->fault);
        }
        // return array of parameters
        $return = $this->response->getResponse();
        $headers = $this->response->getHeaders();
        if ($headers) {
            $this->headersIn = $this->decode($headers);
        }
        
        #$this->soapmsg = NULL;        
        
        // check for valid response
        if (PEAR::isError($return)) {
            return $this->raiseSoapFault($return);
        } else if (!is_a($return,'soap_value')) {
            return $this->raiseSoapFault("didn't get SOAP_Value object back from client");
        }

        // decode to native php datatype
        $returnArray = $this->decode($return);
        // fault?
        if (PEAR::isError($returnArray)) {
            return $this->raiseSoapFault($returnArray);
        }
        if (is_object($returnArray)) {
            $vars = get_object_vars($returnArray);
            if (array_key_exists('faultcode',$vars) || array_key_exists('Fault',$vars)) {
                $faultcode = $faultstring = $faultdetail = $faultactor = '';
                foreach ($returnArray as $k => $v) {
                    if (stristr($k,'faultcode')) $faultcode = $v;
                    if (stristr($k,'faultstring')) $faultstring = $v;
                    if (stristr($k,'detail')) $faultdetail = $v;
                    if (stristr($k,'faultactor')) $faultactor = $v;
                }
                return $this->raiseSoapFault($faultstring, $faultdetail, $faultactor, $faultcode);
            }
            if (count($vars) == 1) {
                return array_shift($vars);
            }
            // multiple return arguments!
            return $vars;
        } else
        if (is_array($returnArray)) {
            if (isset($returnArray['faultcode']) || isset($returnArray['SOAP-ENV:faultcode'])) {
                $faultcode = $faultstring = $faultdetail = $faultactor = '';
                foreach ($returnArray as $k => $v) {
                    if (stristr($k,'faultcode')) $faultcode = $v;
                    if (stristr($k,'faultstring')) $faultstring = $v;
                    if (stristr($k,'detail')) $faultdetail = $v;
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