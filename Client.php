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
require_once "PEAR.php";
require_once "SOAP/Transport.php";
require_once "SOAP/Message.php";
require_once "SOAP/Value.php";
require_once "SOAP/WSDL.php";

/**
*  SOAP Client Class
* this class is the main interface for making soap requests
*
* basic usage: 
* $soapclient = new SOAP_Client( string path [ ,boolean wsdl] );
* echo $soapclient->call( string methodname [ ,array parameters] );
*
* originaly based on SOAPx4 by Dietrich Ayala http://dietrich.ganx4.com/soapx4
*
* @access public
* @version $Id$
* @package SOAP::Client
* @author Shane Caraveo <shane@php.net> Conversion to PEAR and updates
* @author Stig Bakken <ssb@fast.no> Conversion to PEAR
* @author Dietrich Ayala <dietrich@ganx4.com> Original Author
*/
class SOAP_Client extends PEAR
{
    var $fault, $faultcode, $faultstring, $faultdetail;
    var $endpoint, $portName;
    var $debug_flag = false;
    var $endpointType = "";
    var $wsdl = NULL;
    
    /**
    * SOAP_Client constructor
    *
    * @params string endpoint (URL)
    * @params boolean wsdl (true if endpoint is a wsdl file)
    * @params string portName
    * @access public
    */
    function SOAP_Client($endpoint,$wsdl=false,$portName=false)
    {
        parent::PEAR();
        $this->endpoint = $endpoint;
        $this->portName = $portName;
        
        // make values
        if ($wsdl) {
            $this->endpointType = "wsdl";
            // instantiate wsdl class
            $this->wsdl = new SOAP_WSDL($this->endpoint);
        }
    }
    
    /**
    * SOAP_Client::_setFault
    *
    * @params string method
    * @params array params
    * @params string namespace  (not required if using wsdl)
    * @params string soapAction   (not required if using wsdl)
    *
    * @return array of results
    * @access public
    function _setFault($code, $summary, $detail = '')
    {
        $this->debug("FAULT: $summary<br>\n");
        $this->fault = true;
        $this->faultcode = $code;
        $this->faultstring = $summary;
        $this->faultdetail = $detail;
    }
    */
    
    /**
    * SOAP_Client::call
    *
    * @params string method
    * @params array params
    * @params string namespace  (not required if using wsdl)
    * @params string soapAction   (not required if using wsdl)
    *
    * @return array of results
    * @access public
    */
    function call($method,$params=array(),$namespace=false,$soapAction=false)
    {
        $this->fault = FALSE;
        if ($this->endpointType == "wsdl") {
            // get portName
            if (!$this->portName) {
                $this->portName = $this->wsdl->getPortName($method);
            }
            // get endpoint
            if (!$this->endpoint = $this->wsdl->getEndpoint($this->portName)) {
                return $this->raiseError("no port of name '$this->portName' in the wsdl at that location!", -1);
            }
            $this->debug("endpoint: $this->endpoint");
            $this->debug("portName: $this->portName");
            // get operation data
            if ($opData = $this->wsdl->getOperationData($this->portName,$method)) {
                $soapAction = $opData["soapAction"];
                // set input params
                $i = count($opData["input"]["parts"])-1;
                foreach ($opData["input"]["parts"] as $name => $type) {
                    if (isset($params[$name])) {
                        $nparams[$name] = $params[$name];
                    } else {
                        $nparams[$name] = $params[$i];
                    }
                }
                $params = $nparams;
            } else {
                return $this->raiseError("could not get operation info from wsdl for operation: $method", -1);
                return false;
            }
        }
        $this->debug("soapAction: $soapAction");
        // get namespace
        if (!$namespace && $this->endpointType == 'wsdl') {
            $namespace = $this->wsdl->getNamespace($this->portName,$method);
            #if ($this->endpointType != "wsdl") {
            #    //die("method call requires namespace if wsdl is not available!");
            #} elseif (!$namespace = $this->wsdl->getNamespace($this->portName,$method)) {
            #    //die("no namespace found in wsdl for operation: $method!");
            #}
        }
        $this->debug("namespace: $namespace");
        
        // make message
        $soapmsg = new SOAP_Message($method,$params,$namespace);
        //$this->debug( "<xmp>".$soapmsg->serialize()."</xmp>");
        // instantiate client
        $dbg = "calling server at '$this->endpoint'...";
        
        $soap_transport = new SOAP_Transport($this->endpoint, $this->debug_flag);

        $this->debug($dbg."instantiated client successfully");
        $this->debug("endpoint: $this->endpoint<br>\n");
        // send
        $dbg = "sending msg w/ soapaction '$soapAction'...";
        
        $soap_data = $soapmsg->serialize();
        $result = $soap_transport->send($this->response,$soap_data,$soapAction);
        if ($result && !PEAR::isError($result)) {
            // parse the response
            $return = $soapmsg->parseResponse($this->response);
            $this->debug($soap_transport->debug_str);
            $this->debug($dbg."sent message successfully and got a(n) ".gettype($return)." back");
            // check for valid response
            if (strcasecmp(get_class($return),"SOAP_Value")==0) {
                // decode to native php datatype
                $returnArray = $return->decode();
                // fault?
                if (is_array($returnArray)) {
                    if (isset($returnArray['faultcode']) || isset($returnArray['SOAP-ENV:faultcode'])) {
                        $this->debug('got fault');
                        $this->fault = true;
                        foreach ($returnArray as $k => $v) {
                            //print "$k = $v<br>";
                            if (stristr($k,'faultcode')) $this->faultcode = $v;
                            if (stristr($k,'faultstring')) $this->faultstring = $v;
                            if (stristr($k,'faultdetail')) $this->faultdetail = $v;
                            $this->debug("$k = $v<br>");
                        }
                        return false;
                    }
                    // return array of return values
                    if (count($returnArray) == 1) {
                        return array_shift($returnArray);
                    }
                    return $returnArray;
                }
                return $returnArray;
            } else {
                return $this->raiseError("didn't get SOAP_Value object back from client", -1);
            }
        }
        return $this->raiseError("client send/recieve error", -1);
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
            $this->debug_data .= "SOAP_Client: ".preg_replace("/>/","/>\r\n/",$string)."\n";
        }
    }
}


?>