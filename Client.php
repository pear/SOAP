<?php
/*
 * Copyright (c) 2001 Tim Uckun
 * All rights reserved.
 * 
 * This Software is distributed under the GPL. For a list of your
 * obligations and rights under this license please visit the GNU
 * website at http://www.gnu.org/
 *
 */

require_once "SOAP/Response.php";

/**
 * A soap client object to be used with other soap objects.  It
 * attempts to encapsulate various delivery methods for SOAP_Message
 * objects.
 *
 * @version 0.01
 * @author Tim Uckun <tim@diligence.com>
 *
 * Interface.
 *
 * public variables:
 *     None please don't attempt to directly access variables in objects
 *         
 * Public Functions
 * 
 * SOAP_Client($msg='')        // constructor. Use this method to create a
 *                             // soap client msg is the SOAP_Message object
 *  
 * userAgent($agent='')        // gets or sets the useragent (default is IE!)
 * msg ($msg = '')             // gets or sets the SOAP_Message object. Usually
 *                             // not needed and provided in the
 *                             // constructor
 * 
 * sendHTTP($url, $timeout=0)  // send the payload via HTTP to the URL
 * sendHTTPS($url, $timeout=0) // send the payload via HTTPS to the URL
 *                             // needs CURL to be compiled into php
 *                             // (--with-curl)
 */
class SOAP_Client
{
    var $_msg='';
  
    var $_path;
    var $_server;
    var $_port;
    var $_errno;          
    var $_errstring;      
    // some servers are picky about the user agent so let's fake it
    var $_userAgent='Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';  

    function SOAP_Client($msg='')
    {
        $this->_msg=$msg;
        return 1;
    }

    function userAgent($agent='')
    {
        if ($agent) {
            $this->_userAgent = $agent;
        }
        return $this->_userAgent;
    }

    // obviously this has to be a valid soap msg
    function msg ($msg = '')
    {
        if ($msg) {
            $this->_msg=$msg;
        }
        return $this->_msg;
    }
  
    function sendHTTP($url, $timeout=0)
    {
        if (!$this->_msg) {
            $this->_errno=1;
            $this->errstr='No message to send, nothing to do.';
            return 0;
        }
        
        $urlparts = @parse_url($url);
        /* this will crack the url like this..
            [scheme] => https
            [host] => www.diligence.com
            [port] => 8080
            [user] => tim
            [pass] => password
            [path] => /something.php
            [query] => something=something
            
            unless the user omitted somethings if so then the element is not defined
        */
        
        if ( ! is_array($urlparts) ) {
            $this->_errno=2;
            $this->errstr="Unable to parse URL $url";
            return 0;
        }
       
        if (!$urlparts['port']) {
            $urlparts['port'] = 80;
        }
        
        if ($timeout > 0) {
            $fp=fsockopen($urlparts['host'], $urlparts['port'], &$this->errno, &$this->errstr, $timeout);
        } else {
            $fp=fsockopen($urlparts['host'], $urlparts['port'], 	&$this->errno, &$this->errstr);
        }
        
        if (!$fp) {
            $this->_errno=3;
            $this->errstr="Unable to open socket in function sendHTTP10, server = $server ; port= $port";
            
            return 0;
        }
        
        $credentials="";
        if ($urlparts['user']) {
            $credentials="Authorization: Basic " .
                base64_encode($urlparts['user'] . ":" . $urlparts['pass']) . "\r\n";
        }
				  
        $body = $this->_msg->serialize();
        
        $op= "POST " . $urlparts['path'] . " " . $this->_userAgent . "\r\n" .
            "Host: ". $urlparts['host']  . "\r\n" .
            $credentials . 
            "Content-Type: text/xml\r\nContent-Length: " .
            strlen($body) . "\r\n\r\n" .
            $body;
		
        if (!fputs($fp, $op, strlen($op))) {
            $this->_errno=3;
            $this->errstr="Unable to write to the already opened socket";
            return 0;
        }
        
        $result="";

        while($data=fread($fp, 32768)) {
            $result .= $data;
        }
	    
        $resp = new SOAP_Response($result);
        
        return $resp;
    }

    function sendHTTPS($url, $timeout=0)
    {
        /* NOTE This function uses the CURL functions
        *  Your php must be compiled with CURL
        */
        print "<br> The URL is .. $url <br>";
        $ch = curl_init(); 
        if ($timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT,$timeout); //times out after 4s 
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, "Content-Type: text/xml");
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        curl_setopt ($ch, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
        curl_setopt($ch, CURLOPT_VERBOSE,1); 
        
        curl_setopt($ch, CURLOPT_POST, 1); 
        $result=curl_exec ($ch); 
        curl_close ($ch); 
        print "RESULT    $result<br>";
        $resp= new SOAP_Response($result);
        return $resp;
   }                
                       
                       
                       

} // end class soapClient


 ?>
