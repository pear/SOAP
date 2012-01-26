<?php
/**
 * @category Web Services
 * @package SOAP
 */

require_once 'SOAP/Parser.php';
require_once 'SOAP/Value.php';

function number_compare($f1, $f2)
{
    // figure out which has the least fractional digits
    preg_match('/.*?\.(.*)/', $f1, $m1);
    preg_match('/.*?\.(.*)/', $f2, $m2);
    //print_r($m1);
    // always use at least 2 digits of precision
    $d = max(min(strlen(count($m1)?$m1[1]:'0'), strlen(count($m2)?$m2[1]:'0')) ,2);
    $f1 = round($f1, $d);
    $f2 = round($f2, $d);

    if (function_exists('bccomp')) {
        return bccomp($f1, $f2, $d) == 0;
    }

    return $f1 == $f2;
}

function boolean_compare($f1, $f2)
{
    if (($f1 == 'true' || $f1 === true || $f1 != 0) &&
        ($f2 == 'true' || $f2 === true || $f2 != 0)) return true;
    if (($f1 == 'false' || $f1 === false || $f1 == 0) &&
        ($f2 == 'false' || $f2 === false || $f2 == 0)) return true;
    return false;
}

function string_compare($e1, $e2)
{
    if (!is_string($e1) || !is_string($e2)) {
        return false;
    }
    $e1 = trim(str_replace(array("\r", "\n"), '', $e1));
    $e2 = trim(str_replace(array("\r", "\n"), '', $e2));

    if (is_numeric($e1) && is_numeric($e2)) {
        return number_compare($e1, $e2);
    }
    // handle dateTime comparison
    $e1_type = gettype($e1);
    $e2_type = gettype($e2);
    $ok = false;
    if ($e1_type == "string") {
        require_once 'SOAP/Type/dateTime.php';
        $dt = new SOAP_Type_dateTime();
        $ok = $dt->compare($e1, $e2) == 0;
    }
    return $ok || $e1 == $e2 || strcasecmp($e1, $e2) == 0;
}

function array_compare(&$ar1, &$ar2)
{
    if (gettype($ar1) != 'array' || gettype($ar2) != 'array') return false;
    // first a shallow diff
    if (count($ar1) != count($ar2)) return false;
    $diff = array_diff($ar1, $ar2);
    if (count($diff) == 0) return true;

    // diff failed, do a full check of the array
    foreach ($ar1 as $k => $v) {
        //print "comparing $v == $ar2[$k]\n";
        if (gettype($v) == 'array') {
            if (!array_compare($v, $ar2[$k])) return false;
        } elseif (is_object($v)) {
            if (!object_compare($v, $ar2[$k])) return false;
        } else {
            if (!string_compare($v, $ar2[$k])) return false;
        }
    }
    return true;
}

function object_compare(&$o1, &$o2)
{
    if (!is_object($o1) || !is_object($o2) || gettype($o1) != gettype($o2)) {
        return false;
    }
    $o1 = (array)$o1;
    $o2 = (array)$o2;
    return array_compare($o1, $o2);
}

function parseMessage($msg)
{
    // strip line endings
    // $msg = preg_replace('/\r|\n/', ' ', $msg);
    $parser = new SOAP_Parser($msg);
    if ($parser->fault) {
        return $parser->fault->getFault();
    }
    $response = $parser->getResponse();
    $v = $parser->_decode($response);
    if (gettype($v) == 'array' && count($v) === 1) {
        return array_shift($v);
    }
    return $v;
}
