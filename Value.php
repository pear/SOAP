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
require_once 'SOAP/Type/dateTime.php';
require_once 'SOAP/Type/hexBinary.php';

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
class SOAP_Value extends SOAP_Base
{
    /**
    *
    * @var  boolean
    */
    var $convert_strings = FALSE;
    
    /**
    *
    *
    * @var  string
    */
    var $value = NULL;
    
    /**
    * 
    * @var  int
    */
    var $type_code = 0;
    
    /**
    *
    * @var  boolean
    */
    var $type_prefix = false;
    
    /**
    *
    * @var  string
    */
    var $name = '';
    
    /**
    *
    * @var  string
    */
    var $type = '';
    
    /**
    *
    * @var  
    */
    var $soapTypes;
    
    /**
    * Namespace
    *
    * @var  string
    */
    var $namespace = '';
    var $type_namespace = '';
    /**
    *
    * @var  string
    */
    var $prefix = '';
    
    /**
    *
    * @var  
    */
    var $wsdl;

    /**
    *
    * @var string
    */
    var $arrayType = '';
    
    
    /**
    * set to true to flatten multidimensional arrays
    * @var string
    */
    var $flattenArray = FALSE;
    /**
    *
    *
    * @param    string  name of the soap-value <value_name>
    * @param    mixed   soap value type, if not set an automatic 
    * @param    int
    * @param    mixed
    * @param    mixed
    * @param    mixed
    * @global   $SOAP_typemap, $SOAP_namespaces
    */
    function SOAP_Value($name = '', $type = false, $value = -1, $methodNamespace = NULL, $type_namespace = NULL, $wsdl = NULL)
    {
        global $SOAP_typemap, $SOAP_namespaces;
        parent::SOAP_Base('Value');
        // detect type if not passed

        $this->name = $name;
        $this->wsdl = $wsdl;
        $this->type_namespace = $type_namespace;
        $this->type = $this->_getSoapType($value, $type, $name, $type_namespace);
        $this->namespace = $methodNamespace;
        
        if (strpos($this->type , ':') !== false) {
            $qname = new QName($type);
            $this->type = $qname->name;
            $this->type_prefix = $qname->ns;
        }
        
        if (array_key_exists($this->type, $SOAP_typemap[SOAP_XML_SCHEMA_VERSION])) {
            // scalar

            $this->type_code = SOAP_VALUE_SCALAR;
            $this->addScalar($value, $this->type, $name);

        } elseif (strcasecmp('Array', $this->type) == 0 || strcasecmp('ur-type', $this->type) == 0) {
            // array
            
            $this->type_code = SOAP_VALUE_ARRAY;
            $this->addArray($value);
            
        } elseif (stristr($this->type, 'Struct')) {
            // struct
            
            $this->type_code = SOAP_VALUE_STRUCT;
            $this->addStruct($value);
            
        } elseif (is_array($value)) {
        
            $this->type_code = SOAP_VALUE_STRUCT;
            $this->addStruct($value);
            
        } else {
        
            $this->type_code = SOAP_VALUE_SCALAR;
            $this->addScalar($value, 'string', $name);
            
        }
    }
    
    /**
    *
    *
    * @param    
    * @param    
    * @param
    * @return   boolean
    */
    function addScalar($value, $type, $name = '')
    {
        if ($type == 'string') {
            $this->value = !is_null($value)?htmlspecialchars($value):NULL;
        } else {
            $this->value = $value;
        }
        return true;
    }
    
    /**
    * 
    * @param    array
    * @return   boolean
    */
    function addArray($vals)
    {
        $this->value = array();
        
        if (is_array($vals) && count($vals) >= 1) {
            foreach ($vals as $k => $v) {
                // if SOAP_Value, add..
                if (strcasecmp(get_class($v), 'SOAP_Value' ) == 0) {
                    $this->value[] = $v;
                // else make obj and serialize
                } else {
                    #$type = $this->arrayType;
                    //$type = $this->_getSoapType($v, $type, $k);
                    $new_val =  new SOAP_Value('item', NULL /*$this->arrayType*/, $v);
                    $this->value[] = $new_val;
                }
            }
        }
        return true;
    }

