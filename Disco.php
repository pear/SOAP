<?
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Dmitri Vinogradov <dimitri@vinogradov.de>                    |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'SOAP/Base.php';

class SOAP_DISCO_Server extends SOAP_Base_Object
{
    var $namespaces     = array(SCHEMA_WSDL => 'wsdl', SCHEMA_SOAP => 'soap');
    var $import_ns      = array();
    var $wsdl           = '';
    var $disco          = '';
    var $_wsdl          = array();
    var $_disco         = array();
    var $_service_name  = '';
    var $_service_desc  = '';
    var $_portname      = '';
    var $_bindingname   = '';


    function SOAP_DISCO_Server($soap_server, $service_name = '', $service_desc = '', $import_ns = null)
    {
        parent::SOAP_Base_Object('Server');

        if ( !is_object($soap_server) 
            || !get_class($soap_server) == 'soap_server') return;

        $host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
        # DISCO
        $this->_disco['disco:discovery']['attr']['xmlns:disco'] = SCHEMA_DISCO;
        $this->_disco['disco:discovery']['attr']['xmlns:scl'] = SCHEMA_DISCO_SCL;
        $this->_disco['disco:discovery']['scl:contractRef']['attr']['ref'] = 
                                    array_key_exists('HTTPS',$_SERVER) 
                                    ? 'https://' . $host . $_SERVER['PHP_SELF'] . '?wsdl' 
                                    : 'http://'  . $host . $_SERVER['PHP_SELF'] . '?wsdl';

        # generate disco xml
        $this->_generate_DISCO_XML($this->_disco);

        # WSDL
        if (is_array($soap_server->_namespaces)) {
            # need to get: typens, xsd & soapenc
            $this->namespaces = array_merge($this->namespaces, array($soap_server->method_namespace => 'typens'));
            $this->namespaces = array_merge($this->namespaces, array(array_search('xsd',$soap_server->_namespaces) => 'xsd'));
            $this->namespaces = array_merge($this->namespaces, array(array_search('SOAP-ENC',$soap_server->_namespaces) => 'soapenc'));
        }

        # DEFINITIONS
        $this->_service_name = $service_name !='' ? $service_name : ereg_replace('urn:','',$soap_server->method_namespace);
        $this->_service_desc = $service_desc;
        $this->_wsdl['definitions']['attr']['name'] = $this->_service_name;
        $this->_wsdl['definitions']['attr']['targetNamespace'] = $soap_server->method_namespace;
        foreach ($this->namespaces as $ns => $prefix) {
            $this->_wsdl['definitions']['attr']['xmlns:' . $prefix] = $ns;
        }
        $this->_wsdl['definitions']['attr']['xmlns'] = SCHEMA_WSDL;

        # import namespaces
        # seems to not work yet: wsdl.exe fom .NET cant handle imported complete wsdl-definitions
        # 
        /*
        $this->import_ns = isset($import_ns) ? $import_ns : $this->import_ns;
        if (count($this->import_ns)>0) {
            $i = 0;
            foreach ($this->import_ns as $_ns => $_location) {
                $this->_wsdl['definitions']['import'][$i]['attr']['location'] = $_location;
                $this->_wsdl['definitions']['import'][$i]['attr']['namespace'] = $_ns;
                $i++;
            }
        }
        */

        # SCHEMA
        $this->_wsdl['definitions']['types']['attr'] = '';
        $this->_wsdl['definitions']['types']['xsd:schema']['attr']['xmlns'] = array_search('xsd',$this->namespaces);
        $this->_wsdl['definitions']['types']['xsd:schema']['attr']['targetNamespace'] = array_search('typens',$this->namespaces);
        $this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'] = array();

        $cTypes = 0;

        #
        foreach ($soap_server->dispatch_objects as $namespace => $namespace_objects) {
            if (is_array($namespace_objects)) {
                $_m = 0;
                foreach ($namespace_objects as $object) {
                    # types definitions
                    foreach ($object->__typedef as $_type_name => $_type_def) {
                        if (!$this->_ifComplexTypeExists($this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'], $_type_name)) {
                            $this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'][$cTypes]['attr']['name'] = $_type_name;
                            $z=0;
                            foreach ($_type_def as $_varname => $_vartype) {
                                if (!is_int($_varname)) {
                                    list($_vartypens,$_vartype) = $this->_getTypeNs($_vartype);
                                    $this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'][$cTypes]['xsd:all']['attr'] = '';
                                    $this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'][$cTypes]['xsd:all']['xsd:element'][$z]['attr']['name'] = $_varname;
                                    $this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'][$cTypes]['xsd:all']['xsd:element'][$z]['attr']['type'] = $_vartypens . ':' . $_vartype;
                                } else {
                                    $this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'][$cTypes]['xsd:complexContent']['attr'] = '';
                                    $this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'][$cTypes]['xsd:complexContent']['xsd:restriction']['attr']['base'] = 'soapenc:Array';
                                    foreach ($_vartype as $array_var => $array_type) {
                                        list($_vartypens,$_vartype) = $this->_getTypeNs($array_type);
                                        $this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'][$cTypes]['xsd:complexContent']['xsd:restriction']['xsd:attribute']['attr']['ref'] = 'soapenc:arrayType';
                                        $this->_wsdl['definitions']['types']['xsd:schema']['xsd:complexType'][$cTypes]['xsd:complexContent']['xsd:restriction']['xsd:attribute']['attr']['wsdl:arrayType'] = $_vartypens . ':' . $_vartype . '[]';
                                    }
                                }
                            $z++;
                            }
                        }
                        $cTypes++;
                    }

                    # MESSAGES
                    $i = $_m > 0 ? $i : 0;
                    foreach ($object->__dispatch_map as $method_name => $method_types) {

                        # INPUT
                        $this->_wsdl['definitions']['message'][$i]['attr']['name'] = $method_name . 'Request';
                        $part_i = 0;
                        foreach ($method_types['in'] as $name => $type) {
                                list($typens,$type) = $this->_getTypeNs($type);
                            $this->_wsdl['definitions']['message'][$i]['part'][$part_i]['attr']['name'] = $name;
                            $this->_wsdl['definitions']['message'][$i]['part'][$part_i]['attr']['type'] = $typens . ':' . $type;
                            $part_i++;
                        }

                        # OUTPUT
                        $y = $i + 1;
                        $this->_wsdl['definitions']['message'][$y]['attr']['name'] = $method_name . 'Response';
                        
                        $part_i = 0;
                        foreach ($method_types['out'] as $name => $type) {
                                list($typens,$type) = $this->_getTypeNs($type);
                            $this->_wsdl['definitions']['message'][$y]['part'][$part_i]['attr']['name'] = $name;
                            $this->_wsdl['definitions']['message'][$y]['part'][$part_i]['attr']['type'] = $typens . ':' . $type;
                            $part_i++;
                        }

                        # PORTTYPES
                        $this->_wsdl['definitions']['portType']['operation'][$i]['attr']['name'] = $method_name;

                        # INPUT
                        $this->_wsdl['definitions']['portType']['operation'][$i]['input']['attr']['message'] = 'typens:' 
                                        . $this->_wsdl['definitions']['message'][$i]['attr']['name'];

                        # OUTPUT
                        $this->_wsdl['definitions']['portType']['operation'][$i]['output']['attr']['message'] = 'typens:' 
                                        . $this->_wsdl['definitions']['message'][$y]['attr']['name'];

                        # BINDING
                        $this->_wsdl['definitions']['binding']['operation'][$i]['attr']['name'] = $method_name;
                        $this->_wsdl['definitions']['binding']['operation'][$i]['soap:operation']['attr']['soapAction'] = $soap_server->method_namespace . '#' . get_class($object) . '#' . $method_name;

                        # INPUT
                        $this->_wsdl['definitions']['binding']['operation'][$i]['input']['attr'] = '';
                        $this->_wsdl['definitions']['binding']['operation'][$i]['input']['soap:body']['attr']['use'] = 'encoded';
                        $this->_wsdl['definitions']['binding']['operation'][$i]['input']['soap:body']['attr']['namespace'] = $soap_server->method_namespace;
                        $this->_wsdl['definitions']['binding']['operation'][$i]['input']['soap:body']['attr']['encodingStyle'] = SOAP_SCHEMA_ENCODING;

                        # OUTPUT
                        $this->_wsdl['definitions']['binding']['operation'][$i]['output']['attr'] = '';
                        $this->_wsdl['definitions']['binding']['operation'][$i]['output']['soap:body']['attr']['use'] = 'encoded';
                        $this->_wsdl['definitions']['binding']['operation'][$i]['output']['soap:body']['attr']['namespace'] = $soap_server->method_namespace;
                        $this->_wsdl['definitions']['binding']['operation'][$i]['output']['soap:body']['attr']['encodingStyle'] = SOAP_SCHEMA_ENCODING;
                        $i = $i + 2;
                    }
                    $_m++;
                }
            }
        }

        # PORTTYPE-NAME
        $this->_portname = $this->_service_name . 'Port';
        $this->_wsdl['definitions']['portType']['attr']['name'] = $this->_portname;

        # BINDING-NAME
        $this->_bindingname = $this->_service_name . 'Binding';
        $this->_wsdl['definitions']['binding']['attr']['name'] = $this->_bindingname;
        $this->_wsdl['definitions']['binding']['attr']['type'] = 'typens:' . $this->_portname;
        $this->_wsdl['definitions']['binding']['soap:binding']['attr']['style'] = 'rpc';
        $this->_wsdl['definitions']['binding']['soap:binding']['attr']['transport'] = SCHEMA_HTTP;

        # SERVICE
        $this->_wsdl['definitions']['service']['attr']['name'] = $this->_service_name . 'Service';
        $this->_wsdl['definitions']['service']['documentation']['attr'] = '';
        $this->_wsdl['definitions']['service']['documentation'] = htmlentities($this->_service_desc);
        $this->_wsdl['definitions']['service']['port']['attr']['name'] = $this->_portname;
        $this->_wsdl['definitions']['service']['port']['attr']['binding'] = 'typens:' . $this->_bindingname;
        $this->_wsdl['definitions']['service']['port']['soap:address']['attr']['location'] = 
                                    array_key_exists('HTTPS',$_SERVER) 
                                    ? 'https://' . $host . $_SERVER['PHP_SELF'] 
                                    : 'http://'  . $host . $_SERVER['PHP_SELF'];

        # generate wsdl
        $this->_generate_WSDL_XML($this->_wsdl);
    }

    function _generate_DISCO_XML($disco_array) {
        $disco = '<?xml version="1.0"?>';
        foreach ($disco_array as $key => $val) {
            $disco .= $this->_arrayToNode($key,$val);
        }
        $this->disco = $disco;
    }

    function _generate_WSDL_XML($wsdl_array) {
        $wsdl = '<?xml version="1.0"?>';
        foreach ($wsdl_array as $key => $val) {
            $wsdl .= $this->_arrayToNode($key,$val);
        }
        $this->wsdl = $wsdl;
    }

    function _arrayToNode($node_name = '', $array) {
        $return = '';
        if (is_array($array)) {
            # we have a node if there's key 'attr'
            if (array_key_exists('attr',$array)) {
                $return .= "<$node_name";
                if (is_array($array['attr'])) {
                    foreach ($array['attr'] as $attr_name => $attr_value) {
                        $return .= " $attr_name=\"$attr_value\"";
                    }
                }

                # unset 'attr' and proceed other childs...
                unset($array['attr']);

                if (count($array) > 0) {
                    $i = 0;
                    foreach ($array as $child_node_name => $child_node_value) {
                        $return .= $i == 0 ? ">\n" : '';
                        $return .= $this->_arrayToNode($child_node_name,$child_node_value);
                        $i++;
                    }
                    $return .= "</$node_name>\n";
                } else {
                    $return .= " />\n";
                }
            } else {
                # we have no 'attr' key in array - so it's list of nodes with the same name ...
                foreach ($array as $child_node_name => $child_node_value) {
                    $return .= $this->_arrayToNode($node_name,$child_node_value);
                }
            }
        } else {
            # $array is not an array
            if ($array !='') {
                # and its not empty
                $return .= "<$node_name>$array</$node_name>\n";
            } else {
                # and its empty...
                $return .= "<$node_name />\n";
            }
        }
        return $return;
    }

    function _getTypeNs($type) {
        preg_match_all("'\{(.*)\}'sm",$type,$m);
        if (isset($m[1][0]) && $m[1][0] != '') {
            if (!array_key_exists($m[1][0],$this->namespaces)) {
                $ns_pref = 'ns' . count($this->namespaces);
                $this->namespaces[$m[1][0]] = $ns_pref;
                $this->_wsdl['definitions']['attr']['xmlns:' . $ns_pref] = $m[1][0];
            }
            $typens = $this->namespaces[$m[1][0]];
            $type = ereg_replace($m[0][0],'',$type);
        } else {
            $typens = 'xsd';
        }
        return array($typens,$type);
    }

    function _ifComplexTypeExists($typesArray, $type_name) {
        if (is_array($typesArray)) {
            foreach ($typesArray as $index => $type_data) {
                if ($typesArray[$index]['attr']['name'] == $type_name) {
                    return true;
                }
            }
        }
        return false;
    }
}
?>