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

require_once 'SOAP/globals.php';
require_once 'SOAP/Fault.php';
require_once 'SOAP/Parser.php';
require_once 'SOAP/Message.php';
require_once 'SOAP/Value.php';

// make errors handle properly in windows
#error_reporting(2039);

$soap_server_fault = NULL;
function SOAP_ServerErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
    global $soap_server_fault;
    $detail = "Errno: $errno\nFilename: $filename\nLineno: $linenum\n";
    $soap_server_fault = new SOAP_Fault($errmsg, 'Server', NULL,NULL, array('detail'=>$detail));
}

/**
*  SOAP::Server
* SOAP Server Class
*
* originaly based on SOAPx4 by Dietrich Ayala http://dietrich.ganx4.com/soapx4
*
* @access   public
* @version  $Id$
* @package  SOAP::Client
* @author   Shane Caraveo <shane@php.net> Conversion to PEAR and updates
* @author   Dietrich Ayala <dietrich@ganx4.com> Original Author
*/
class SOAP_Server {

    /**
    *
    * @var  array
    */    
    var $dispatch_map = array(); // create empty dispatch map
    var $dispatch_objects = array();
    var $soapobject = NULL;
    
    /**
    *
    * @var  string
    */
    var $headers = '';
    
    /**
    *
    * @var  string
    */
    var $request = '';
    
    /**
    *
    * @var  string  XML-Encoding
    */
    var $xml_encoding = SOAP_DEFAULT_ENCODING;
    var $response_encoding = 'UTF-8';
    /**
    * 
    * @var  boolean
    */
    var $soapfault = false;
    
    var $result = 'successful'; // for logging interop results to db

    var $endpoint = ''; // the uri to ME!
    
    var $service = ''; //soapaction header
    
    function SOAP_Server() {
        ini_set('track_errors',1);
    }
    
    // parses request and posts response
    function service($data, $endpoint = '', $test = FALSE)
    {
        global $_ENV, $_SERVER;
        // figure out our endpoint
        $this->endpoint = $endpoint;
        if (!$this->endpoint) {
            // we'll try to build our endpoint
            $this->endpoint = 'http://'.$_SERVER['SERVER_NAME'];
            if ($_SERVER['SERVER_PORT']) $this->endpoint .= ':'.$_SERVER['SERVER_PORT'];
            $this->endpoint .= $PHP_SELF;
        }
        
        // if this is not a POST with Content-Type text/xml, try to return a WSDL file
        if (!$test && ($_SERVER['REQUEST_METHOD'] != 'POST' ||
            strncmp($_SERVER['CONTENT_TYPE'], 'text/xml', 8) != 0)) {
                // this is not possibly a valid soap request, try to return a WSDL file
                $this->makeFault('Server',"Invalid SOAP request, must be POST with content-type: text/xml");
                $response = $this->getFaultMessage();
        } else {
            // $response is a soap_msg object
            $response = $this->parseRequest($data);
        }
        
        $payload = $response->serialize($this->response_encoding);
        // print headers
        if ($this->soapfault) {
            $header[] = "Status: 500 Internal Server Error\r\n";
        } else {
            $header[] = "Status: 200 OK\r\n";
        }

        $header[] = 'Server: ' . SOAP_LIBRARY_NAME . "\r\n";
        $header[] = "Content-Type: text/xml; charset=$this->response_encoding\r\n";
        $header[] = 'Content-Length: ' . strlen($payload) . "\r\n\r\n";
        reset($header);
        foreach ($header as $hdr) {
            header($hdr);
        }
        $this->response = join("\n", $header) . $payload;
        print $payload;
    }
    
    function callMethod($methodname, &$args) {
        global $soap_server_fault;
        set_error_handler("SOAP_ServerErrorHandler");
        if ($args) {
            // call method with parameters
            if (is_object($this->soapobject)) {
                $ret = @call_user_func_array(array(&$this->soapobject, $methodname),$args);
            } else {
                $ret = @call_user_func_array($methodname,$args);
            }
        } else {
            // call method w/ no parameters
            if (is_object($this->soapobject)) {
                $ret = @call_user_func(array(&$this->soapobject, $methodname));
            } else {
                $ret = @call_user_func($methodname);
            }
        }
        restore_error_handler();
        if ($soap_server_fault) {
            return $soap_server_fault->message();
        }
        return $ret;
    }
    