    /**
    *
    * @param   array
    * @param    boolean
    */
    function addStruct($vals)
    {
        if (is_array($vals) && count($vals) >= 1) {
            foreach ($vals as $k => $v) {
                // if serialize, if SOAP_Value
                if (strcasecmp(get_class($v), 'SOAP_Value') == 0) {
                    $this->value[] = $v;
                // else make obj and serialize
                } else {
                    //$type = NULL;
                    //$type = $this->_getSoapType($v, $type, $k);
                    $new_val = new SOAP_Value($k, NULL, $v);
                    $this->value[] = $new_val;
                }
            }
        } else {
            $this->value = array();
        }
        return true;
    }
    
    /**
    *
    * @param    
    * @param    
    * @param    
    * @param    
    * @return   int
    */
    function _multiArrayType(&$value, &$type, &$size, &$xml)
    {
        foreach ($value as $array_val) {
            $array_types[$array_val->type] = 1;
            #$xml .= $this->serializeval($array_val);
        }

        if ($array_val->type_prefix) {
            $type = $array_val->type_prefix . ':' . $array_val->type;
        } else {
            $type = $array_val->type;
        }

        $sz = count($value);
        $num_types = count($array_types);
        if ($num_types == 1) {
            if (array_key_exists('Array', $array_types)) {
                // seems we have a multi dimensional array, figure it out if we do
                foreach ($value as $array_val) {
                    $numtypes = $this->_multiArrayType($array_val->value, $type, $size, $xml);
                    if ($numtypes > 1) 
                        return $numtypes;
                }

                if ($sz) {
                    $size = $sz.','.$size;
                } else {
                    $size = $sz;
                }
                return 1;
            } else {
                foreach ($value as $array_val) {
                    #$array_types[$array_val->type] = 1;
                    $xml .= $array_val->serialize();
                }
            }
        }
        $size = $sz;
        return $num_types;
    }
    
    /**
    * Turn SOAP_Value's into xml, woohoo!
    * 
    * @param    mixed
    * @return   string  xml representation
    */
    function serializeval($soapval = false)
    {
        if (!$soapval) {
            $soapval = $this;
        }

        if (is_int($soapval->name)) {
            $soapval->name = 'item';
            $soapval->prefix = '';
        }
        
        if ($soapval->prefix && $soapval->type_prefix) {
            $xmlout_name = "$soapval->prefix:$soapval->name";
            $xmlout_type = "$soapval->type_prefix:$soapval->type";
        } else
        if ($soapval->type_prefix) {
            $xmlout_name = $soapval->name;
            $xmlout_type = "$soapval->type_prefix:$soapval->type";
        } elseif ($soapval->prefix) {
            $xmlout_name = "$soapval->prefix:$soapval->name";
        } else {
            $xmlout_name = $soapval->name;
        }
        $xmlout_value = NULL;
        $xmlout_offset = '';

        switch ($soapval->type_code) {
        case SOAP_VALUE_STRUCT:
            // struct
            if (is_array($soapval->value)) {
                foreach ($soapval->value as $k => $v) {
                    $xmlout_value .= $v->serialize($v);
                }
            }
            break;
            
        case SOAP_VALUE_ARRAY:
            // array
            $xmlout_type = 'SOAP-ENC:Array';
            // XXX this will be slow on larger array's.  Basicly, it flattens array's to allow us
            // to serialize multi-dimensional array's.  We only do this if arrayType is set,
            // which will typicaly only happen if we are using WSDL
            if ($this->flattenArray || ($this->arrayType && strchr($this->arrayType,','))) {
                $numtypes = $this->_multiArrayType($soapval->value, $array_type, $ar_size, $xmlout_value);
            }
            #$numtypes = 0;
            $array_type_prefix = '';
            if ($numtypes != 1) {
                foreach ($soapval->value as $array_val) {
                    $array_types[$array_val->type] = 1;
                    $xmlout_value .= $array_val->serialize();
                }

                $ar_size = count($soapval->value);
                $numtypes = count($array_types);
                if ($this->arrayType) {
                    $ch = strpos($this->arrayType, '[');
                    if ($ch) {
                        $array_type = substr($this->arrayType,0,$ch);
                    } else {
                        $array_type = $this->arrayType;
                    }
                } else {
                    $array_type = $array_val->type;
                }
                if ($array_type == 'Struct') {
                    $array_type = 'anyType'; // should reference what schema we're using
                    $array_val->type_prefix = 'xsd';
                }
                $array_type_prefix = $array_val->type_prefix;
                $xmlout_offset = " SOAP-ENC:offset=\"[0]\"";
            }
            if ($numtypes > 1) {
                $array_type = 'xsd:anyType'; // should reference what schema we're using
            } elseif ($numtypes == 1) {
                if ($array_type_prefix != '') {
                    $array_type = $array_type_prefix . ':' . $array_type;
                } elseif ($array_type_prefix = $this->getPrefix($array_type)) {
                    $array_type = $array_type_prefix . ':' . $array_type;
                }
            }
            $xmlout_arrayType = " SOAP-ENC:arrayType=\"".$array_type."[$ar_size]\"";
            break;
            
        case SOAP_VALUE_SCALAR:
            $xmlout_value = $soapval->value;
            break;
        default:
            break;
        }
        
        if ($xmlout_type) $xmlout_type = " xsi:type=\"$xmlout_type\"";
        if (is_null($xmlout_value)) {
            $xml = "\r\n<{$xmlout_name}{$xmlout_type}/>";
        } else {
            $xml = "\r\n<{$xmlout_name}{$xmlout_type}{$xmlout_arrayType}{$xmlout_offset}".
                $this->xmlout_extra.">".
                $xmlout_value."</$xmlout_name>";
        }        
        return $xml;
    }
    
