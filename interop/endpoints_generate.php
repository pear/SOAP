<?
// this script is usefull for quickly testing stuff, use the 'pretty' file for html output
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

set_time_limit(0);
require_once("SOAP/Client.php");
require_once("client_params.php");
require_once("SOAP/test/test.utility.php");
require_once("SOAP/interop/client_library.php");

$tests = array('base','GroupB', 'GroupC');

foreach ($tests as $test) {
    getInteropEndpoints($test);
    $out = "<?php\n";
    foreach ($endpoints as $k => $v) {
        $out .= "\$endpoints['$k'] = array(\n".
            "    'endpointURL' => '{$v['endpointURL']}',\n".
            "    'wsdlURL' => '{$v['wsdlURL']}',\n".
            "    'endpointName' => '{$v['endpointName']}');\n";
        
    }
    $out .= "?>";
    $fd = fopen('endpoints_'.$test.'.php', 'w');
    fwrite($fd, $out);
    fclose($fd);
}
?>