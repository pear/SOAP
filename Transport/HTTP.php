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
// | Authors: Shane Caraveo <Shane@Caraveo.com>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//
require_once("SOAP/globals.php");
/**
*  HTTP Transport for SOAP
*
* @access public
* @version $Id$
* @package SOAP::Transport::HTTP
* @author Shane Caraveo <shane@php.net>
*/
class HTTP_Transport
{
    var $credentials = "";
    var $_userAgent;
    var $timeout = 4; // connect timeout
    var $errno = 0;
    var $errmsg = "";
    var $urlparts = NULL;
    var $url = "";
    var $incoming_payload = "";
    /**
    * HTTP_Transport Constructor
    *
    * @param string $URL    http url to soap endpoint
    *
    * @access public
    */
    function HTTP_Transport($URL)
    {
        global $soapLibraryName;
        $this->urlparts = @parse_url($URL);
        $this->url = $URL;
        $this->_userAgent = $soapLibraryName;
    }
    
    /**
    * send and receive soap data
    *
    * @param string &$msg       outgoing post data
    * @param string &$response   response data, minus http headers
    * @param string $action      SOAP Action header data
    * @param int $timeout  socket timeout, default 0 or off
    *
    * @return boolean (success or failure)
    * @access public
    */
    function send(&$msg, &$response, $action = "", $timeout=0)
    {
        if (!$this->_validateUrl()) {
            return FALSE;
        }
        if ($timeout) $this->timeout = $timeout;
        if (strcasecmp($this->urlparts['scheme'],'HTTP') == 0) {
            return $this->_sendHTTP($msg, $response, $action);
        } else if (strcasecmp($this->urlparts['scheme'],'HTTPS') == 0) {
            return $this->_sendHTTPS($msg, $response, $action);
        }
        return FALSE;
    }

    /**
    * set data for http authentication
    * creates Authorization header
    *
    * @param string $username   username
    * @param string $password   response data, minus http headers
    *
    * @return none
    * @access public
    */
    function setCredentials($username, $password)
    {
        $this->credentials = "Authorization: Basic ".base64_encode($username.":".$password)."\r\n";
    }
    
    // private members
    
    /**
    * validate url data passed to constructor
    *
    * @return boolean
    * @access private
    */
    function _validateUrl()
    {
        if ( ! is_array($this->urlparts) ) {
            $this->errno=2;
            $this->errmsg="Unable to parse URL $url";
            return FALSE;
        }
        if (!isset($this->urlparts['host'])) {
            return FALSE;
        }
        if (!isset($this->urlparts['port'])) {
            $this->urlparts['port'] = 80;
        }
        if (isset($this->urlparts['user'])) {
            $this->setCredentials($this->urlparts['user'],$this->urlparts['password']);
        }
        return TRUE;
    }
    
    /**
    * remove http headers from response
    *
    * @return boolean
    * @access private
    */
    function _parseResponse()
    {
        if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s",$this->incoming_payload,$match)) {
            $this->response = preg_replace("/[\r|\n]/","",$match[2]);
            return TRUE;
        }
        return FALSE;
    }
    
    /**
    * create http request, including headers, for outgoing request
    *
    * @return string outgoing_payload
    * @access private
    */
    function _getRequest(&$msg, $action)
    {
        $this->outgoing_payload = 
                "POST {$this->urlparts['path']} HTTP/1.0\r\n".
                "User-Agent: {$this->_userAgent}\r\n".
                "Host: {$this->urlparts['host']}\r\n".
                $this->credentials. 
                "Content-Type: text/xml\r\n".
                "Content-Length: ".strlen($msg)."\r\n".
                "SOAPAction: \"$action\"\r\n\r\n".
                $msg;
        return $this->outgoing_payload;
    }
    
    /**
    * send outgoing request, and read/parse response
    *
    * @param string &$msg   outgoing SOAP package
    * @param string &$response   response data, minus http headers
    * @param string $action   SOAP Action
    *
    * @return boolean
    * @access private
    */
    function _sendHTTP(&$msg, &$response, $action = "")
    {
        $this->_getRequest($msg, $action);
        
        // send
        if ($this->timeout > 0) {
            $fp = fsockopen($this->urlparts['host'], $this->urlparts['port'], $this->errno, $this->errmsg, $this->timeout);
        } else {
            $fp = fsockopen($this->urlparts['host'], $this->urlparts['port'], $this->errno, $this->errmsg);
        }
        if (!$fp) {
            $this->errmsg = "Unable to connect to $this->urlparts['host']:$this->urlparts['port']";
            return FALSE;
        }
        if (!fputs($fp, $this->outgoing_payload, strlen($this->outgoing_payload))) {
            $this->errmsg = "Error POSTing Data to {$this->urlparts['host']}";
            return FALSE;
        }
        
        // get reponse
        while ($data = fread($fp, 32768)) {
            $this->incoming_payload .= $data;
        }

        fclose($fp);
        if (!$this->_parseResponse()) {
            $this->errmsg = "Invalid HTTP Response";
            return FALSE;
        }
        $response = $this->response;
        return TRUE;
    }

    /**
    * send outgoing request, and read/parse response, via HTTPS
    *
    * @param string &$msg   outgoing SOAP package
    * @param string &$response   response data, minus http headers
    * @param string $action   SOAP Action
    *
    * @return boolean
    * @access private
    */
    function _sendHTTPS(&$msg, &$response, $action)
    {
        /* NOTE This function uses the CURL functions
        *  Your php must be compiled with CURL
        */
        if (!extension_loaded("php_curl")) {
            $this->errno = -1;
            $this->errmsg = "CURL Extension is required for HTTPS";
            return FALSE;
        }
        
        $this->_getRequest($msg, $action);
        
        $ch = curl_init(); 
        if ($this->timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout); //times out after 4s 
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->outgoing_payload);
        curl_setopt($ch, CURLOPT_URL, $this->url); 
        curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
        curl_setopt($ch, CURLOPT_VERBOSE,1); 
        $response=curl_exec($ch); 
        curl_close($ch);
        
        return TRUE;
    }
}; // end HTTP_Transport
?>