    /**
    * Serialize
    * 
    * @return   string  xml representation
    */
    function serialize()
    {
        global $SOAP_namespaces;
        
        if ($this->namespace) {
            if (!isset($SOAP_namespaces[$this->namespace])) {
                $SOAP_namespaces[$this->namespace] = 'ns' . (count($SOAP_namespaces) + 1);
            }

            $this->prefix = $SOAP_namespaces[$this->namespace];
        }
        // get type prefix
        if (strpos($this->type , ':') !== false) {
            $qname = new QName($type);
            $this->type = $qname->name;
            $this->type_prefix = $qname->ns;

        } elseif ($this->type_namespace) {
        
            if (!isset($SOAP_namespaces[$this->type_namespace])) {
                $SOAP_namespaces[$this->type_namespace] = 'ns'.(count($SOAP_namespaces)+1);
            }
            $this->type_prefix = $SOAP_namespaces[$this->type_namespace];
            
        // if type namespace was not explicitly passed, and we're not in a method struct:
        } elseif (!$this->type_prefix && $this->type != 'Struct' /*!isset($type_namespace)*/) {
        
            // try to get type prefix from typeMap
            if ($ns = $this->verifyType($this->type)) {
                $this->type_prefix = $SOAP_namespaces[$ns];
            } else if ($this->namespace) {
                // else default to method namespace
                $this->type_prefix = $SOAP_namespaces[$this->namespace];
            }
        }
        return $this->serializeval($this);
    }
    
