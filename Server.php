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
require_once("SOAP/globals.php");
require_once("SOAP/Parser.php");
require_once("SOAP/Message.php");
require_once("SOAP/Value.php");

// make errors handle properly in windows
error_reporting(2039);

/**
*  SOAP::Server
* soap server class
*
* originaly based on SOAPx4 by Dietrich Ayala http://dietrich.ganx4.com/soapx4
*
* @access public
* @version $Id$
* @package SOAP::Client
* @author Shane Caraveo <shane@php.net> Conversion to PEAR and updates
* @author Dietrich Ayala <dietrich@ganx4.com> Original Author
*/
class SOAP_Server {
    var $dispatch_map = array(); // create empty dispatch map
    var $debug_flag = true;
    var $debug_str = "";
    var $headers = "";
    var $request = "";
    var $xml_encoding = "UTF-8";
    var $fault = false;
    var $fault_code = "";
    var $fault_str = "";
    var $fault_actor = "";
    var $result = "successful"; // for logging interop results to db

    function SOAP_Server($debug = FALSE) {
        // turn on debugging?
        $this->debug_flag = $debug;
    }
    
    // parses request and posts response
    function service($data)
    {
        global $SOAP_LibraryName;
        // $response is a soap_msg object
        $response = $this->parseRequest($data);
        $this->debug("parsed request and got an object of this class '".get_class($response)."'");
        $this->debug("server sending...");
        // pass along the debug string
        if ($this->debug_flag) {
            $response->debug($this->debug_str);
        }
        $payload = $response->serialize();
        // print headers
        if ($this->fault) {
            //$header[] = "HTTP/1.0 500 Internal Server Error\r\n";
            $header[] = "Status: 500 Internal Server Error\r\n";
        } else {
            //$header[] = "HTTP/1.0 200 OK\r\n";
            $header[] = "Status: 200 OK\r\n";
        }
        $header[] = "Server: $SOAP_LibraryName\r\n";
        $header[] = "Connection: Close\r\n";
        $header[] = "Content-Type: text/xml; charset=$this->xml_encoding\r\n";
        $header[] = "Content-Length: ".strlen($payload)."\r\n\r\n";
        reset($header);
        foreach ($header as $hdr) {
            header($hdr);
        }
        $this->response = join("\n",$header).$payload;
        print $payload;
    }
    
