<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Untitled</title>
</head>

<body>

<?php
   //*-----------------------
    include './Value.php';
    include './Message.php';
    include './Response.php';
    include './Client.php';
    
    $send_int=2001;
	$send_string="happy!";
	$send_arr=array(0, 1);
    $send_str=array("year"=>2001,"word"=>"happy");
  
	
    $soap_env=new soapMsg(  'get_array',          //Invoke Method Name
    				 'urn:soap_test',	   //Invoke Target Object Id	
    				 array(				   //Parametres List Below
    				 new soapval('intvalue', $send_int,"int"),
    				 new soapval('stringvalue', $send_string,"string"),
    				 new soapval('arrayvalue', $send_arr, "array"),
                     new soapval('structvalue', $send_str, "struct")));
                     
    print "<B>SOAP Request XML:</B><font color='red'><PRE>\n";
	print htmlspecialchars($soap_env->serialize()) . "\n<br>" ;
    $soapcl=new soapClient($soap_env);
    print_r($soapcl);
    $resp = $soapcl->sendHTTPS('https://secure.diligence.com/index.php');
    print_r($resp);
    //print htmlspecialchars($resp->dump()) . "\n<br>" ;		
    /**/    
    
    /*
    $test = new soapval('someint', $send_int,"int");
    print htmlspecialchars($test->serialize()) . "\n<br>" ;
    $test =  new soapval('somestring' , $send_string,"string");
     print htmlspecialchars($test->serialize()) . "\n<br>" ;
    $test =  new soapval('somestruct', $send_str, "struct");
     print htmlspecialchars($test->serialize()) . "\n<br>" ;
    $test = new soapval('somearray', $send_arr, "array");
     print htmlspecialchars($test->serialize()) . "\n<br>" ;
    print "</PRE></font>";

   */

    ?> 
    
                    
</body>
</html>
