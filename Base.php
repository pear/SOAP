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
require_once 'PEAR.php';
#require_once 'SOAP/Fault.php';

class SOAP_Base extends PEAR
{
    var $debug_flag = FALSE;
    var $debug_data = '';
    var $fault = NULL;
    
    function SOAP_Base($faultcode)
    {
        global $soap_debug;
        $this->debug_flag = $soap_debug;
        $this->myfaultcode = $faultcode;
        parent::PEAR('SOAP_Fault');
    }
    
    function &raiseSoapFault($str, $detail='', $actorURI='', $code = null, $mode = null, $options = null, $skipmsg = false)
    {
        # pass through previous faults
        if (is_object($str)) {
            $this->fault = $str;
        } else {
            if (!$code) $code = $this->myfaultcode;
            $this->fault =  $this->raiseError($str,
                             $code,
                             $mode,
                             $options,
                             array('actor'=>$actorURI, 'detail'=>$detail),
                             'SOAP_Fault',
                             $skipmsg);
        }
        $this->debug($this->fault->toString());
        return $this->fault;
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
            $this->debug_data .= get_class($this).': '.preg_replace("/>/",">\r\n",$string)."\n";
        }
    }
}

?>