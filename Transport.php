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
require_once("PEAR.php");
/**
* SOAP Transport Layer
* This layer can use different protocols dependant on the endpoint url provided
* no knowlege of the SOAP protocol is available at this level
* no knowlege of the transport protocols is available at this level
*
* @access public
* @version $Id$
* @package SOAP::Transport
* @author Shane Caraveo <shane@php.net>
*/
class SOAP_Transport extends PEAR
{
    var $transport = NULL;
    var $outgoing_payload = "";
    var $incoming_payload = "";
    var $errmsg = "";

    /**
    * SOAP::Transport constructor
    *
    * @param string $url   soap endpoint url
    *
    * @access public
    */
    function SOAP_Transport($url, $debug = 0)
    {
        /* only HTTP transport for now, later look at url for scheme */
        $this->debug_flag = $debug;
        $urlparts = @parse_url($url);
        if (strcasecmp($urlparts['scheme'],"http")==0) {
            require_once("SOAP/Transport/HTTP.php");
            $this->transport = new SOAP_Transport_HTTP($url);
            return;
        } else if (strcasecmp($urlparts['scheme'],"mailto")==0) {
            require_once("SOAP/Transport/SMTP.php");
            $this->transport = new SOAP_Transport_SMTP($url);
            return;
        }
        $this->errmsg = "No Transport for {$urlparts['scheme']}";
    }
    
    /**
    * send a soap package, get a soap response
    *
    * @param string &$response   soap response (in xml)
    * @param string &$soap_data   soap data to be sent (in xml)
    * @param string $action SOAP Action
    * @param int $timeout protocol timeout in seconds
    *
    * @return boolean
    * @access public
    */
    function send(&$response, &$soap_data, $action = "", $timeout=0)
    {
        if (!$this->transport) return $this->raiseError($this->errmsg, -1);
        
        if (!$this->transport->send($soap_data, $response, $action, $timeout)) {
            $this->errmsg = $this->transport->errmsg;
            return $this->raiseError($this->transport->errmsg, -1);
        }
        $this->outgoing_payload = $this->transport->outgoing_payload;
        #echo "\n OUTGOING: ".$this->transport->outgoing_payload."\n\n";
        #echo "\n INCOMING: ".$this->transport->incoming_payload."\n\n";
        #echo "\n INCOMING: ".preg_replace("/>/",">\n",$this->transport->incoming_payload)."\n\n";
        return TRUE;
    }

} // end SOAP_Transport

?>