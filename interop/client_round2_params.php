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
// | Authors: Shane Caraveo <Shane@Caraveo.com>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//
require_once 'SOAP/Header.php';
require_once 'SOAP/Value.php';


class SOAP_Test {
    var $type = 'php';
    var $method_name = NULL;
    var $method_params = NULL;
    var $expect = NULL;
    var $expect_fault = FALSE;
    var $headers = NULL;
    var $headers_expect = NULL;
    var $result = array();
    var $show = 1;
    var $debug = 0;
    
    function SOAP_Test($methodname, $params, $expect = NULL) {
        $this->method_name = $methodname;
        $this->method_params = $params;
        $this->expect = $expect;
        
        // determine test type
        if ($params) {
        $v = array_values($params);
        if (gettype($v[0]) == 'object' && get_class($v[0]) == 'soap_value')
            $this->type = 'soapval';
        }
    }
    
    function setResult($ok, $result, $wire, $error = '', $fault = NULL)
    {
        $this->result['success'] = $ok;
        $this->result['result'] = $result;
        $this->result['error'] = $error;
        $this->result['wire'] = $wire;
        $this->result['fault'] = $fault;
    }

    /**
    *  showMethodResult
    * print simple output about a methods result
    *
    * @param array endpoint_info
    * @param string method
    * @access public
    */    
    function showTestResult($debug = 0) {
        // debug output
        if ($debug) $this->show = 1;
        if ($debug) {
            echo str_repeat("-",50)."<br>\n";
        }
        
        echo "testing $this->method_name : ";
        if ($this->headers) {
            foreach ($this->headers as $h) {
                if (get_class($h) == 'soap_header') {
                    echo "\n    {$h->name},{$h->actor},{$h->mustunderstand} : ";
                } else {
                    if (!$h[4]) $h[4] = 'http://schemas.xmlsoap.org/soap/actor/next';
                    if (!$h[3]) $h[3] = 0;
                    echo "\n    $h[0],$h[4],$h[3] : ";
                }
            }
        }
        
        if ($debug) {
            print "method params: ";
            print_r($this->params);
            print "\n";
        }
        
        $ok = $this->result['success'];
        if ($ok) {
            print "SUCCESS\n";
        } else {
            $fault = $this->result['fault'];
            if ($fault) {
                print "FAILED: {$fault['faultcode']} {$fault['faultstring']}\n";
                if ($debug) {
                    echo "<pre>\n".$this->result['wire']."</pre>\n";
                }
            } else {
                print "FAILED: ".$this->result['result']."\n";
            }
        }
    }
}

# XXX I know this isn't quite right, need to deal with this better
function make_2d($x, $y)
{
    for ($_x = 0; $_x < $x; $_x++) {
        for ($_y = 0; $_y < $y; $_y++) {
            $a[$_x][$_y] = "x{$_x}y{$_y}";
        }
    }
    return $a;
}

//***********************************************************
// Base echoString

$soap_tests['base'][] = new SOAP_Test('echoString', array('inputString' => 'blah'));
$soap_tests['base'][] = new SOAP_Test('echoString', array('inputString' => new SOAP_Value('inputString','string','blah')));

//***********************************************************
// Base echoStringArray

$soap_tests['base'][] = new SOAP_Test('echoStringArray',
        array('inputStringArray' => array('good','bad')));
$soap_tests['base'][] = new SOAP_Test('echoStringArray',
        array('inputStringArray' =>
        new SOAP_Value('inputStringArray','Array',
            array( #push struct elements into one soap value
                new SOAP_Value('item','string','good'),
                new SOAP_Value('item','string','bad')
            )
        )));

//***********************************************************
// Base echoInteger

$soap_tests['base'][] = new SOAP_Test('echoInteger', array('inputInteger' => 34345));
$soap_tests['base'][] = new SOAP_Test('echoInteger', array('inputInteger' => new SOAP_Value('inputInteger','int',34345)));

