<?php
require_once '../Schema.php';
/*
* this is a data type that is used in SOAP Interop testing, but is
* here as an example of using complex types.  When the class is
* deserialized from a SOAP message, it's constructor IS NOT CALLED!
* So your type classes need to behave in a way that will work
* with that.
*
* Some types may need more explicit serialization for SOAP.  The
* __to_soap function allows you to be very explicit in building the
* SOAP_Value structures.  The soap library does not call this directly,
* you would call it from your soap server class, echoStruct in the server
* class is an example of doing this.
*/
class SOAPStruct implements SchemaTypeInfo {
    public $varInt = 123;
    public $varFloat = 123.123;
    public $varString = 'hello world';

    public static function getTypeName() { return 'SOAPStruct'; }
    public static function getTypeNamespace() { return 'http://soapinterop.org/xsd'; }
}

?>