    function parseRequest($data="")
    {
        $this->debug("entering parseRequest() on ".date("H:i Y-m-d"));
        $this->debug("request uri: ".$HTTP_SERVER_VARS["REQUEST_URI"]);
        // get headers
        if (function_exists("getallheaders")) {
            $this->headers = getallheaders();
            foreach ($this->headers as $k=>$v) {
                $dump .= "$k: $v\r\n";
            }
            // get SOAPAction header
            if ($headers_array["SOAPAction"]) {
                $this->SOAPAction = str_replace('"','',$headers_array["SOAPAction"]);
                $this->service = $this->SOAPAction;
            }
            // get the character encoding of the incoming request
            if (ereg("=",$headers_array["Content-Type"])) {
                $enc = str_replace("\"","",substr(strstr($headers_array["Content-Type"],"="),1));
                if (eregi("^(ISO-8859-1|US-ASCII|UTF-8)$",$enc)) {
                    $this->xml_encoding = $enc;
                } else {
                    $this->xml_encoding = "us-ascii";
                }
            }
            $this->debug("got encoding: $this->xml_encoding");
        }
        $this->request = $dump."\r\n\r\n".$data;
        // parse response, get soap parser obj
        $parser = new SOAP_Parser($data,$this->xml_encoding);
        // get/set methodname
        $this->methodname = $parser->root_struct_name;
        $this->debug("method name: $this->methodname");
        // does method exist?
        if (!function_exists($this->methodname)) {
            // "method not found" fault here
            $this->debug("method '$this->methodname' not found!");
            $this->result = "fault: method not found";
            $this->makeFault("Server","method '$this->methodname' not defined in service '$this->service'");
            return $this->fault();
        }
        $this->debug("method '$this->methodname' exists");
        // if fault occurred during message parsing
        if ($parser->fault()) {
            // parser debug
            $this->debug($parser->debug_str);
            $this->result = "fault: error in msg parsing";
            $this->makeFault("Server","error in msg parsing:\n".$parser->getResponse());
            // return soapresp
            return $this->fault();
        // else successfully parsed request into SOAP_Value object
        } else {
            // evaluate message, getting back a SOAP_Value object
            $this->debug("calling parser->getResponse()");
            if (!$request_val = $parser->getResponse()) {
                return $this->fault();
            }
            // parser debug
            $this->debug($parser->debug_str);
            /* set namespaces
            if ($parser->namespaces["xsd"] != "") {
                //print "got ".$parser->namespaces["xsd"];
                global $SOAP_XMLSchemaVersion,$SOAP_namespaces;
                $SOAP_XMLSchemaVersion = $parser->namespaces["xsd"];
                $tmpNS = array_flip($SOAP_namespaces);
                $tmpNS["xsd"] = $SOAP_XMLSchemaVersion;
                $tmpNS["xsi"] = $SOAP_XMLSchemaVersion."-instance";
                $SOAP_namespaces = array_flip($tmpNS);
            }*/
            if (strcasecmp(get_class($request_val),"SOAP_Value")==0) {
                // verify that SOAP_Value objects in request match the methods signature
                if ($this->verifyMethod($request_val)) {
                    $this->debug("request data - name: $request_val->name, type: $request_val->type, value: $request_val->value");
                    // need to set special error detection inside the value class
                    // so as to differentiate between no params passed, and an error decoding
                    $request_data = $request_val->decode();
                    $this->debug($request_val->debug_str);
                    $this->debug("request data: $request_data");
                    
                    $this->debug("about to call method '$this->methodname'");
                    // if there are parameters to pass
                    if ($request_data) {
                        // call method with parameters
                        $this->debug("calling '$this->methodname' with params");
                        #print_r($request_data);
                        $method_response = call_user_func_array("$this->methodname",$request_data);
                    } else {
                        // call method w/ no parameters
                        $this->debug("calling $this->methodname w/ no params");
                        $method_response = call_user_func($this->methodname);
                    }
                    $this->debug("done calling method: $this->methodname");
                    // if return val is SOAP_Message
                    if (strcasecmp(get_class($method_response),"SOAP_Message")==0) {
                        if (eregi("fault",$method_response->value->name)) {
                            $this->fault = true;
                        }
                        $return_msg = $method_response;
                    } else {
                        // if return val is SOAP_Value object
                        if (strcasecmp(get_class($method_response),"SOAP_Value")==0) {
                            $return_val = $method_response;
                        // create soap_val object w/ return values from method, use method signature to determine type
                        } else {
                            $this->debug("creating new SOAP_Value to return, of type $this->return_type");
                            $return_val = new SOAP_Value($this->methodname,$this->return_type,$method_response);
                        }
                        if ($this->debug_flag) {
                            $this->debug($return_val->debug_str);
                        }
                        $this->debug("creating return soap_msg object: ".$this->methodname."Response");
                        // response object is a soap_msg object
                        $return_msg =  new SOAP_Message($this->methodname."Response",array($return_val),"$this->service");
                    }
                    if ($this->debug_flag) {
                        $return_msg->debug_flag = true;
                    }
                    $this->result = "successful";
                    return $return_msg;
                } else {
                    // debug
                    $this->debug("ERROR: request not verified against method signature");
                    $this->result = "fault: request failed validation against method signature";
                    // return soapresp
                    return $this->fault();
                }
            } else {
                // debug
                $this->debug("ERROR: parser did not return SOAP_Value object: $request_val ".get_class($request_val));
                $this->result = "fault: parser did not return SOAP_Value object: $request_val";
                // return fault
                $this->makeFault("Server","parser did not return SOAP_Value object: $request_val");
                return $this->fault();
            }
        }
    }
    
