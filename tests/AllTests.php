<?php
/**
 * @category Web Services
 * @package SOAP
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'SOAP_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';


chdir(dirname(__FILE__));
require_once 'SOAP_BugsTest.php';


class SOAP_AllTests
{
    public static function main()
    {

        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('SOAP Tests');
        /** Add testsuites, if there is. */
        $suite->addTestSuite('SOAP_BugsTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'SOAP_AllTests::main') {
    SOAP_AllTests::main();
}
?>