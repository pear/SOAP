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
 * A soap response object to be used with other soap objects.  Nothing
 * here yet just a skeleton.
 *
 * @version 0.01
 * @author Tim Uckun <tim@diligence.com>
 * 
 * Interface.
 * 
 * not much to see here just a skeleton to get stated working..
 * 
 * public variables:
 *     None please don't attempt to directly access variables in objects
 *		
 * Public Functions
 *	
 * SOAP_Response($val)              // constructor. Use this method to
 *                                  // create a soap resp $val is the
 *                                  // raw XML from your client..
 * dump()                          // dumps the raw XML 
 */
class SOAP_Response
{
    var $_value;
    var $_faultCode;
    var $_faultString;
    var $_headers;
    var $_XML;
    
    function SOAP_Response($val)
    {
        $this->_XML=$val;
        $this->_value = xmltree($val);
    }

    function dump()
    {
        return $this->_value;
    }
} // end of class SOAP_Response
	
?>