    // create soap_val object w/ return values from method, use method signature to determine type
    function buildResult(&$method_response, &$return_type, $return_name='return', $namespace = '')
    {
        $class = get_class($method_response) ;
        if ($class == 'soap_value' || $class == 'soap_header') {
            $return_val = array($method_response);
        } else {
            if (is_array($return_type) && is_array($method_response)) {
                $i = 0;

                foreach ($return_type as $key => $type) {
                    if (is_numeric($key)) $key = 'item';
                    $return_val[] = new SOAP_Value($key,$type,$method_response[$i++],$namespace);
                }
            } else {
                if (is_array($return_type)) {
                    $keys = array_keys($return_type);
                    if (!is_numeric($keys[0])) $return_name = $keys[0];
                    $values = array_values($return_type);
                    $return_type = $values[0];
                }
                $return_val = array(new SOAP_Value($return_name,$return_type,$method_response, $namespace));
            }
        }
        return $return_val;
    }
    
    function parseRequest($data='')
    {
        global $_ENV, $_SERVER, $SOAP_Encodings;
        
        // get headers
        // get SOAPAction header
        if ($headers_array['SOAPAction']) {
            $this->SOAPAction = str_replace('"','',$_ENV['HTTP_SOAPACTION']);
            $this->service = $this->SOAPAction;
        }

        // get the character encoding of the incoming request
        // treat incoming data as UTF-8 if no encoding set
        $this->xml_encoding = 'UTF-8';
        if (strpos($_SERVER['CONTENT_TYPE'],'=')) {
            $enc = strtoupper(str_replace('"',"",substr(strstr($_SERVER['CONTENT_TYPE'],'='),1)));
            if (in_array($enc, $SOAP_Encodings)) {
                $this->xml_encoding = $enc;
            } else {
                $this->xml_encoding = SOAP_DEFAULT_ENCODING;
                // an encoding we don't understand, return a fault
                $this->makeFault('Server','Unsupported encoding, use one of ISO-8859-1, US-ASCII, UTF-8');
                return $this->getFaultMessage();                
            }
        }

        $this->request = $dump."\r\n\r\n".$data;
        // parse response, get soap parser obj
        $parser = new SOAP_Parser($data,$this->xml_encoding);
        // if fault occurred during message parsing
        if ($parser->fault) {
            $fault = $parser->fault->message();
            #$this->makeFault('Server',"error in msg parsing:\n".$fault->serialize."\n\n<pre>$data</pre>\n\n");
            return $parser->fault->message();
        }

        /* set namespaces
        if ($parser->namespaces['xsd'] != '') {
            //print 'got '.$parser->namespaces['xsd'];
            global $SOAP_namespaces;
            $this->XMLSchemaVersion = $parser->namespaces['xsd'];
            $tmpNS = array_flip($SOAP_namespaces);
            $tmpNS['xsd'] = $this->XMLSchemaVersion;
            $tmpNS['xsi'] = $this->XMLSchemaVersion.'-instance';
            $SOAP_namespaces = array_flip($tmpNS);
        }*/

        //*******************************************************
        // handle message headers

        $request_headers = $parser->getHeaders();
        $header_results = array();

        if ($request_headers) {
            if (get_class($request_headers) != 'soap_value') {
                $this->makeFault('Server',"parser did not return SOAP_Value object: $request_headers");
                return $this->getFaultMessage();
            }
            if ($request_headers->value) {
            // handle headers now
            foreach ($request_headers->value as $header_val) {
                $f_exists = $this->validateMethod($header_val->name);
                $myactor = (
                    $header_val->actor == 'http://schemas.xmlsoap.org/soap/actor/next' ||
                    $header_val->actor == $this->endpoint);
                
                if (!$f_exists && $header_val->mustunderstand && $myactor) {
                    $this->makeFault('Server',"I don't understand header $header_val->name.");
                    return $this->getFaultMessage();
                }
                
                // we only handle the header if it's for us
                $isok = $f_exists && $myactor;
                
                if ($isok) {
                    # call our header now!
                    $header_method = $header_val->name;
                    $header_data = array($header_val->decode());
                    // if there are parameters to pass
                    $hr = $this->callMethod($header_method, $header_data);
                    # if they return a fault, then it's all over!
                    if (get_class($hr) == 'soap_message' &&
                        stristr($hr->value->name,'fault')) {
                            return $hr;
                    }
                    $header_results[] = array_shift($this->buildResult($hr, $this->return_type, $header_method, $header_val->namespace));
                }
            }
            }
        }

        //*******************************************************
        // handle the method call
        
        // evaluate message, getting back a SOAP_Value object
        $this->methodname = $parser->root_struct_name[0];

        // does method exist?
        if (!$this->methodname || !$this->validateMethod($this->methodname)) {
            $this->makeFault('Server',"method '$this->methodname' not defined in service '$this->service'");
            return $this->getFaultMessage();
        }

        if (!$request_val = $parser->getResponse()) {
            return $this->getFaultMessage();
        }
        if (get_class($request_val) != 'soap_value') {
            $this->makeFault('Server',"parser did not return SOAP_Value object: $request_val");
            return $this->getFaultMessage();
        }
        
        // verify that SOAP_Value objects in request match the methods signature
        if (!$this->verifyMethod($request_val)) {
            $this->makeFault('Server','request failed validation against method signature');
            return $this->getFaultMessage();
        }
        
        // need to set special error detection inside the value class
        // so as to differentiate between no params passed, and an error decoding
        $request_data = $request_val->decode();

        $method_response = $this->callMethod($this->methodname, $request_data);

        // if return val is SOAP_Message
        if (get_class($method_response) == 'soap_message') {
            return $method_response;
        }
        
        // get the method result
        $return_val = $this->buildResult($method_response, $this->return_type, $this->methodname);
        
        // response object is a soap_msg object
        $return_msg =  new SOAP_Message();
        
        // add response headers
        if (count($header_results) > 0) {
            foreach($header_results as $hr) {
                $return_msg->addHeader($hr);
            }
        }
        
        $return_msg->method($this->methodname.'Response',$return_val,$this->service);

        if ($this->debug_flag) {
            $return_msg->debug_flag = true;
        }

        return $return_msg;
    }
    
