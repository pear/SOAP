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
require_once('SOAP/globals.php');
require_once('SOAP/Type/dateTime.php');
require_once('SOAP/Type/hexBinary.php');
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

class SOAP_Value
{
    var $convert_strings = FALSE;
    var $value = "";
    var $type_code = 0;
    var $type_prefix = false;
    var $array_type = "";
    var $debug_flag = true;
    var $debug_str = "";
    var $name = "";
    var $type;
    var $soapTypes;
    var $namespace = "";
    var $prefix = "";
    
    function SOAP_Value($name="",$type=false,$value=-1,$namespace=false,$type_namespace=false)
    {
        global $soapTypes, $SOAP_typemap, $SOAP_namespaces, $methodNamespace, $SOAP_XMLSchemaVersion;
        // detect type if not passed
        
        #print("Entering SOAP_Value - name: '$name' type: '$type' value: $value\n");
        $this->soapTypes = $soapTypes;
        $this->name = $name;
        $this->type = $this->_getSoapType($value, $type);
        #$this->debug("Entering SOAP_Value - name: '$name' type: '$type' value: $value");
        #print("Entering SOAP_Value - name: '$name' type: '$type' value: $value\n");
        
        if ($namespace) {
            $this->namespace = $namespace;
            if (!isset($SOAP_namespaces[$namespace])) {
                $SOAP_namespaces[$namespace] = "ns".(count($SOAP_namespaces)+1);
            }
            $this->prefix = $SOAP_namespaces[$namespace];
        }
        
        // get type prefix
        if (strpos($type,":")!==false) {
            $this->type = substr(strrchr($type,":"),1,strlen(strrchr($type,":")));
            $this->type_prefix = substr($type,0,strpos($type,":"));
        } elseif ($type_namespace) {
            if (!isset($SOAP_namespaces[$type_namespace])) {
                $SOAP_namespaces[$type_namespace] = "ns".(count($SOAP_namespaces)+1);
            }
            $this->type_prefix = $SOAP_namespaces[$type_namespace];
        // if type namespace was not explicitly passed, and we're not in a method struct:
        } elseif (!$this->type_prefix && $type != "struct" /*!isset($type_namespace)*/) {
            // try to get type prefix from typeMap
            if ($ns = $this->verifyType($this->type)) {
                $this->type_prefix = $SOAP_namespaces[$ns];
            } else {
                // else default to method namespace
                $this->type_prefix = $SOAP_namespaces[$methodNamespace];
            }
        }
        
        // if scalar
        if (in_array($this->type,$SOAP_typemap[$SOAP_XMLSchemaVersion])) {
            $this->type_code = 1;
            $this->addScalar($value,$this->type,$name);
        // if array
        } elseif (strcasecmp("array",$this->type) == 0 ||
             strcasecmp("ur-type",$this->type) == 0) {
            $this->type_code = 2;
            $this->addArray($value);
        // if struct
        } elseif (stristr($this->type,"struct")) {
            $this->type_code = 3;
            $this->addStruct($value);
        } elseif (is_array($value)) {
            $this->type_code = 3;
            $this->addStruct($value);
        } else {
            $this->type_code = 1;
            $this->addScalar($value,"string",$name);
        }
    }
    
    function addScalar($value, $type, $name="")
    {
        $this->debug("adding scalar '$name' of type '$type'");
        
        $this->value = $value;
        return true;
    }
    
    function addArray($vals)
    {
        $this->debug("adding array '$this->name' with ".count($vals)." vals");
        $this->value = array();
        if (is_array($vals) && count($vals) >= 1) {
            foreach ($vals as $k => $v) {
                $this->debug("checking value $k : $v");
                // if SOAP_Value, add..
                if (strcasecmp(get_class($v),"SOAP_Value")==0) {
                    $this->value[] = $v;
                    $this->debug($v->debug_str);
                // else make obj and serialize
                } else {
                    $type = "";
                    $type = $this->_getSoapType($v, $type);
                    $new_val =  new SOAP_Value("item",$type,$v);
                    $this->debug($new_val->debug_str);
                    $this->value[] = $new_val;
                }
            }
        }
        return true;
    }

    function addStruct($vals)
    {
        $this->debug("adding struct '$this->name' with ".count($vals)." vals");
        if (is_array($vals) && count($vals) >= 1) {
            foreach ($vals as $k => $v) {
                // if serialize, if SOAP_Value
                if (strcasecmp(get_class($v),"SOAP_Value")==0) {
                    $this->value[] = $v;
                    $this->debug($v->debug_str);
                // else make obj and serialize
                } else {
                    $type = NULL;
                    $type = $this->_getSoapType($v, $type);
                    $new_val = new SOAP_Value($k,$type,$v);
                    $this->debug($new_val->debug_str);
                    $this->value[] = $new_val;
                }
            }
        } else {
            $this->value = array();
        }
        return true;
    }
    
