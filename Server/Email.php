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

require_once 'SOAP/Server.php';
require_once 'SOAP/Transport.php';
require_once 'Mail/mimeDecode.php';
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
* @access   public
* @version  $Id$
* @package  SOAP::Server
* @author   Shane Caraveo <shane@php.net> 
*/
class SOAP_Server_Email extends SOAP_Server {
    var $headers = array();
    
    function SOAP_Server_Email($send_response = TRUE)
    {
        parent::SOAP_Server();
        $this->send_response = $send_response;
    }
    
    function service(&$data, $endpoint = '', $send_response = TRUE, $dump = FALSE)
    {
        $this->endpoint = $endpoint;
        $attachments = array();
        $headers = array();

        # if neither matches, we'll just try it anyway
        if (stristr($data,'Content-Type: application/dime')==0) {
            $this->decodeDIMEMessage($data,$this->headers,$attachments);
            $useEncoding = 'DIME';
        } else if (stristr($data,'Content-Type: multipart/related')) {
            // this is a mime message, lets decode it.
            $data = 'Content-Type: '.stripslashes($_SERVER['CONTENT_TYPE'])."\r\n\r\n".$data;
            $this->decodeMimeMessage($data,$this->headers,$attachments);
            $useEncoding = 'Mime';
        }
        
        // get the character encoding of the incoming request
        // treat incoming data as UTF-8 if no encoding set
        if (!$response && !$this->_getContentEncoding($this->headers['content-type'])) {
            $this->xml_encoding = SOAP_DEFAULT_ENCODING;
            // an encoding we don't understand, return a fault
            $this->makeFault('Server','Unsupported encoding, use one of ISO-8859-1, US-ASCII, UTF-8');
            $response = $this->getFaultMessage();                
        }
        
        if (!$this->soapfault) {
            $soap_msg = $this->parseRequest($data,$attachments);
            
            // handle Mime or DIME encoding
            // XXX DIME Encoding should move to the transport, do it here for now
            // and for ease of getting it done
            if (count($this->attachments)) {
                if ($useEncoding == 'Mime') {
                    $soap_msg = $this->_makeMimeMessage($soap_msg);
                } else {
                    // default is dime
                    $soap_msg = $this->_makeDIMEMessage($soap_msg);
                    $header['Content-Type'] = 'application/dime';
                }
                if (PEAR::isError($soap_msg)) {
                    return $this->raiseSoapFault($soap_msg);
                }
            }
            
            if (is_array($soap_msg)) {
                $response = $soap_msg['body'];
                if (count($soap_msg['headers'])) {
                    $headers = $soap_msg['headers'];
                }
            } else {
                $response = $soap_msg;
            }
        }

        if ($this->send_response) {        
            $payload = $response->serialize($this->response_encoding);
            
            if ($dump) {
                print $payload;
            } else {
                $from = array_key_exists('reply-to',$this->headers) ? $this->headers['reply-to']:$this->headers['from'];
                # XXX what if no from?????
                
                $soap_transport = new SOAP_Transport('mailto:'.$from, $this->response_encoding);
                $from = $this->endpoint ? $this->endpoint : $this->headers['to'];
                $headers['In-Reply-To']=$this->headers['message-id'];
                $options = array('from' => $from, 'subject'=> $this->headers['subject'], 'headers' => $headers);
                $soap_transport->send($payload, $options);
            }
        }
    }    
}

?>