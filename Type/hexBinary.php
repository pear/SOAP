<?php

class hexBinary
{
    function to_bin($value)
    {
        $len = strlen($value);
        return pack("H" . $len, $value);
    }
    function to_hex($value)
    {
        return bin2hex($value);
    }
    function is_hexbin($value)
    {
        # first see if there are any invalid chars
        $l = strlen($value);
        if ($l < 1 || strspn($value, "0123456789ABCDEFabcdef") != $l) return FALSE;
        $bin = hexBinary::to_bin($value);
        $hex = hexBinary::to_hex($bin);
        return strcasecmp($value, $hex) == 0;
    }
}

?>