    /**
    *
    * @param    mixed
    * @global   $SOAP_typemap
    */
    function decode($soapval = false)
    {
        global $SOAP_typemap;
        
        if (!$soapval) {
            $soapval = $this;
        }
        
        // scalar decode
        if ($soapval->type_code == SOAP_VALUE_SCALAR) {
            if ($soapval->type == 'boolean') {
                if ($soapval->value != '0' && strcasecmp($soapval->value,'false') !=0) {
                    $soapval->value = TRUE;
                } else {
                    $soapval->value = FALSE;
                }
            #} else if ($soapval->type == 'dateTime') {
            #    # we don't realy know what a user want's in return,
            #    # but we'll just do unix time stamps for now
            #    # THOUGHT: we could return a class instead.
            #    $dt = new SOAP_Type_dateTime($soapval->value);
            #    $soapval->value = $dt->toUnixtime();
            } else if (array_key_exists($soapval->type, $SOAP_typemap[SOAP_XML_SCHEMA_VERSION])) {
                # if we can, lets set php's variable type
                settype($soapval->value, $SOAP_typemap[SOAP_XML_SCHEMA_VERSION][$soapval->type]);
            }
            if (!is_null($soapval->value)) 
                return $this->un_htmlentities($soapval->value);
            return NULL;
        // array decode
        } elseif ($soapval->type_code == SOAP_VALUE_ARRAY) {
            if (is_array($soapval->value)) {
                foreach ($soapval->value as $item) {
                    $return[] = $this->decode($item);
                }
                return $return;
            }
            return $soapval->value;
        // struct decode
        } elseif ($soapval->type_code == SOAP_VALUE_STRUCT) {
            if (is_array($soapval->value)) {
                $counter = 1;
                foreach ($soapval->value as $item) {
                    if (isset($return[$item->name])) {
                        // this is realy an array, we need to redirect
                        $soapval->type_code = SOAP_VALUE_ARRAY;
                        return $soapval->decode();
                    } else {
                        $return[$item->name] = $this->decode($item);
                    }
                }
                return $return;
            }
            return $soapval->value;
        }
        # couldn't decode, return a fault!
        return $this->raiseSoapFault("couldn't decode response, invalid type_code");
    }
    
    /**
    * pass it a type, and it attempts to return a namespace uri
    *
    * @param    
    * @global   $SOAP_typemap, $SOAP_namespaces
    */
    function verifyType($type)
    {
        global $SOAP_typemap;
        foreach ($SOAP_typemap as $uri => $types) {
            if (array_key_exists($type,$types)) return $uri;
        }
        return FALSE;
    }
    
    /** 
    * alias for verifyType() - pass it a type, and it returns it's prefix
    *
    * @brother  varityType()
    */
    function getPrefix($type)
    {
        global $SOAP_namespaces;
        if ($uri = $this->verifyType($type)) {
            return $SOAP_namespaces[$uri];
        }
        return NULL;
    }
    
    
    /**
    * SOAP::Value::_getSoapType
    *
    * convert php type to soap type
    * @param    string  value
    * @param    string  type  - presumed php type
    *
    * @return   string  type  - soap type
    * @access   private
    */
    function _getSoapType(&$value, &$type, $name, $type_namespace='') {
    
        $doconvert = FALSE;
        if ($this->wsdl) {
            # see if it's a complex type so we can deal properly with SOAPENC:arrayType
            if (!$type && $name) {
                # XXX TODO:
                # look up the name in the wsdl and validate the type
                if ($this->type) {
                    foreach ($this->wsdl->complexTypes as $types) {
                        if (array_key_exists($this->type, $types) &&
                            array_key_exists($name, $types[$this->type]['elements'])) {
                            $type = $types[$this->type]['elements']['type'];
                            return $type;
                        }
                    }
                }
            } else if ($type && $type_namespace) {
                # XXX TODO:
                # this code currently handles only one way of encoding array types in wsdl
                # need to do a generalized function to figure out complex types
                $p = $this->wsdl->ns[$type_namespace];
                if ($p &&
                    array_key_exists($p, $this->wsdl->complexTypes) &&
                    array_key_exists($type, $this->wsdl->complexTypes[$p])) {
                    if ($this->arrayType = $this->wsdl->complexTypes[$p][$type]['arrayType']) {
                        $type = 'Array';
                    } else if ($this->wsdl->complexTypes[$p][$type]['order']=='sequence' &&
                               array_key_exists('elements', $this->wsdl->complexTypes[$p][$type])) {
                        reset($this->wsdl->complexTypes[$p][$type]['elements']);
                        # assume an array
                        if (count($this->wsdl->complexTypes[$p][$type]['elements']) == 1) {
                            $arg = current($this->wsdl->complexTypes[$p][$type]['elements']);
                            $this->arrayType = $arg['type'];
                            $type = 'Array';
                        } else {
                            foreach($this->wsdl->complexTypes[$p][$type]['elements'] as $element) {
                                if ($element['name'] == $type) {
                                    $this->arrayType = $element['type'];
                                    $type = $element['type'];
                                }
                            }
                        }
                    }
                    return $type;
                }
            }
        }
        if (!$type || !$this->verifyType($type)) {
            if (is_object($value)) {
                # allows for creating special classes to handle soap types
                $type = get_class($value);
                # this may return a different type that we process below
                $value = $value->toSOAP();
            } elseif ($this->isArray($value)) {
                $type = $this->isHash($value)?'Struct':'Array';
            } elseif ($this->isInt($value)) {
                $type = 'int';
            } elseif ($this->isFloat($value)) {
                $type = 'float';
            } elseif (SOAP_Type_hexBinary::is_hexbin($value)) {
                $type = 'hexBinary';
            } elseif ($this->isBase64($value)) {
                $type = 'base64Binary';
            } elseif ($this->isBoolean($value)) {
                $type = 'boolean';
            } else {
                $type = gettype($value);
                # php defaults a lot of stuff to string, if we have no
                # idea what the type realy is, we have to try to figure it out
                # this is the best we can do if the user did not use the SOAP_Value class
                if ($type == 'string') $doconvert = TRUE;
                elseif ($type == 'NULL') $type = '';
            }
        }
        # we have the type, handle any value munging we need
        if ($doconvert) {
            $dt = new SOAP_Type_dateTime($value);
            if ($dt->toUnixtime() != -1) {
                $type = 'dateTime';
                $value = $dt->toSOAP();
            }
        } else
        if ($type == 'dateTime') {
            # encode a dateTime to ISOE
            $dt = new SOAP_Type_dateTime($value);
            $value = $dt->toSOAP();
        } else
        // php type name mangle
        if ($type == 'integer') {
            $type = 'int';
        } else
        if ($type == 'boolean') {
            if (($value != 0 && $value != '0') || strcasecmp($value, 'true') == 0) 
                $value = 'true';
            else 
                $value = 'false';
        }
        return $type;
    }

