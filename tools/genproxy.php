<?php
require_once 'SOAP/WSDL.php';

/**
 * genproxy
 *
 * a command line tool for generating SOAP proxies from WSDL files
 *
 * genproxy.php http://site/foo.wsdl > foo.php
 *
 */

function do_wsdl($uri) {
    $wsdl = new SOAP_WSDL($uri);
    print $wsdl->generateProxyCode();
}
echo "<?php\n\n";
do_wsdl($_SERVER['argv'][1]);
echo "\n?>";
?>