    // turn SOAP_Value's into xml, woohoo!
    function serializeval($soapval=false)
    {
        if (!$soapval) {
            $soapval = $this;
        }
        $this->debug("serializing '$soapval->name' of type '$soapval->type'");
        if (is_int($soapval->name)) {
            $soapval->name = "item";
        }
        
        $xml = "";
        switch ($soapval->type_code) {
        case 3:
            // struct
            $this->debug("got a struct");
            if ($soapval->prefix && $soapval->type_prefix) {
                $xml .= "<$soapval->prefix:$soapval->name xsi:type=\"$soapval->type_prefix:$soapval->type\">\n";
            } elseif ($soapval->type_prefix) {
                $xml .= "<$soapval->name xsi:type=\"$soapval->type_prefix:$soapval->type\">\n";
            } elseif ($soapval->prefix) {
                $xml .= "<$soapval->prefix:$soapval->name>\n";
            } else {
                $xml .= "<$soapval->name>\n";
            }
            if (is_array($soapval->value)) {
                foreach ($soapval->value as $k => $v) {
                    $xml .= $this->serializeval($v);
                }
            }
            if ($soapval->prefix) {
                $xml .= "</$soapval->prefix:$soapval->name>\n";
            } else {
                $xml .= "</$soapval->name>\n";
            }
            break;
        case 2:
            // array
            foreach ($soapval->value as $array_val) {
                $array_types[$array_val->type] = 1;
                $xml .= $this->serializeval($array_val);
            }
            if (count($array_types) > 1) {
                $array_type = "xsd:ur-type";
            } elseif (count($array_types) >= 1) {
                if ($array_val->type_prefix != "") {
                    $array_type = $array_val->type_prefix.":".$array_val->type;
                } else {
                    $array_type = $array_val->type;
                }
            }
            
            $xml = "<$soapval->name xsi:type=\"SOAP-ENC:Array\" SOAP-ENC:arrayType=\"".$array_type."[".sizeof($soapval->value)."]\">\n".$xml."</$soapval->name>\n";
            break;
        case 1:
            if ($soapval->prefix && $soapval->type_prefix) {
                $xml .= "<$soapval->prefix:$soapval->name xsi:type=\"$soapval->type_prefix:$soapval->type\">$soapval->value</$soapval->prefix:$soapval->name>\n";
            } elseif ($soapval->type_prefix) {
                $xml .= "<$soapval->name xsi:type=\"$soapval->type_prefix:$soapval->type\">$soapval->value</$soapval->name>\n";
            } elseif ($soapval->prefix) {
                $xml .= "<$soapval->prefix:$soapval->name>$soapval->value</$soapval->prefix:$soapval->name>\n";
            } elseif ($soapval->type) {
                $xml .= "<$soapval->name xsi:type=\"$soapval->type\">$soapval->value</$soapval->name>\n";
            } else {
                $xml .= "<$soapval->name>$soapval->value</$soapval->name>\n";
            }
            break;
        default:
            break;
        }
        return $xml;
    }
    
    // serialize
    function serialize()
    {
        return $this->serializeval($this);
    }
    
    function decode($soapval=false)
    {
        global $SOAP_XMLSchemaVersion, $SOAP_typemap;
        if (!$soapval) {
            $soapval = $this;
        }
        $this->debug("inside SOAP_Value->decode for $soapval->name of type $soapval->type and value: $soapval->value");
        // scalar decode
        if ($soapval->type_code == 1) {
            if ($soapval->type == "boolean") {
                #echo strcasecmp($soapval->value,"false");
                if ($soapval->value != "0" &&
                    strcasecmp($soapval->value,"false") !=0) {
                    $soapval->value = TRUE;
                } else {
                    $soapval->value = FALSE;
                }
            #} else if ($soapval->type == "dateTime") {
            #    # we don't realy know what a user want's in return,
            #    # but we'll just do unix time stamps for now
            #    # THOUGHT: we could return a class instead.
            #    $dt = new SOAP_Type_dateTime($soapval->value);
            #    $soapval->value = $dt->toUnixtime();
            } else if (in_array($soapval->type, $SOAP_typemap[$SOAP_XMLSchemaVersion], TRUE)) {
                # if we can, lets set php's variable type
                settype($soapval->value,$SOAP_typemap[$SOAP_XMLSchemaVersion][$soapval->type]);
            }
            #print "value: $soapval->value type: $soapval->type phptype: {$SOAP_typemap[$SOAP_XMLSchemaVersion][$soapval->type]}\n";
            return $soapval->value;
        // array decode
        } elseif ($soapval->type_code == 2) {
            if (is_array($soapval->value)) {
                foreach ($soapval->value as $item) {
                    $return[] = $this->decode($item);
                }
                return $return;
            }
            return $soapval->value;
        // struct decode
        } elseif ($soapval->type_code == 3) {
            if (is_array($soapval->value)) {
                $counter = 1;
                foreach ($soapval->value as $item) {
                    if (isset($return[$item->name])) {
                        $return[$item->name.($counter++)] = $this->decode($item);
                    } else {
                        $return[$item->name] = $this->decode($item);
                    }
                }
                return $return;
            }
            return $soapval->value;
        }
        # couldn't decode, return a fault!
        return array(
                        'faultcode' => 'SOAP-ENV:Value',
                        'faultstring' => 'couldn\'t decode response, invalid type_code',
                        'faultdetail' => ''
                    );
    }
    