//***********************************************************
// Base echoIntegerArray

$soap_tests['base'][] = new SOAP_Test('echoIntegerArray', array('inputIntegerArray' => array(1,234324324,2)));
$soap_tests['base'][] = new SOAP_Test('echoIntegerArray',
        array('inputIntegerArray' =>
        new SOAP_Value('inputIntegerArray','Array',
            array( #push struct elements into one soap value
               new SOAP_Value('item','int',1),
               new SOAP_Value('item','int',234324324),
               new SOAP_Value('item','int',2)
            )
        )));

//***********************************************************
// Base echoFloat

$soap_tests['base'][] = new SOAP_Test('echoFloat', array('inputFloat' => 342.23));
$soap_tests['base'][] = new SOAP_Test('echoFloat', array('inputFloat' => new SOAP_Value('inputFloat','float',342.23)));

//***********************************************************
// Base echoFloatArray

$soap_tests['base'][] = new SOAP_Test('echoFloatArray', array('inputFloatArray' => array(1.3223,34.2,325.325)));
$soap_tests['base'][] = new SOAP_Test('echoFloatArray', 
        array('inputFloatArray' =>
        new SOAP_Value('inputFloatArray','Array',
            array( #push struct elements into one soap value
                new SOAP_Value('nan','float',1.3223),
                new SOAP_Value('inf','float',34.2),
                new SOAP_Value('neginf','float',325.325)
            )
        )));

//***********************************************************
// Base echoStruct

$soap_tests['base'][] = new SOAP_Test('echoStruct', array('inputStruct' => array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325)));
$soap_tests['base'][] = new SOAP_Test('echoStruct', array('inputStruct' =>
        new SOAP_Value('inputStruct','Struct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
            )
        )));

//***********************************************************
// Base echoStructArray

$soap_tests['base'][] = new SOAP_Test('echoStructArray', array('inputStructArray' => array(
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325),
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325),
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325)
        )));
$soap_tests['base'][] = new SOAP_Test('echoStructArray', array('inputStructArray' =>
        new SOAP_Value('inputStructArray','Array',
        array( #push struct elements into one soap value
            new SOAP_Value('item','SOAPStruct',array(
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
                )),
            new SOAP_Value('item','SOAPStruct',array(
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
                )),
            new SOAP_Value('item','SOAPStruct',array(
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
                ))
        )
    )));

//***********************************************************
// Base echoVoid

$soap_tests['base'][] = new SOAP_Test('echoVoid', '');
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$soap_tests['base'][] = $test;

//***********************************************************
// Base echoBase64

$soap_tests['base'][] = new SOAP_Test('echoBase64', array('inputBase64' => 'TmVicmFza2E='));
$soap_tests['base'][] = new SOAP_Test('echoBase64', array('inputBase64' => new SOAP_Value('inputBase64','base64Binary','TmVicmFza2E=')));

//***********************************************************
// Base echoHexBinary

$soap_tests['base'][] = new SOAP_Test('echoHexBinary', array('inputHexBinary' => '736F61707834'));
$soap_tests['base'][] = new SOAP_Test('echoHexBinary', array('inputHexBinary' => new SOAP_Value('inputHexBinary','hexBinary','736F61707834')));

//***********************************************************
// Base echoDecimal

$soap_tests['base'][] = new SOAP_Test('echoDecimal', array('inputDecimal' => '1234567890'));
$soap_tests['base'][] = new SOAP_Test('echoDecimal', array('inputDecimal' => new SOAP_Value('inputDecimal','decimal','1234567890')));

//***********************************************************
// Base echoDate

$soap_tests['base'][] = new SOAP_Test('echoDate', array('inputDate' => '2001-04-25T13:31:41-0700'));
$soap_tests['base'][] = new SOAP_Test('echoDate', array('inputDate' => new SOAP_Value('inputDate','dateTime','2001-04-25T13:31:41-0700')));

//***********************************************************
// Base echoBoolean

