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
    * SOAP fault code
    * 
    * @var  mixed
    */    
    var $faultcode = '';
    
    /**
    * SOAP fault string
    * 
    * @var  mixed
    */
    var $faultstring = '';
    
    /**
    * SOAP fault details
    * 
    * @var  mixed
    */
    var $faultdetail = '';
    
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

        if ($this->endpointType == 'wsdl') {
            // get portName
            if (!$this->portName) {
                $this->portName = $this->wsdl->getPortName($method);
                if (PEAR::isError($this->portName)) {
                    return $this->raiseSoapFault($this->portName);
                }
            }
            // get endpoint
            $this->endpoint = $this->wsdl->getEndpoint($this->portName);
            if (PEAR::isError($this->endpoint)) {
                return $this->raiseSoapFault($this->endpoint);
            }
            $this->debug("endpoint: $this->endpoint");
            $this->debug("portName: $this->portName");
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
                // XXX this seems very wrong, we should be creating SOAP_Value
                // classes at this point, setting the correct type defined by the wsdl
                foreach ($opData['input']['parts'] as $name => $type) {
                    if (isset($params[$name])) {
                        $nparams[$name] = $params[$name];
                    } else {
                        // XXX assuming it's an array, not a hash
                        $nparams[$name] = $params[$i++];
                    }
                    if (gettype($nparams[$name]) != 'object' &&
                        get_class($nparams[$name]) != 'soap_value') {
                        // type is a qname likely, split it apart, and get the type namespace from wsdl
                        $type_namespace = NULL;
                        if ($qname = split(':', $type)) {
                            $type_namespace = $this->wsdl->namespaces[$qname[0]];
                            $type = $qname[1];
                        }
                        $nparams[$name] = new SOAP_Value($name, $type, $nparams[$name], $type_namespace, $type_namespace, $this->wsdl);
                    }
                }
            }
            $params = $nparams;
        }
        
        
        $this->debug("soapAction: $soapAction");
        // get namespace
        if (!$namespace && $this->endpointType == 'wsdl') {
            $namespace = $this->wsdl->getNamespace($this->portName,$method);
            #if ($this->endpointType != 'wsdl') {
            #    //die('method call requires namespace if wsdl is not available!');
            #} elseif (!$namespace = $this->wsdl->getNamespace($this->portName,$method)) {
            #    //die("no namespace found in wsdl for operation: $method!");
            #}
        }
        $this->debug("namespace: $namespace");
        
        // make message
        $soapmsg = new SOAP_Message($method, $params, $namespace, NULL, $this->wsdl);
        if ($soapmsg->fault) {
            return $this->raiseSoapFault($soapmsg->fault);
        }

        //$this->debug( "<xmp>".$soapmsg->serialize()."</xmp>");
        // instantiate client
        $dbg = "calling server at '$this->endpoint'...";
        
        $soap_transport = new SOAP_Transport($this->endpoint, $this->debug_flag);
        if ($soap_transport->fault) {
            return $this->raiseSoapFault($soap_transport->fault);
        }
        
        $this->debug($dbg . 'instantiated client successfully');
        $this->debug("endpoint: $this->endpoint<br>\n");

        // send
        $dbg = "sending msg w/ soapaction '$soapAction'...";
        
        // serialize the message
        $soap_data = $soapmsg->serialize();
        if (PEAR::isError($soap_data)) {
            return $this->raiseSoapFault($soap_data);
        }
        
        // send the message
        $this->response = $soap_transport->send($soap_data, $soapAction);
        if ($soap_transport->fault) {
            return $this->raiseSoapFault($this->response);
        }

        // parse the response
        $return = $soapmsg->parseResponse($this->response);
        $this->debug($soap_transport->debug_str);
        $this->debug($dbg . 'sent message successfully and got a(n) ' . gettype($return) . ' back');

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
                    if (stristr($k,'faultcode')) $this->faultcode = $v;
                    if (stristr($k,'faultstring')) $this->faultstring = $v;
                    if (stristr($k,'faultdetail')) $this->faultdetail = $v;
                    if (stristr($k,'faultactor')) $this->faultactor = $v;
                }
                return $this->raiseSoapFault($this->faultstring, $this->faultdetail, $this->faultactor, $this->faultcode);
            }
            // return array of return values
            if (count($returnArray) == 1) {
                return array_shift($returnArray);
            }
            return $returnArray;
        }
        return $returnArray;
    }
}
?>