    // pass it a type, and it attempts to return a namespace uri
    function verifyType($type)
    {
        global $SOAP_typemap,$SOAP_namespaces,$SOAP_XMLSchemaVersion;
        /*foreach ($SOAP_typemap as $namespace => $types) {
            if (is_array($types) && in_array($type,$types)) {
                return $namespace;
            }
        }*/
        foreach ($SOAP_namespaces as $uri => $prefix) {
            if (is_array($SOAP_typemap[$uri]) && isset($SOAP_typemap[$uri][$type])) {
                #print "returning: $uri for type $type\n";
                return $uri;
            }
            #print "$type not in: $uri\n";
        }
        #print "$type not found\n";
        return false;
    }
    
    // alias for verifyType() - pass it a type, and it returns it's prefix
    function getPrefix($type)
    {
        if ($prefix = $this->verifyType($type)) {
            return $prefix;
        }
        return false;
    }
    
    
    /**
    * SOAP::Value::_getSoapType
    *
    * convert php type to soap type
    * @params string value
    * @params string type  - presumed php type
    *
    * @return string type  - soap type
    * @access private
    */
    function _getSoapType(&$value, &$type) {
        $doconvert = FALSE;
        if (!$type) {
            if (is_object($value)) {
                # allows for creating special classes to handle soap types
                $type = get_class($value);
                # this may return a different type that we process below
                $value = $value->toSOAP();
            } elseif (isArray($value)) {
                foreach ($value as $k => $v) {
                    if (preg_match("/^[0-9]+$/",$k)) {
                        $type = "array";
                    } else {
                        $type = "struct";
                    }
                    break;
                }
            } elseif (isInt($value)) {
                $type = "int";
            } elseif (isFloat($value)) {
                $type = "float";
            } elseif (hexBinary::is_hexbin($value)) {
                $type = "hexBinary";
            } elseif (isBase64($value)) {
                $type = "base64Binary";
            } elseif (isBoolean($value)) {
                $type = "boolean";
            } else {
                $type = gettype($value);
                # php defaults a lot of stuff to string, if we have no
                # idea what the type realy is, we have to try to figure it out
                # this is the best we can do if the user did not use the SOAP_Value class
                if ($type == "string") $doconvert = TRUE;
            }
        }
        # we have the type, handle any value munging we need
        if ($doconvert) {
            $dt = new SOAP_Type_dateTime($value);
            if ($dt->toUnixtime() != -1) {
                $type = "dateTime";
                $value = $dt->toSOAP();
            }
        } else
        if ($type == "dateTime") {
            # encode a dateTime to ISOE
            $dt = new SOAP_Type_dateTime($value);
            $value = $dt->toSOAP();
        } else
        // php type name mangle
        if ($type == "integer") {
            $type = "int";
        } else
        if ($type == "boolean") {
            if (($value != 0 && $value !='0') || strcasecmp($value,"true")==0) $value = "true";
            else $value = "false";
        }
        return $type;
    }

    function debug($string)
    {
        if ($this->debug_flag) {
            $this->debug_str .= "SOAP_Value: ".preg_replace("/>/","/>\r\n/",$string)."\n";
        }
    }
}

// support functions
function isBase64($value)
{
    return $value[strlen($value)-1]=="=" && preg_match("/[A-Za-z=\/\+]+/",$value);
}

function isBoolean($value)
{
    return gettype($value) == "boolean" || strcasecmp($value, "true")==0 || strcasecmp($value, "false")==0;
}

function isFloat($value)
{
    return gettype($value) == FLOAT ||
                $value === "NaN" ||  $value === "INF" || $value === "-INF" ||
                (is_numeric($value) && strstr($value,"."));
}

function isInt($value)
{
    return gettype($value) == "integer" || (is_numeric($value) && !strstr($value,"."));
}

function isArray($value)
{
    return is_array($value) && count($value) >= 1;
}

function isDateTime($value)
{
    $dt = new SOAP_Type_dateTime($value);
    return $dt->toUnixtime() != -1;
}
?>
