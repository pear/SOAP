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
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once 'SOAP/Server/Email.php';
require_once 'SOAP/Transport.php';

/**
*  SOAP_Server_Email
* SOAP Server Class
*
* implements Email SOAP Server
* http://www.pocketsoap.com/specs/smtpbinding/
*
* class overrides the default HTTP server, providing the ability
* to parse an email message and execute soap calls.
* this class DOES NOT pop the message, the message, complete
* with headers, must be passed in as a parameter to the service
* function call
*
* This class calls a provided HTTP SOAP server, forwarding
* the email request, then sending the HTTP response out as an
* email
*
* @access   public
* @version  $Id$
* @package  SOAP::Server
* @author   Shane Caraveo <shane@php.net> 
*/
class SOAP_Server_Email_Gateway extends SOAP_Server_Email {
    /**
    * remove http headers from response
    *
    * TODO: use PEAR email classes
    *
    * @return boolean
    * @access private
    */
    function _parseEmail(&$data)
    {
        if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $data, $match)) {
            
            if (preg_match_all('/^(.*?):\s+(.*)$/m', $match[1], $matches)) {
                $hc = count($matches[0]);
                for ($i = 0; $i < $hc; $i++) {
                    $this->headers[strtolower($matches[1][$i])] = trim($matches[2][$i]);
                }
            }

            if (!stristr($this->headers['content-type'],'text/xml')) {
                    $this->makeFault('Client','Invalid Content Type');
                    return FALSE;
            }
            
            if (strcasecmp($this->headers['content-transfer-encoding'],'base64')==0) {
                // join lines back together
                $enctext = preg_replace("/[\r|\n]/", '', $match[2]);
                $this->request = base64_decode($enctext);
            } else if (strcasecmp($this->headers['content-transfer-encoding'],'quoted-printable')==0) {
                $this->request = $match[2];
            } else {
                $this->makeFault('Client','Invalid Content-Transfer-Encoding');
                return FALSE;
            }
            
            // if no content, return false
            return strlen($this->request) > 0;
        }
        $this->makeFault('Client','Invalid Email Format');
        return FALSE;
    }


    function service(&$data, $gateway, $endpoint = '', $send_response = TRUE, $dump = FALSE)
    {
        $this->endpoint = $endpoint;
        $response = '';
        
        // we have a full set of headers, need to find the first blank line
        if (!$this->_parseEmail($data)) {
            $fault = $this->getFaultMessage();
            $response = $fault->serialize();
        }
        // get the character encoding of the incoming request
        // treat incoming data as UTF-8 if no encoding set
        if (!$response && !$this->_getContentEncoding($this->headers['content-type'])) {
            $this->xml_encoding = SOAP_DEFAULT_ENCODING;
            // an encoding we don't understand, return a fault
            $this->makeFault('Server','Unsupported encoding, use one of ISO-8859-1, US-ASCII, UTF-8');
            $fault = $this->getFaultMessage();
            $response = $fault->serialize();
        }
        
        # call the HTTP Server
        $soap_transport = new SOAP_Transport($gateway, $this->xml_encoding);
        if ($soap_transport->fault) {
            $fault = $soap_transport->fault->message();
            $response = $fault->serialize();
        }
        
        // send the message
        if (!$response) {
            $options['soapaction'] = $this->headers['soapaction'];
            $options['headers']['Content-Type'] = $this->headers['content-type'];
            $response = $soap_transport->send($this->request, $options);
            if ($soap_transport->fault) {
                $fault = $soap_transport->fault->message();
                $response = $fault->serialize();
            }
        }
        
        if ($this->send_response) {        
            if ($dump) {
                print $payload;
            } else {
                $from = array_key_exists('reply-to',$this->headers) ? $this->headers['reply-to']:$this->headers['from'];
                # XXX what if no from?????
                
                $soap_transport = new SOAP_Transport('mailto:'.$from, $this->response_encoding);
                $from = $this->endpoint ? $this->endpoint : $this->headers['to'];
                $headers = array('In-Reply-To'=>$this->headers['message-id']);
                $options = array('from' => $from, 'subject'=> $this->headers['subject'], 'headers' => $headers);
                $soap_transport->send($response, $options);
            }
        }
    }    
}

?>