    function verifyMethod($request)
    {
        global $SOAP_typemap, $SOAP_XMLSchemaVersion;
        //return true;
        $this->debug("entered verifyMethod() w/ request name: ".$request->name);
        $params = $request->value;
        // if there are input parameters required...
        if ($sig = $this->dispatch_map[$this->methodname]["in"]) {
            $this->input_value = count($sig);
            $this->return_type = $this->getReturnType($this->methodname);
            if (is_array($params)) {
                if ($this->debug_flag) {
                    $this->debug("entered verifyMethod() with ".count($params)." parameters");
                    foreach ($params as $v) {
                        $this->debug("param '$v->name' of type '$v->type'");
                    }
                }
                // validate the number of parameters
                if (count($params) == count($sig)) {
                    $this->debug("got correct number of parameters: ".count($sig));
                    // make array of param types
                    foreach ($params as $param) {
                        $p[] = strtolower($param->type);
                    }
                    // validate each param's type
                    for($i=0; $i < count($p); $i++) {
                        // type not match
                        // if soap types do not match, we ok it if the mapped php types match
                        // this allows using plain php variables to work (ie. stuff like Decimal would fail otherwise)
                        // XXX we should do further validation of the value of the type
                        if (strtolower($sig[$i]) != strtolower($p[$i]) &&
                            !(isset($SOAP_typemap[$SOAP_XMLSchemaVersion][$sig[$i]]) &&
                            strtolower($SOAP_typemap[$SOAP_XMLSchemaVersion][$sig[$i]]) == strtolower($SOAP_typemap[$SOAP_XMLSchemaVersion][$p[$i]]))) {
                            $param = $params[$i];
                            $this->debug("mismatched parameter types: $sig[$i] != $p[$i]");
                            $this->makeFault("Client","soap request contained mismatching parameters of name $param->name had type $p[$i], which did not match signature's type: $sig[$i]");
                            return false;
                        }
                        $this->debug("parameter type match: $sig[$i] = $p[$i]");
                    }
                    return true;
                // oops, wrong number of paramss
                } else {
                    $this->debug("oops, wrong number of parameter!");
                    $this->makeFault("Client","soap request contained incorrect number of parameters. method '$this->methodname' required ".count($sig)." and request provided ".count($params));
                    return false;
                }
            // oops, no params...
            } else {
                $this->debug("oops, no parameters sent! Method '$this->methodname' requires ".count($sig)." input parameters!");
                $this->makeFault("Client","soap request contained incorrect number of parameters. method '$this->methodname' requires ".count($sig)." parameters, and request provided none");
                return false;
            }
        // no params
        } elseif (count($params)==0) {
            $this->input_values = 0;
            return true;
        } else {
            return true;
        }
    }
    
    // get string return type from dispatch map
    function getReturnType()
    {
        if (count($this->dispatch_map[$this->methodname]["out"]) >= 1) {
            $type = array_shift($this->dispatch_map[$this->methodname]["out"]);
            $this->debug("got return type from dispatch map: '$type'");
            return $type;
        }
        return false;
    }
    
    // dbg
    function debug($string)
    {
        if ($this->debug_flag) {
            $this->debug_str .= "SOAP_Server: $string\n";
        }
    }
    
    // add a method to the dispatch map
    function addToMap($methodname,$in,$out)
    {
        $this->dispatch_map[$methodname]["in"] = $in;
        $this->dispatch_map[$methodname]["out"] = $out;
    }
    
    // set up a fault
    function fault()
    {
        return new SOAP_Message("Fault",
            array(
                "faultcode" => $this->fault_code,
                "faultstring" => $this->fault_str,
                "faultactor" => $this->fault_actor,
                "faultdetail" => $this->fault_detail.$this->debug_str
            ),
            "http://schemas.xmlsoap.org/soap/envelope/"
        );
    }
    
    function makeFault($fault_code,$fault_string)
    {
        $this->fault_code = $fault_code;
        $this->fault_str = $fault_string;
        $this->fault = true;
    }
}

?>