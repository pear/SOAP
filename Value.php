<?php
/*
 * Copyright (c) 2001 Tim Uckun
 * All rights reserved.
 * 
 * This Software is distributed under the GPL. For a list of your
 * obligations and rights under this license please visit the GNU
 * website at http://www.gnu.org/
 *
 */

/**
 * A SOAP variable object to be used with other SOAP objects.
 *
 * @version 0.01
 * @author Tim Uckun <tim@diligence.com>
 *
 * Interface.
 * 
 * public variables:
 *     None please don't attempt to directly access variables in objects
 *         
 * Public Functions
 * 
 * function SOAP_Value($name, $value [, $type])// constructor. Use
 *                                             // this method to set
 *                                             // your variables it
 *                                             // will guess at the
 *                                             // type if you don't
 *                                             // specify it's
 *                                             // acceptable to leave
 *                                             // name blank (empty
 *                                             // string)
 *                                             
 * function serialize()                        // will output the XML
 *                                             // fragment
 *                                             // representing this
 *                                             // variable it will be
 *                                             // called by other
 *                                             // objects no need to
 *                                             // call it yourself
 *                 
 */
class SOAP_Value
{
    // all variables are private
    var $_name ='';
    var $_value;
    var $_type = '';
                                     
  	                    
    /**
     * Constructor Creates a new soap variable
     *
     * This is the constructor for this class
     *
     * @param    string  Name of the soap variable
     * @param    mixed   The contents of the variable
     * @param    string  Optional variable type.
     *                   If you don't specify the type it will guess 
     *                   If the type is an array or struct it will
     *                   guess about the contents of the struct.    
     *                   You can use this to coerce soap types like
     *                   int to boolean 
     *                     
     * @return   void
     */

    function SOAP_Value($name, $value, $type = '') 
    {
        $this->_name=$name;
        $this->_type=$this->_getSoapType($value , $type);
        $this->_value=$this->_getSoapValue ($value, $this->_type);
    }
            
    function serialize()
    {
        switch($this->_type) {
            case 'array':
                return $this->_serializeArray($this->_name, $this->_value);
                break;
            case 'struct':
                return $this->_serializeStruct($this->_name, $this->_value);
                break;
            default:
                return $this->_serializeScalar($this->_name, $this->_value, $this->_type);
                break;
        }
    }   

    function _serializeStruct($name, $struct)
    {
        $s = "<$name xmlns:ns2='http://xml.apache.org/xml-soap' xsi:type='ns2:Map'>\n";
        reset($struct);
        while(list($key, $value) = each($struct)) {
            $s .="<item>\n";
            $type = $this->_getSoapType($value);
            $value= $this->_getSoapValue($value, $type);
            
            $keytype  = $this->_getSoapType($key);
            $keyvalue = $this->_getSoapValue($key, $keytype);
            
            $s .= "<key xsi:type='xsd:$keytype'>$keyvalue</key>\n";
            $s .= "<value xsi:type='xsd:$type'>$value</value>\n";
            $s .="</item>\n";
        }
        $s .= "</$name>\n";
        return $s;
    }
    
    function _serializeArray($name, $values)
    {
        $s ="<$name xmlns:ns2=\"http://schemas.xmlsoap.org/soap/encoding/\" xsi:type=\"ns2:Array\" ns2:arrayType=\"xsd:ur-type[".sizeof($val)."]\">\n";
        //echo "size of array is:".sizeof($val);
        for ($i = 0; $i < sizeof($values); $i++) {
            $itemname = "item";
            $itemtype = $this->_getSoapType($values[$i]);
            $itemvalue= $this->_getSoapValue($values[$i], $type);
            $s.= $this->_serializeScalar($itemname, $itemvalue, $itemtype);
        }
        $s .= "</$name>\n";
        return $s;  
    } 

    function _serializeScalar($name, $value, $type)
    {
        return "<$name xsi:type='xsd:$type'>$value</$name>\n";
    }
    
    function _isValidSoapType($type)
    {
        static $validSoapTypes;
        if (empty($validSoapTypes)) {
            $validSoapTypes = array(
                "i4" => true,
                "int" => true,
                "boolean" => true,
                "double" => true,
                "string" => true,
                "dateTime.iso8601" => true,
                "base64" => true,
                "array" => true,
                "struct" => true,
            );
        }
        return (bool)$validSoapTypes[$type]);
    }
    
    function _getSoapValue($value, $type)
    {
        // don't send anything here unless you ran
        // it through  get soaptype first
        // this function needs good soap types
        // maybe we could combine the two functions...
        switch ($type) {
            case 'boolean':
               if ($value === "true" || $value === 1 || $value === true) {
                   $value = 1;
               } else {
                   $value = 0;
               }
               break;
            case 'integer':
                // just to catch some oddball values
                $value = (int) $value;
                break;
            case 'double':
                $value = doubleval($value);
                break;
            case 'string':
                // XXX this will probably quote more than is required [ssb]
                $value = htmlspecialchars($value);
                break;
            case 'base64':
                $value = base64_encode($value);
                break;
            default:
                // for all other types we do nothing
                // do nothing for now
                break;
        }
        return $value;
    }
                   
    function _getSoapType($value, $type = '')
    {
        if ($type) {
            // A type was specified so map it to lower
            $type = strtolower($type);
            
            if (!$this->_isValidSoapType($type)) {
                // a type was specified but we don't recognize it.
                // let's just make it a string.
                $type = 'string'; 
            }
        }
        
        // We still don't know the type 
        // Let's guess
        
        if (!$type) {
         
            // let's try to guess
            // get the php type
            $type = gettype($value);
            switch ($type) {
                case 'boolean':
                    // do nothing
                    break;
                case 'integer':
                    $type = 'int';
                    break;
                case 'double':
                    // do nothing
                    break;
                case 'string':
                    // do nothing
                    break;
                case 'array':
                    // do nothing
                    break;
                case 'object':
                    $type = 'struct';
                    break;
                default:
                    // How could we still not know?
                    $type = 'string';
                    break;
            }
        }
        return $type;
    }
}
    
?>
