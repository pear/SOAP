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
require_once('PEAR.php');
require_once('SOAP/Message.php');

// reference http://www.w3.org/TR/SOAP/ Section 4.4

class SOAP_Fault extends PEAR_Error
{
    function SOAP_Fault($message = 'unknown error', $code = null,
                        $mode = null, $options = null, $userinfo = null)
    {
        if (is_array($userinfo)) {
            $actor = $userinfo['actor'];
            $detail = $userinfo['detail'];
        } else {
            $actor = 'Unknown';
            $detail = $userinfo;
        }
        parent::PEAR_Error($message, $code,
                        $mode, $options, $detail);
        $this->error_message_prefix = $actor;
    }
    
    // set up a fault
    function message()
    {
        return new SOAP_Message('Fault',
            array(
                'faultcode' => $this->code,
                'faultstring' => $this->message,
                'faultactor' => $this->error_message_prefix,
                'faultdetail' => $this->userinfo
            ),
            SOAP_ENVELOP
        );
    }
    
    function getFault()
    {
        return array(
                'faultcode' => $this->code,
                'faultstring' => $this->message,
                'faultactor' => $this->error_message_prefix,
                'faultdetail' => $this->userinfo
            );
    }
    function getActor()
    {
        return $this->error_message_prefix;
    }
    function getDetail()
    {
        return $this->userinfo;
    }
    
}

?>