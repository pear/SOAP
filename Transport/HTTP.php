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

require_once 'SOAP/globals.php';
require_once 'SOAP/Base.php';

/**
*  HTTP Transport for SOAP
*
* @access public
* @version $Id$
* @package SOAP::Transport::HTTP
* @author Shane Caraveo <shane@php.net>
*/
class SOAP_Transport_HTTP extends SOAP_Base
{
    
    /**
    * Basic Auth string
    *
    * @var  string
    */
    var $credentials = '';
    
    /**
    *
    * @var  int connection timeout in seconds - 0 = none
    */
    var $timeout = 4;
    
    /**
    * Array containing urlparts - parse_url()
    * 
    * @var  mixed
    */
    var $urlparts = NULL;
    
    /**
    * Connection endpoint - URL
    *
    * @var  string
    */
    var $url = '';
    
    /**
    * Incoming payload
    *
    * @var  string
    */
    var $incoming_payload = '';
    
    /**
    * HTTP-Request User-Agent
    *
    * @var  string
    */
    var $_userAgent = SOAP_LIBRARY_NAME;

    var $encoding = SOAP_DEFAULT_ENCODING;
    
    /**
    * HTTP-Response Content-Type encoding
    *
    * we assume UTF-8 if no encoding is set
    * @var  string
    */
    var $result_encoding = 'UTF-8';
    
    var $result_content_type;
    /**
    * SOAP_Transport_HTTP Constructor
    *
    * @param string $URL    http url to soap endpoint
    *
    * @access public
    */
    function SOAP_Transport_HTTP($URL, $encoding=SOAP_DEFAULT_ENCODING)
    {
        parent::SOAP_Base('HTTP');
        $this->urlparts = @parse_url($URL);
        $this->url = $URL;
        $this->encoding = $encoding;
    }
    
    /**
    * send and receive soap data
    *
    * @param string &$msg       outgoing post data
    * @param string $action      SOAP Action header data
    * @param int $timeout  socket timeout, default 0 or off
    *
    * @return string|fault response
    * @access public
    */
    function &send(&$msg, $action = '', $timeout = 0)
    {
        if (!$this->_validateUrl()) {
            return $this->fault;
        }
        
        if ($timeout) 
            $this->timeout = $timeout;
    
        if (strcasecmp($this->urlparts['scheme'], 'HTTP') == 0) {
            return $this->_sendHTTP($msg, $action);
        } else if (strcasecmp($this->urlparts['scheme'], 'HTTPS') == 0) {
            return $this->_sendHTTPS($msg, $action);
        }
        
        return $this->raiseSoapFault('Invalid url scheme '.$this->url);
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
        $this->credentials = 'Authorization: Basic ' . base64_encode($username . ':' . $password) . "\r\n";
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
            $this->raiseSoapFault("Unable to parse URL $url");
            return FALSE;
        }
        if (!isset($this->urlparts['host'])) {
            $this->raiseSoapFault("No host in URL $url");
            return FALSE;
        }
        if (!isset($this->urlparts['port'])) {
            
            if (strcasecmp($this->urlparts['scheme'], 'HTTP') == 0)
                $this->urlparts['port'] = 80;
            else if (strcasecmp($this->urlparts['scheme'], 'HTTPS') == 0) 
                $this->urlparts['port'] = 443;
                
        }
        if (isset($this->urlparts['user'])) {
            $this->setCredentials($this->urlparts['user'], $this->urlparts['pass']);
        }
        
