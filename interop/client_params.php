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
require_once 'SOAP/Value.php';

// method test params
$method_params['base']['php']['echoString']['inputString'] = 'blah';
$method_params['base']['soapval']['echoString']['inputString'] = new SOAP_Value('inputString','string','blah');

$method_params['base']['php']['echoStringArray']['inputStringArray'] = array('good','bad');
$method_params['base']['soapval']['echoStringArray']['inputStringArray'] =
        new SOAP_Value('inputStringArray','Array',
            array( #push struct elements into one soap value
                new SOAP_Value('item','string','good'),
                new SOAP_Value('item','string','bad')
            )
        );

$method_params['base']['php']['echoInteger']['inputInteger'] = 34345;
$method_params['base']['soapval']['echoInteger']['inputInteger'] = new SOAP_Value('inputInteger','int',34345);

$method_params['base']['php']['echoIntegerArray']['inputIntegerArray'] = array(1,234324324,2);
$method_params['base']['soapval']['echoIntegerArray']['inputIntegerArray'] = 
        new SOAP_Value('inputIntegerArray','Array',
            array( #push struct elements into one soap value
               new SOAP_Value('item','int',1),
               new SOAP_Value('item','int',234324324),
               new SOAP_Value('item','int',2)
            )
        );

$method_params['base']['php']['echoFloat']['inputFloat'] = 342.23;
$method_params['base']['soapval']['echoFloat']['inputFloat'] = new SOAP_Value('inputFloat','float',342.23);

$method_params['base']['php']['echoFloatArray']['inputFloatArray'] = array(1.3223,34.2,325.325);
$method_params['base']['soapval']['echoFloatArray']['inputFloatArray'] =
        new SOAP_Value('inputFloatArray','Array',
            array( #push struct elements into one soap value
                new SOAP_Value('nan','float',1.3223),
                new SOAP_Value('inf','float',34.2),
                new SOAP_Value('neginf','float',325.325)
            )
        );

$method_params['base']['php']['echoStruct']['inputStruct'] = array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325);
$method_params['base']['soapval']['echoStruct']['inputStruct'] =
        new SOAP_Value('inputStruct','SOAPStruct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
            )
        );

$method_params['base']['php']['echoStructArray']['inputStructArray'] = array(
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325),
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325),
        array('varString'=>'arg', 'varInt'=>34, 'varFloat'=>325.325)
        );
$method_params['base']['soapval']['echoStructArray']['inputStructArray'] =
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
    );

$method_params['base']['php']['echoVoid'] = '';
$method_params['base']['soapval']['echoVoid'] = '';

$method_params['base']['php']['echoBase64']['inputBase64'] = 'TmVicmFza2E=';
$method_params['base']['soapval']['echoBase64']['inputBase64'] = new SOAP_Value('inputBase64','base64Binary','TmVicmFza2E=');

$method_params['base']['php']['echoHexBinary']['inputHexBinary'] = '736F61707834';
$method_params['base']['soapval']['echoHexBinary']['inputHexBinary'] = new SOAP_Value('inputHexBinary','hexBinary','736F61707834');

$method_params['base']['php']['echoDecimal']['inputDecimal'] = 1234567890; 
$method_params['base']['soapval']['echoDecimal']['inputDecimal'] = new SOAP_Value('inputDecimal','decimal','1234567890');

$method_params['base']['php']['echoDate']['inputDate'] = '2001-04-25T13:31:41-0700';//'2001-05-24T13:31:41Z';// '2001-04-25T09:31:41-0700';
$method_params['base']['soapval']['echoDate']['inputDate'] = new SOAP_Value('inputDate','dateTime','2001-04-25T13:31:41-0700');

$method_params['base']['php']['echoBoolean']['inputBoolean'] = TRUE;
$method_params['base']['soapval']['echoBoolean']['inputBoolean'] = new SOAP_Value('inputBoolean','boolean',TRUE);

// GROUP B
// this is untested yet

$method_params['GroupB']['php']['echoStructAsSimpleTypes']['inputStruct'] =
    array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325
    );
$method_params['GroupB']['soapval']['echoStructAsSimpleTypes']['inputStruct'] =
        new SOAP_Value('inputStruct','struct',
            array( #push struct elements into one soap value
                new SOAP_Value('varString','string','arg'),
                new SOAP_Value('varInt','int',34),
                new SOAP_Value('varFloat','float',325.325)
            )
        );
$method_expect['echoStructAsSimpleTypes'] =
    array(
        'outputString'=>'arg',
        'outputInteger'=>34,
        'outputFloat'=>325.325
    );    
        
$method_params['GroupB']['php']['echoSimpleTypesAsStruct'] =
    array(
        'inputString'=>'arg',
        'inputInteger'=>34,
        'inputFloat'=>325.325
    );
$method_params['GroupB']['soapval']['echoSimpleTypesAsStruct'] =
    array(
        new SOAP_Value('inputString','string','arg'),
        new SOAP_Value('inputInteger','int',34),
        new SOAP_Value('inputFloat','float',325.325)
    );
$method_expect['echoSimpleTypesAsStruct'] =
    array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325
    );    

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

$method_params['GroupB']['php']['echo2DStringArray']['input2DStringArray'] = make_2d(3,3);
$method_params['GroupB']['soapval']['echo2DStringArray']['input2DStringArray'] =
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
    );


$method_params['GroupB']['php']['echoNestedStruct']['inputStruct'] =
    array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325,
        'varStruct' => array(
            'varString'=>'arg',
            'varInt'=>34,
            'varFloat'=>325.325
        )
    );
$method_params['GroupB']['soapval']['echoNestedStruct']['inputStruct'] =
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
        );
        
$method_params['GroupB']['php']['echoNestedArray']['inputStruct'] =
    array(
        'varString'=>'arg',
        'varInt'=>34,
        'varFloat'=>325.325,
        'varArray' => array('red','blue','green')
    );
$method_params['GroupB']['soapval']['echoNestedArray']['inputStruct'] =
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
        );

?>