<?php
$totals = array();
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

function do_endpoint_method($endpoint, $method, $method_params) {
    global $endpoints, $method_expect,$usewsdl, $show, $debug;

    if ($debug) $show = 1;
    if ($debug) {
        echo str_repeat("-",50)."<br>\n";
    }
    if ($show) echo "testing $endpoint : $method : ";
    if ($debug) {
        print "method params: ";
        print_r($method_params);
        print "\n";
    }
    
    $endpoint_info = $endpoints[$endpoint];
    
    $endpoints[$endpoint]["methods"][$method] = array();
    if ($usewsdl) {
        if (array_key_exists('wsdlURL',$endpoint_info)) {
            if (!array_key_exists('client',$endpoints[$endpoint])) {
                $endpoints[$endpoint]['client'] = new SOAP_Client($endpoint_info['wsdlURL'],1);
            }
            $soap = $endpoints[$endpoint]['client'];
            if ($soap->wsdl->fault) {
                $fault = $soap->wsdl->fault->getFault();
                if ($show) echo "FAILED - WSDL: {$fault['faultstring']}\n";
                $endpoints[$endpoint]["methods"][$method]['success'] = 0;
                $endpoints[$endpoint]["methods"][$method]['fault'] = $fault;
                return FALSE;
            }
        } else {
            if ($show) echo "FAILED - No WSDL for $endpoint\n";
            $endpoints[$endpoint]["methods"][$method]['success'] = 0;
            $endpoints[$endpoint]["methods"][$method]['fault'] = array(
                'faultcode'=>'WSDL',
                'faultstring'=>"no WSDL defined for $endpoint");
            return FALSE;
        }
    } else {
        $soap = new SOAP_Client($endpoint_info['endpointURL']);
    }
    $soap->debug_flag = true;
    if ($usewsdl) {
        $return = $soap->call($method,$method_params);
    } else {
        $return = $soap->call($method,$method_params,'http://soapinterop.org/','http://soapinterop.org/');
    }
    
    if(!PEAR::isError($return)){
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
            if ($show) print "PASSED<br>\n";
        } else {
            $endpoints[$endpoint]["methods"][$method]['success'] = 0;
            $endpoints[$endpoint]["methods"][$method]['fault'] =
                                array('faultcode'=>'RESULT',
                                      'faultstring'=>'The returned result did not match what we expected to receive');
            if ($show) print "FAILED - return: ".gettype($return)."<br>\n";
            if ($debug) print  "Debug: ".HTMLSpecialChars($soap->debug_data)."\n";
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
        return $ok;
    }
    
    $fault = $return->getFault();
    $endpoints[$endpoint]["methods"][$method]['fault'] = $fault;
    $endpoints[$endpoint]["methods"][$method]['success'] = 0;
    $endpoints[$endpoint]["connectFailed"]++;

    if ($show) {
        print "FAILED <br>\nERROR: ".
            HTMLSpecialChars($fault['faultcode']).' '.
            HTMLSpecialChars($fault['faultstring']).' '.
            HTMLSpecialChars($fault['faultdetail'])."<br>\n";
    }
    if ($debug) print " Debug: $soap->debug_data<br>\n";
    return false;
}

function do_interopTest(&$method_params) {
    global $endpoints, $show, $debug, $specificendpoint, $numservers, $testfunc, $totals, $skip;
    
    #clear totals
    $totals = array();
    
    $i = 0;
    foreach($endpoints as $endpoint => $endpoint_info){
        if ($specificendpoint && $endpoint != $specificendpoint) continue;
        $skipendpoint = FALSE;
        $totals['servers']++;
        $endpoints[$endpoint]["methods"] = array();
        if ($show) print "Processing $endpoint at {$endpoint_info['endpointURL']}<br>\n";
        foreach(array_keys($method_params) as $func){
            if (in_array($endpoint, $skip)) {
                $skipendpoint = TRUE;
                $skipfault = array('faultcode'=>'SKIP','faultstring'=>'endpoint skipped');
                $endpoints[$endpoint]["methods"][$func]['fault'] = $skipfault;
                continue;
            }
            if ($testfunc && $func != $testfunc) continue;
            if ($skipendpoint) {
                $endpoints[$endpoint]["methods"][$func]['fault'] = $skipfault;
                $totals['fail']++;
            } else {
                if (do_endpoint_method($endpoint, $func, $method_params[$func])) {
                    $totals['success']++;
                } else {
                    $skipendpoint = $endpoints[$endpoint]["methods"][$func]['fault']['faultcode']=='HTTP';
                    $skipfault = $endpoints[$endpoint]["methods"][$func]['fault'];
                    $totals['fail']++;
                }
            }
            $totals['calls']++;
        }
        if ($numservers && ++$i >= $numservers) break;
    }
}

function outputTables($test, $parm,$usewsdl, &$endpoints, $methods)
{
    global $totals;
    
    echo "<b>Testing $test ";
    if ($usewsdl) echo "using WSDL ";
    else echo "using Direct calls ";
    echo "with $parm values</b><br>\n";
    echo "\n\n<b>Servers: {$totals['servers']} Calls: {$totals['calls']} Success: {$totals['success']} Fail: {$totals['fail']}</b><br>\n";
   
    echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"2\">\n";
    echo "<tr><td class=\"BLANK\">Endpoint</td>";
    foreach ($methods as $func) {
        echo "<td class=\"BLANK\">$func</td>";
    }
    echo "</tr>\n";
    $faults = array();
    foreach ($endpoints as $endpoint => $endpoint_info) {
        echo "<tr><td class=\"BLANK\">$endpoint</td>";
        foreach ($methods as $func) {
            if ($endpoints[$endpoint]["methods"][$func]['success']) {
                echo '<td class="OK">OK</td>';
            } else {
                $fault = $endpoints[$endpoint]["methods"][$func]['fault'];
                if (!$fault['faultcode']) $fault['faultcode'] = 'Unknown';
                $faults[] = "$endpoint:$func: {$fault['faultcode']} {$fault['faultstring']}";
                echo "<td class=\"{$fault['faultcode']}\">{$fault['faultcode']}</td>";
            }
            
        }
        echo "</tr>\n";
    }
    echo "</table><br>\n";
    if (count($faults) > 0) {
        echo "<b>ERROR Details:</b><br>\n<ul>\n";
        # output more error detail
        foreach ($faults as $fault) {
            echo '<li>'.$fault."</li>\n";
        }
    }
    echo "</ul><br><br>\n";
}

?>