$soap_tests['base'][] = new SOAP_Test('echoBoolean', array('inputBoolean' => TRUE));
$soap_tests['base'][] = new SOAP_Test('echoBoolean', array('inputBoolean' => new SOAP_Value('inputBoolean','boolean',TRUE)));



//***********************************************************
// GROUP B


//***********************************************************
// GroupB echoStructAsSimpleTypes

$expect = array(
        'outputString'=>'arg',
        'outputInteger'=>34,
        'outputFloat'=>325.325
    );
$soap_tests['GroupB'][] = new SOAP_Test('echoStructAsSimpleTypes',
    array('inputStruct' => array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325
    )), $expect);
$soap_tests['GroupB'][] = new SOAP_Test('echoStructAsSimpleTypes',
    array('inputStruct' =>
        new SOAP_Value('inputStruct','struct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
            )
    )), $expect);

//***********************************************************
// GroupB echoSimpleTypesAsStruct

$expect =
    array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325
    );
$soap_tests['GroupB'][] = new SOAP_Test('echoSimpleTypesAsStruct',
    array(
        'inputString'=>'arg',
        'inputInteger'=>34,
        'inputFloat'=>325.325
    ), $expect);
$soap_tests['GroupB'][] = new SOAP_Test('echoSimpleTypesAsStruct',
    array(
        new SOAP_Value('inputString','string','arg'),
        new SOAP_Value('inputInteger','int',34),
        new SOAP_Value('inputFloat','float',325.325)
    ), $expect);    

//***********************************************************
// GroupB echo2DStringArray

$soap_tests['GroupB'][] = new SOAP_Test('echo2DStringArray',
    array('input2DStringArray' => make_2d(3,3)));
$soap_tests['GroupB'][] = new SOAP_Test('echo2DStringArray',
    array('input2DStringArray' =>
        new SOAP_Value('input2DStringArray','Array',
        array(
            array(
                new SOAP_Value('item','string','row0col0'),
                new SOAP_Value('item','string','row0col1'),
                new SOAP_Value('item','string','row0col2')
                 ),
            array(
                new SOAP_Value('item','string','row1col0'),
                new SOAP_Value('item','string','row1col1'),
                new SOAP_Value('item','string','row1col2')
                )
        )
    )));

//***********************************************************
// GroupB echoNestedStruct

$soap_tests['GroupB'][] = new SOAP_Test('echoNestedStruct',
    array('inputStruct' => array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325,
        'varStruct' => array(
            'varString'=>'arg',
            'varInt'=>34,
            'varFloat'=>325.325
        )
    )));
$soap_tests['GroupB'][] = new SOAP_Test('echoNestedStruct',
    array('input2DStringArray' =>
        new SOAP_Value('inputStruct','struct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325),
                new SOAP_Value('varStruct','SOAPStruct',
                    array( #push struct elements into one soap value
                        new SOAP_Value('varString','string','arg'),
                        new SOAP_Value('varInt','int',34),
                        new SOAP_Value('varFloat','float',325.325)
                    )
                )
            )
        )));

//***********************************************************
// GroupB echoNestedArray

$soap_tests['GroupB'][] = new SOAP_Test('echoNestedArray',
    array('inputStruct' => array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325,
        'varArray' => array('red','blue','green')
    )));
$soap_tests['GroupB'][] = new SOAP_Test('echoNestedArray',
    array('input2DStringArray' =>
        new SOAP_Value('inputStruct','struct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325),
                new SOAP_Value('varArray','Array',
                    array( #push struct elements into one soap value
                        new SOAP_Value('item','string','red'),
                        new SOAP_Value('item','string','blue'),
                        new SOAP_Value('item','string','green')
                    )
                )
            )
        )));
        

//***********************************************************
// GROUP C header tests

//***********************************************************
// echoMeStringRequest php val tests