        return TRUE;
    }
    
    function _parseEncoding($headers)
    {
        global $SOAP_Encodings;
        
        $h = stristr($headers,'Content-Type');
        preg_match('/^Content-Type:\s*(.*)$/im',$h,$ct);
        $this->result_content_type = str_replace("\r","",$ct[1]);
        if (preg_match('/(.*?)(?:;\s?charset=)(.*)/i',$this->result_content_type,$m)) {
            // strip the string of \r
            $this->result_content_type = $m[1];
            if (count($m) > 2) {
                $enc = strtoupper(str_replace('"',"",$m[2]));
                if (in_array($enc, $SOAP_Encodings)) {
                    $this->result_encoding = $enc;
                }
            }
        }
        // deal with broken servers that don't set content type on faults
        if (!$this->result_content_type) $this->result_content_type = 'text/xml';
    }
    
    /**
    * remove http headers from response
    *
    * @return boolean
    * @access private
    */
    function _parseResponse()
    {
        if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $this->incoming_payload, $match)) {
            #$this->response = preg_replace("/[\r|\n]/", '', $match[2]);
            $this->response = $match[2];
            // find the response error, some servers response with 500 for soap faults
            if (preg_match("/^HTTP\/1\.. (\d+).*/s",$match[1],$status) &&
                $status[1] >= 400 && $status[1] < 500) {
                    $this->raiseSoapFault("HTTP Response $status[1] Not Found");
                    return FALSE;
            }
            $this->_parseEncoding($match[1]);
            if ($this->result_content_type != 'text/xml') {
                    $this->raiseSoapFault($this->response);
                    return FALSE;
            }
            // if no content, return false
            return strlen($this->response) > 0;
        }
        $this->raiseSoapFault('Invalid HTTP Response');
        return FALSE;
    }
    
    /**
    * create http request, including headers, for outgoing request
    *
    * @return string outgoing_payload
    * @access private
    */
    function &_getRequest(&$msg, $action)
    {
        $fullpath = $this->urlparts['path'].
                        ($this->urlparts['query']?'?'.$this->urlparts['query']:'').
                        ($this->urlparts['fragment']?'#'.$this->urlparts['fragment']:'');
        $this->outgoing_payload = 
                "POST $fullpath HTTP/1.0\r\n".
                "User-Agent: {$this->_userAgent}\r\n".
                "Host: {$this->urlparts['host']}\r\n".
                $this->credentials. 
                "Content-Type: text/xml; charset=$this->encoding\r\n".
                "Content-Length: ".strlen($msg)."\r\n".
                "SOAPAction: \"$action\"\r\n\r\n".
                $msg;
        return $this->outgoing_payload;
    }
    
    /**
    * send outgoing request, and read/parse response
    *
    * @param string &$msg   outgoing SOAP package
    * @param string $action   SOAP Action
    *
    * @return string &$response   response data, minus http headers
    * @access private
    */
    function &_sendHTTP(&$msg, $action = '')
    {
        $this->_getRequest($msg, $action);
        
        // send
        if ($this->timeout > 0) {
            $fp = fsockopen($this->urlparts['host'], $this->urlparts['port'], $this->errno, $this->errmsg, $this->timeout);
        } else {
            $fp = fsockopen($this->urlparts['host'], $this->urlparts['port'], $this->errno, $this->errmsg);
        }
        if (!$fp) {
            return $this->raiseSoapFault("Connect Error to {$this->urlparts['host']}:{$this->urlparts['port']}");
        }
        if (!fputs($fp, $this->outgoing_payload, strlen($this->outgoing_payload))) {
            return $this->raiseSoapFault("Error POSTing Data to {$this->urlparts['host']}");
        }
        
        // get reponse
        // XXX time consumer
        while ($data = fread($fp, 32768)) {
            $this->incoming_payload .= $data;
        }

        fclose($fp);

        if (!$this->_parseResponse()) {
            return $this->fault;
        }
        return $this->response;
    }

    /**
    * send outgoing request, and read/parse response, via HTTPS
    *
    * @param string &$msg   outgoing SOAP package
    * @param string $action   SOAP Action
    *
    * @return string &$response   response data, minus http headers
    * @access private
    */
    function &_sendHTTPS(&$msg, $action)
    {
        /* NOTE This function uses the CURL functions
        *  Your php must be compiled with CURL
        */
        if (!extension_loaded('curl')) {
            return $this->raiseSoapFault('CURL Extension is required for HTTPS');
        }
        
        $this->_getRequest($msg, $action);
        
        $ch = curl_init(); 
        if ($this->timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout); //times out after 4s 
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->outgoing_payload);
        curl_setopt($ch, CURLOPT_URL, $this->url); 
        curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_VERBOSE, 1); 
        $this->response = curl_exec($ch); 
        curl_close($ch);
        
        return $this->response;
    }
} // end SOAP_Transport_HTTP
?>
