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
require_once("SOAP/Client.php");
require_once("client_params.php");
require_once("SOAP/test/test.utility.php");

error_reporting(E_ALL ^ E_NOTICE);

$localonly = 0; // set to 1 to test only your local server
$usebuiltin = 0; // use builtin list of endpoints
$test = 'base';  // which test to do: base, GroupB, GroupC
$parm = 'soapval'; // use base types: php, soapval
$show = 1;
$debug = 0;
$numservers = 0; // zero for all of them
$testfunc = ""; // test a single function
$specificendpoint = ""; //"http://63.142.188.184:1122/"; // endpoint url

if ($localonly) {
    # define your test servers endpointURL here
    $endpoints[$SOAP_LibraryName] = array(
            "endpointURL" => "http://127.0.0.1/soap/interop.php",
            "name" => $SOAP_LibraryName);
} elseif ($usebuiltin) {
    $endpoints["4s4c"] = array(
            "endpointURL" => "http://soap.4s4c.com/ilab/soap.asp",
            "name" => "4s4c");
    $endpoints["Apache Axis"] = array(
            "endpointURL" => "http://nagoya.apache.org:5049/axis/services/echo",
            "name" => "Apache Axis");
    #$endpoints["GLUE"] = array(
    #        "endpointURL" => "http://www.themindelectric.net:8005/glue/round2",
    #        "name" => "GLUE");
    #$endpoints["HP SOAP"] = array(
    #        "endpointURL" => "http://soap.bluestone.com/hpws/soap/EchoService",
    #        "name" => "HP SOAP");
    #$endpoints["IONA XMLBus"] = array(
    #        "endpointURL" => "http://interop.xmlbus.com:7002/xmlbus/container/InteropTest/BaseService/BasePort",
    #        "name" => "IONA XMLBus");
}

/********************************************************************
* you don't need to do anything below here
*/

if ($localonly || $usebuiltin ||
    getInteropEndpoints($test)
    ) {
    print "Got ".count($endpoints)." endpoints\n";
    do_interopTest($method_params[$test][$parm], $testfunc, $numservers);
}

function getInteropEndpoints($base = "base") {
    global $endpoints;
    $endpoints = array();
    // get other interop endpoints
    $soapclient = new SOAP_Client("http://www.whitemesa.net/interopInfo");
    if($endpointArray = $soapclient->call("GetEndpointInfo",array("groupName"=>$base),"http://soapinterop.org/info/","http://soapinterop.org/info/")){
        if (PEAR::isError($endpointArray)) {
            print_r($endpointArray);
            return FALSE;
        }
        #print_r($endpointArray);
        foreach($endpointArray as $k => $v){
            $endpoints[$v["endpointName"]] = $v;
        }
        return count($endpoints) > 0;
    }
    print "<xmp>$soapclient->debug_data</xmp>";
    return FALSE;
}

function decode_soapval($soapval)
{
    if (gettype($soapval) == "object" && strcasecmp(get_class($soapval),"SOAP_Value") == 0) {
        $val = $soapval->decode();
    } else {
        $val = $soapval;
    }
    if (is_array($val)) {
        foreach($val as $k => $v) {
            if (gettype($v) == "object" && strcasecmp(get_class($v),"SOAP_Value") == 0) {
                $val[$k] = decode_soapval($v);
            }
        }
    }
    return $val;
}

function test_result($expect, $result)
{
    $ok = 0;
    $expect_type = gettype($expect);
    $result_type = gettype($result);
    if ($expect_type == "array" && $result_type == "array") {
        # compare arrays
        $ok = array_compare($expect, $result);
    } else {
        $ok = string_compare($expect, $result);
    }
    return $ok;
}

