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
require_once 'SOAP/Base.php';
require_once 'SOAP/globals.php';
require_once 'SOAP/Value.php';

/**
*  SOAP::Value
* this class converts values between PHP and SOAP
*
* originaly based on SOAPx4 by Dietrich Ayala http://dietrich.ganx4.com/soapx4
*
* @access public
* @version $Id$
* @package SOAP::Client
* @author Shane Caraveo <shane@php.net> Conversion to PEAR and updates
* @author Dietrich Ayala <dietrich@ganx4.com> Original Author
*/
class SOAP_Header extends SOAP_Value
{
    var $actor = NULL;
    var $mustunderstand = 0;

    /**
    *
    *
    * @param    string  name of the soap-value <value_name>
    * @param    mixed   soap header value
    * @param    int namespace
    * @param    mixed actor
    * @param    mixed wsdl
    */
    function SOAP_Header($name = '', $type, $value = NULL, $namespace = NULL,
                         $mustunderstand = 0,
                         $actor = 'http://schemas.xmlsoap.org/soap/actor/next',
                         $wsdl = NULL)
    {
        parent::SOAP_Value($name, $type, $value, $namespace, NULL, $wsdl);
        $this->actor = $actor;
        $this->mustunderstand = (int)$mustunderstand;
        $this->xmlout_extra =" SOAP-ENV:actor=\"$actor\" SOAP-ENV:mustUnderstand=\"$mustunderstand\"";
    }
}

?>