    // support functions
    /**
    *
    * @param    string
    * @return   string
    */
    function isBase64(&$value)
    {
        return $value[strlen($value)-1]=='=' && preg_match("/[A-Za-z=\/\+]+/",$value);
    }

    /**
    *
    * @param    mixed
    * @return   boolean
    */
    function isBoolean(&$value)
    {
        return gettype($value) == 'boolean' || strcasecmp($value, 'true')==0 || strcasecmp($value, 'false') == 0;
    }

    /**
    * 
    * @param    mixed
    * @return   boolean
    */
    function isFloat(&$value)
    {
        return gettype($value) == FLOAT ||
                    $value === 'NaN' ||  $value === 'INF' || $value === '-INF' ||
                    (is_numeric($value) && strstr($value, '.'));
    }

    /**
    * 
    * @param    mixed
    * @return   boolean
    */
    function isInt(&$value)
    {
        return gettype($value) == 'integer' || (is_numeric($value) && !strstr($value,'.'));
    }

    /**
    *
    * @param    array
    * @return   boolean
    */
    function isArray(&$value)
    {
        return is_array($value) && count($value) >= 1;
    }

    /**
    *
    * @param    mixed
    * @return   boolean
    */
    function isDateTime(&$value)
    {
        $dt = new SOAP_Type_dateTime($value);
        return $dt->toUnixtime() != -1;
    }

    /**
    *
    * @param    mixed
    * @return   boolean
    */
    function isHash(&$a) {
        # XXX I realy dislike having to loop through this in php code,
        # realy large arrays will be slow.  We need a C function to do this.
        $names = array();
        foreach ($a as $k => $v) {
            # checking the type is faster than regexp.
            if (gettype($k) != 'integer') {
                return TRUE;
            } else if (gettype($v) == 'object' && get_class($v) == 'soap_value') {
                $names[$v->name] = 1;
            }
        }
        return count($names)>1;
    }

    function un_htmlentities($string)
    {
       $trans_tbl = get_html_translation_table (HTML_ENTITIES);
       $trans_tbl = array_flip($trans_tbl);
       return strtr($string, $trans_tbl);
    }
}

?>