    function verifyMethod($request)
    {
        global $SOAP_typemap;

        //return true;
        $params = $request->value;

        // if there are input parameters required...
        if ($sig = $this->dispatch_map[$this->methodname]['in']) {
            $this->input_value = count($sig);
            $this->return_type = $this->getReturnType($this->methodname);
            if (is_array($params)) {
                // validate the number of parameters
                if (count($params) == count($sig)) {
                    // make array of param types
                    foreach ($params as $param) {
                        $p[] = strtolower($param->type);
                    }
                    $sig_t = array_values($sig);
                    // validate each param's type
                    for($i=0; $i < count($p); $i++) {
                        // type not match
                        // if soap types do not match, we ok it if the mapped php types match
                        // this allows using plain php variables to work (ie. stuff like Decimal would fail otherwise)
                        // XXX we should do further validation of the value of the type
                        if (strcasecmp($sig_t[$i],$p[$i])!=0 &&
                            !(isset($SOAP_typemap[SOAP_XML_SCHEMA_VERSION][$sig_t[$i]]) &&
                            strcasecmp($SOAP_typemap[SOAP_XML_SCHEMA_VERSION][$sig_t[$i]],$SOAP_typemap[SOAP_XML_SCHEMA_VERSION][$p[$i]])==0)) {

                            $param = $params[$i];
                            $this->makeFault('Client',"soap request contained mismatching parameters of name $param->name had type [{$p[$i]}], which did not match signature's type: [{$sig_t[$i]}], matched? ".(strcasecmp($sig_t[$i],$p[$i])));
                            return false;
                        }
                    }
                    return true;
                // oops, wrong number of paramss
                } else {
                    $this->makeFault('Client',"soap request contained incorrect number of parameters. method '$this->methodname' required ".count($sig).' and request provided '.count($params));
                    return false;
                }
            // oops, no params...
            } else {
                $this->makeFault('Client',"soap request contained incorrect number of parameters. method '$this->methodname' requires ".count($sig).' parameters, and request provided none');
                return false;
            }
        // no params
        } elseif (count($params)==0) {
            $this->input_values = 0;
            return true;
        }
        // we'll try it anyway
        return true;
    }
    
    // get string return type from dispatch map
    function getReturnType($methodname)
    {
        if (is_array($this->dispatch_map[$methodname]['out'])) {
            if (count($this->dispatch_map[$methodname]['out']) > 1) {
                return $this->dispatch_map[$methodname]['out'];
            }
            $type = array_shift($this->dispatch_map[$methodname]['out']);
            return $type;
        }
        return false;
    }
    
    function validateMethod($methodname)
    {
        $this->soapobject =  NULL;
        /* if it's in our function list, ok */
        if (array_key_exists($methodname, $this->dispatch_map))
            return TRUE;
        
        /* if it's in an object, it's ok */
        foreach ($this->dispatch_objects as $obj) {
            if (method_exists($obj, $methodname)) {
                $this->soapobject =  &$obj;
                return TRUE;
            }
        }
        return FALSE;
    }
    
    function addObjectMap(&$obj)
    {
        $this->dispatch_objects[] = &$obj;
    }
    
    // add a method to the dispatch map
    function addToMap($methodname, $in, $out)
    {
        if (!function_exists($methodname)) {
            $this->makeFault('Server',"error mapping function\n");
            return $this->getFaultMessage();
        }
        $this->dispatch_map[$methodname]['in'] = $in;
        $this->dispatch_map[$methodname]['out'] = $out;
        return TRUE;
    }
    
    // set up a fault
    function getFaultMessage()
    {
        if (!$this->soapfault) {
            $this->makeFault('Server','fault message requested, but no fault has occured!');
        }
        return $this->soapfault->message();
    }
    
    function makeFault($fault_code, $fault_string)
    {
        $this->soapfault = new SOAP_Fault($fault_string, $fault_code);
    }
}
?>