// echoMeStringRequest with endpoint as header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeStringRequest', 'hello world', 'http://soapinterop.org/echoheader/',0,'http://schemas.xmlsoap.org/soap/actor/next');
$test->headers_expect['echoMeStringRequest'] = array('echoMeStringResponse'=>'hello world');
$soap_tests['GroupC'][] = $test;

// echoMeStringRequest with endpoint as header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeStringRequest', 'hello world', 'http://soapinterop.org/echoheader/', 1,'http://schemas.xmlsoap.org/soap/actor/next');
$this->type = 'soapval'; // force a soapval version of this test
$test->headers_expect['echoMeStringRequest'] = array('echoMeStringResponse'=>'hello world');
$soap_tests['GroupC'][] = $test;

// echoMeStringRequest with endpoint NOT header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeStringRequest', 'hello world', 'http://soapinterop.org/echoheader/', 0, 'http://some/other/actor');
$test->headers_expect['echoMeStringRequest'] = array();
$soap_tests['GroupC'][] = $test;

// echoMeStringRequest with endpoint NOT header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeStringRequest', 'hello world', 'http://soapinterop.org/echoheader/', 1, 'http://some/other/actor');
$test->headers_expect['echoMeStringRequest'] = array();
$soap_tests['GroupC'][] = $test;

//***********************************************************
// echoMeStringRequest soapval tests

// echoMeStringRequest with endpoint as header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeStringRequest', 'string', 'hello world', 'http://soapinterop.org/echoheader/');
$test->headers_expect['echoMeStringRequest'] = array('echoMeStringResponse'=>'hello world');
$soap_tests['GroupC'][] = $test;

// echoMeStringRequest with endpoint as header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeStringRequest', 'string', 'hello world', 'http://soapinterop.org/echoheader/', 1);
$this->type = 'soapval'; // force a soapval version of this test
$test->headers_expect['echoMeStringRequest'] = array('echoMeStringResponse'=>'hello world');
$soap_tests['GroupC'][] = $test;

// echoMeStringRequest with endpoint NOT header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeStringRequest', 'string', 'hello world', 'http://soapinterop.org/echoheader/', 0, 'http://some/other/actor');
$test->headers_expect['echoMeStringRequest'] = array();
$soap_tests['GroupC'][] = $test;

// echoMeStringRequest with endpoint NOT header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeStringRequest', 'string', 'hello world', 'http://soapinterop.org/echoheader/', 1, 'http://some/other/actor');
$test->headers_expect['echoMeStringRequest'] = array();
$soap_tests['GroupC'][] = $test;

//***********************************************************
// php val tests
// echoMeStructRequest with endpoint as header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeStructRequest',
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325),
        'http://soapinterop.org/echoheader/',0,'http://schemas.xmlsoap.org/soap/actor/next');
$test->headers_expect['echoMeStructRequest'] =
    array('echoMeStructResponse'=> array('varString'=>'arg','varInt'=>34,'varFloat'=>325.325));
$soap_tests['GroupC'][] = $test;

// echoMeStructRequest with endpoint as header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeStructRequest',
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325),
        'http://soapinterop.org/echoheader/', 1,'http://schemas.xmlsoap.org/soap/actor/next');
$test->headers_expect['echoMeStructRequest'] =
    array('echoMeStructResponse'=> array('varString'=>'arg','varInt'=>34,'varFloat'=>325.325));
$soap_tests['GroupC'][] = $test;

// echoMeStructRequest with endpoint NOT header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeStructRequest',
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325),
        'http://soapinterop.org/echoheader/', 0, 'http://some/other/actor');
$test->headers_expect['echoMeStructRequest'] = array();
$soap_tests['GroupC'][] = $test;

// echoMeStructRequest with endpoint NOT header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeStructRequest',
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325),
        'http://soapinterop.org/echoheader/', 1, 'http://some/other/actor');
$test->headers_expect['echoMeStructRequest'] = array();
$soap_tests['GroupC'][] = $test;