function do_endpoint_method($endpoint, $method, $method_params, $show = 0, $debug = 0) {
    global $endpoints, $method_expect;

    $success = FALSE;
    if ($debug) $show = 1;
    if ($debug) {
        echo str_repeat("-",50)."<br>\n";
    }
    echo "testing $endpoint : $method : ";
    if ($debug) {
        print "method params: ";
        print_r($method_params);
        print "\n";
    }
    
    $endpoint_info = $endpoints[$endpoint];
    
    $endpoints[$endpoint]["methods"][$method] = array();
    $soap = new SOAP_Client($endpoint_info["endpointURL"]);
    $soap->debug_flag = true;
    $return = $soap->call($method,$method_params,"http://soapinterop.org/","http://soapinterop.org/");
    
    if(!PEAR::isError($return) && !$soap->fault){
        if (is_array($method_params) && count($method_params) == 1) {
            $sent = array_shift($method_params);
        } else {
            $sent = $method_params;
        }
        $endpoints[$endpoint]["methods"][$method]['sent'] = $sent;
        $endpoints[$endpoint]["methods"][$method]['return'] = $return;

        # we need to decode what we sent so we can compare!
        $sent = decode_soapval($sent);

        $ok = test_result($sent,$return);
        if (!$ok && array_key_exists($method,$method_expect)) {
            $ok = test_result($method_expect[$method],$return);
        }
        
        if($ok){
            $endpoints[$endpoint]["methods"][$method]['success'] = 1;
            $success = TRUE;
            print "PASSED<br>\n";
        } else {
            $endpoints[$endpoint]["methods"][$method]['success'] = 0;
            print "FAILED - return: ".gettype($return)."<br>\n";
            if ($debug)print  "Debug: ".$soap->debug_data."\n";
            if ($show) {
                print "<pre>\nSENT: [";
                print_r($sent);
                print "]<br>\nRECEIVED: [";
                print_r($return);
                if (array_key_exists($method,$method_expect)) {
                    print "]<br>\nEXPECTED: [";
                    print_r($method_expect[$method]);
                }
                print "]<br></pre>\n";
            }
        }
        return $success;
    }
    print "FAILED <br>\nERROR: Was unable to send or receive. Debug: $soap->faultcode $soap->faultstring $soap->faultdetail<br>\n";
    if ($debug) print " Debug: $soap->debug_data<br>\n";
    $endpoints[$endpoint]["connectFailed"]++;
    return false;
}

function do_interopTest(&$method_params, $onlyfunc, $num_endpoints = 1) {
    global $endpoints;
    global $show;
    global $debug;
    global $specificendpoint;
    $totals = array();
    // slow or unavailable sites in interop list
    $skip = array('http://explorer.ne.mediaone.net/app/interop/interop');
    $xskip = array(
        'www.themindelectric.net',
        'http://soap.bluestone.com/hpws/soap/EchoService', #slow
        'http://easysoap.sourceforge.net/cgi-bin/interopserver',
        'http://www.quakersoft.net/cgi-bin/interop2_server.cgi',
        'http://www.soapware.org/xmethodsInterop',
        'http://demo.openlinksw.com:8890/Interop',
        'http://www.whitemesa.net/interop/std',
        'http://www.phalanxsys.com/ilabA/untyped/target.asp',
        'http://explorer.ne.mediaone.net/app/interop/interop',
        
        );
    
    $i = 0;
    foreach($endpoints as $endpoint => $endpoint_info){
        if ($specificendpoint && $endpoint_info['endpointURL'] != $specificendpoint) continue;
        if (in_array($endpoint_info['endpointURL'], $skip)) continue;
        $totals['servers']++;
        $endpoints[$endpoint]["methods"] = array();
        print "Processing $endpoint at {$endpoint_info['endpointURL']}<br>\n";
        foreach(array_keys($method_params) as $func){
            if (!is_array($method_results[$func])) $method_results[$func] = array();
            if ($onlyfunc && $func != $onlyfunc) continue;
            if (do_endpoint_method($endpoint, $func, $method_params[$func],$show,$debug)) {
                $endpoint_info["methods"][$func]['success'] = TRUE;
                $totals['success']++;
            } else {
                $endpoint_info["methods"][$func]['success'] = FALSE;
                $totals['fail']++;
            }
            $totals['calls']++;
        }
        if ($num_endpoints && ++$i >= $num_endpoints) break;
    }
    echo "\n\nServers: {$totals['servers']} Calls: {$totals['calls']} Success: {$totals['success']} Fail: {$totals['fail']}<br>\n";
    
/*
    echo "\n\n\n";
    echo "<h2>Method Results:</h2>\n";
    foreach ($method_results as $method => $results) {
        echo $method.': S='.$results['success'].' F='.$results['failure'].'  CF: '.$method_results[$method]['connectFailure']."<br>\n";
    }

    echo "\n\n\n";
    echo "<h2>Endpoint Results:</h2>\n";
    foreach ($endpoints as $endpoint => $endpoint_info) {
        if (!is_array($endpoint_info["methods"])) continue;
        echo "<h3>$endpoint</h3>\n";
        foreach($endpoint_info["methods"] as $method => $results) {
            echo $method. " " .($results['success']?"succeeded":"Failed")."<br>\n";;
        }
    }
*/
}

?>
