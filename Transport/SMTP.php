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
// Status: rough draft, untested
//
// TODO:
//  switch to pear mail stuff
//  smtp authentication
//  smtp ssl support
//  ability to define smtp options (encoding, from, etc.)
//

require_once 'SOAP/globals.php';
require_once 'SOAP/Message.php';
require_once 'SOAP/Base.php';

/**
*  SMTP Transport for SOAP
*
* implements SOAP-SMTP as defined at
* http://www.pocketsoap.com/specs/smtpbinding/
*
* TODO: use PEAR smtp and Mime classes
*
* @access public
* @version $Id$
* @package SOAP::Transport::SMTP
* @author Shane Caraveo <shane@php.net>
*/
class SOAP_Transport_SMTP extends SOAP_Base
{
    var $credentials = '';
    var $timeout = 4; // connect timeout
    var $urlparts = NULL;
    var $url = '';
    var $incoming_payload = '';
    var $_userAgent = SOAP_LIBRARY_NAME;
    var $encoding = SOAP_DEFAULT_ENCODING;

    /**
    * SOAP_Transport_SMTP Constructor
    *
    * @param string $URL    mailto:address
    *
    * @access public
    */
    function SOAP_Transport_SMTP($URL, $encoding='US-ASCII')
    {
        parent::SOAP_Base('SMTP');
        $this->encoding = $encoding;
        $this->urlparts = @parse_url($URL);
        $this->url = $URL;
    }
    
    /**
    * send and receive soap data
    *
    * @param string &$msg       outgoing post data
    * @param string $action      SOAP Action header data
    * @param int $timeout  socket timeout, default 0 or off
    *
    * @return string &$response   response data, minus http headers
    * @access public
    */
    function send(&$msg,  /*array*/ $options = NULL)
    {
        $this->outgoing_payload = &$msg;
        if (!$this->_validateUrl()) {
            return $this->fault;
        }
        if (!$options || !array_key_exists('from',$options)) {
            return $this->raiseSoapFault("No FROM address to send message with");
        }
        $headers = "From: {$options['from']}\n".
                            "X-Mailer: $this->_userAgent\n".
                            "MIME-Version: 1.0\n".
                            "Content-Disposition: inline\n".
                            "Content-Type: text/xml; charset=\"$this->encoding\"\n";
        
        if (array_key_exists('transfer-encoding', $options)) {
            if (strcasecmp($options['transfer-encoding'],'quoted-printable')==0) {
                $headers .="Content-Transfer-Encoding: {$options['transfer-encoding']}\n";
                $out = &$msg;
            } else if (strcasecmp($options['transfer-encoding'],'base64')==0) {
                $headers .="Content-Transfer-Encoding: base64\n";
                $out = chunk_split(base64_encode($msg));
            } else {
                return $this->raiseSoapFault("Invalid Transfer Encoding: {$options['transfer-encoding']}");
            }
        } else {
            // default to base64
            $headers .="Content-Transfer-Encoding: base64\n";
            $out = chunk_split(base64_encode($msg));
        }
                            
        if (array_key_exists('soapaction', $options)) {
            "Soapaction: \"{$options['soapaction']}\"\n";
        }
        
        if (array_key_exists('headers', $options)) {
            foreach ($options['headers'] as $key => $value) {
                $headers .= "$key: $value\n";
            }
        }
        
        $subject = array_key_exists('subject', $options) ? $options['subject'] : 'SOAP Message';
        
        $this->outgoing_payload = $headers."\n\n".$this->outgoing_payload;
        # we want to return a proper XML message
        $result = mail($this->urlparts['path'], $subject, $out, $headers);

        if ($result) {
            $val = new SOAP_Value('return','boolean',TRUE);
        } else {
            $val = new SOAP_Value('Fault','Struct',array(
                new SOAP_Value('faultcode','string','SOAP-ENV:Transport:SMTP'),
                new SOAP_Value('faultstring','string',"couldn't send message to $action")
                ));
        }

        $return_msg = new SOAP_Message();
        $return_msg->method('Response',array($val),'smtp');
        $this->incoming_payload = $return_msg->serialize();

        return $this->incoming_payload;
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
        $this->username = $username;
        $this->password = $password;
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
        if (!isset($this->urlparts['scheme']) ||
            strcasecmp($this->urlparts['scheme'], 'mailto') != 0) {
                $this->raiseSoapFault("Unable to parse URL $url");
                return FALSE;
        }
        if (!isset($this->urlparts['path'])) {
            $this->raiseSoapFault("Unable to parse URL $url");
            return FALSE;
        }
        return TRUE;
    }
    
} // end SOAP_Transport_HTTP
?>