//***********************************************************
// soapval tests
// echoMeStructRequest with endpoint as header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeStructRequest','SOAPStruct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
            ),
        'http://soapinterop.org/echoheader/');
$test->headers_expect['echoMeStructRequest'] =
    array('echoMeStructResponse'=> array('varString'=>'arg','varInt'=>34,'varFloat'=>325.325));
$soap_tests['GroupC'][] = $test;

// echoMeStructRequest with endpoint as header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeStructRequest','SOAPStruct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
            ),
        'http://soapinterop.org/echoheader/', 1);
$test->headers_expect['echoMeStructRequest'] =
    array('echoMeStructResponse'=> array('varString'=>'arg','varInt'=>34,'varFloat'=>325.325));
$soap_tests['GroupC'][] = $test;

// echoMeStructRequest with endpoint NOT header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeStructRequest','SOAPStruct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
            ),
        'http://soapinterop.org/echoheader/', 0, 'http://some/other/actor');
$test->headers_expect['echoMeStructRequest'] = array();
$soap_tests['GroupC'][] = $test;

// echoMeStructRequest with endpoint NOT header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeStructRequest','SOAPStruct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
            ),
        'http://soapinterop.org/echoheader/', 1, 'http://some/other/actor');
$test->headers_expect['echoMeStructRequest'] = array();
$soap_tests['GroupC'][] = $test;

//***********************************************************
// echoMeUnknown php val tests
// echoMeUnknown with endpoint as header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeUnknown', 'nobody understands me!',
        'http://soapinterop.org/echoheader/',0,'http://schemas.xmlsoap.org/soap/actor/next');
$test->headers_expect['echoMeUnknown'] = array();
$soap_tests['GroupC'][] = $test;

// echoMeUnknown with endpoint as header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeUnknown', 'nobody understands me!',
        'http://soapinterop.org/echoheader/', 1,'http://schemas.xmlsoap.org/soap/actor/next');
$test->headers_expect['echoMeUnknown'] = array();
$test->expect_fault = TRUE;
$soap_tests['GroupC'][] = $test;

// echoMeUnknown with endpoint NOT header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeUnknown', 'nobody understands me!',
        'http://soapinterop.org/echoheader/', 0, 'http://some/other/actor');
$test->headers_expect['echoMeUnknown'] = array();
$soap_tests['GroupC'][] = $test;

// echoMeUnknown with endpoint NOT header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->headers[] = array('echoMeUnknown', 'nobody understands me!',
        'http://soapinterop.org/echoheader/', 1, 'http://some/other/actor');
$test->headers_expect['echoMeUnknown'] = array();
$soap_tests['GroupC'][] = $test;

//***********************************************************
// echoMeUnknown soapval tests
// echoMeUnknown with endpoint as header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeUnknown','string','nobody understands me!',
        'http://soapinterop.org/echoheader/');
$test->headers_expect['echoMeUnknown'] = array();
$soap_tests['GroupC'][] = $test;

// echoMeUnknown with endpoint as header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeUnknown','string','nobody understands me!',
        'http://soapinterop.org/echoheader/', 1);
$test->headers_expect['echoMeUnknown'] = array();
$test->expect_fault = TRUE;
$soap_tests['GroupC'][] = $test;

// echoMeUnknown with endpoint NOT header destination, doesn't have to understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeUnknown','string','nobody understands me!',
        'http://soapinterop.org/echoheader/', 0, 'http://some/other/actor');
$test->headers_expect['echoMeUnknown'] = array();
$soap_tests['GroupC'][] = $test;

// echoMeUnknown with endpoint NOT header destination, must understand
$test = new SOAP_Test('echoVoid', '');
$test->type = 'soapval';
$test->headers[] = new SOAP_Header('echoMeUnknown','string','nobody understands me!',
        'http://soapinterop.org/echoheader/', 1, 'http://some/other/actor');
$test->headers_expect['echoMeUnknown'] = array();
$soap_tests['GroupC'][] = $test;


?>