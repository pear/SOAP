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
 * A soap message object to be used with other soap objects It's
 * basically an container for SOAP_Value objects.
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
 * SOAP_Message($method , $target="" , $params=0) // constructor. Use
 *                                     // this method to create a soap
 *                                     // msg Method is the name of
 *                                     // the method target is the URN
 *                                     // params is an array of
 *                                     // soapval
 *                                           
 * addparameter($par)                  // par is an soapval object
 * getParameter($i)                    // retrieve a paramter by index
 * getNumParameters()                  // returns the parameter count
 * method($meth='')                    // gets or sets the method
 * target($target='')                  // gets or sets the target
 * function serialize()                // serializes the entire
 *                                     // message usually called by
 *                                     // the soapclient object
 */
class SOAP_Message
{
    var $_parameters;
    var $_env_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\"  xmlns:xsi=\"http://www.w3.org/1999/XMLSchema-instance\"  xmlns:xsd=\"http://www.w3.org/1999/XMLSchema\">\n<SOAP-ENV:Body>\n" ;
    var $_env_footer="</SOAP-ENV:Body>\n</SOAP-ENV:Envelope>\n";
	var $_method='';
    var $_target='';
                            
    function SOAP_Message($method , $target="" , $params=0)
    {
        $this->_method=$method;
        $this->_target=$target;
        
        if ($params) {
            if (is_array($params) && sizeof($params)>0) {
                $this->_parameters = $params;
            } else {
                $this->addParam($params);
            }
        }
    }
    
    function addParameter($par) 
    { 
        $this->_parameters[]=$par; 
    }

    function getParameter($i) 
    { 
        return $this->_parameters[$i]; 
    }

    function getNumParameters() 
    { 
        return sizeof($this->_parameters); 
    }
     
    function method($meth = '')
    {
        if ($meth) {
            $this->_method=$meth;
        }
        return $this->_method;
    }

    function target($target = '')
    {
        if ($target) {
            $this->_target=$target;
        }
        return $this->_target;
    }
    
    function serialize()
    {
        $s = $this->_env_header;
        $s .= "<ns1:$this->_method xmlns:ns1='$this->_target' SOAP-ENV:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'>\n";
        foreach ($this->_parameters as $param) {
            $s .= $param->serialize();
        }
        $s .= "</ns1:" . $this->_method . "s>\n";
        $s .= $this->_env_footer;
        
        $s = str_replace("\n", "\r\n", $s);
        return $s;
    }
}
   
?>
