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

    function service(&$data, $gateway, $endpoint = '', $send_response = TRUE, $dump = FALSE)
    {
        $this->endpoint = $endpoint;
        $response = '';
        
        // we have a full set of headers, need to find the first blank line
        $this->_parseEmail($data);
        if ($this->soapfault) {
            $response = $this->getFaultMessage();
        }
        // get the character encoding of the incoming request
        // treat incoming data as UTF-8 if no encoding set
        if (!$response && !$this->_getContentEncoding($this->headers['content-type'])) {
            $this->xml_encoding = SOAP_DEFAULT_ENCODING;
            // an encoding we don't understand, return a fault
            $this->makeFault('Server','Unsupported encoding, use one of ISO-8859-1, US-ASCII, UTF-8');
            $response = $this->getFaultMessage();
        }
        
        # call the HTTP Server
        if (!$response) {
            $soap_transport = new SOAP_Transport($gateway, $this->xml_encoding);
            if ($soap_transport->fault) {
                $response = $soap_transport->fault->message();
            }
        }
        
        // send the message
        if (!$response) {
            $options['soapaction'] = $this->headers['soapaction'];
            $options['headers']['Content-Type'] = $this->headers['content-type'];
            $response = $soap_transport->send($data, $options);
            if ($soap_transport->fault) {
                $response = $soap_transport->fault->message();
            }
        }
        
        if ($this->send_response) {        
            if ($dump) {
                print $response;
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