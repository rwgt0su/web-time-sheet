<?php # HelloServer.php
# Copyright (c) 2005 by Dr. Herong Yang
#
error_reporting(E_ALL);
ini_set('display_errors', True);


function sayHello($msg){
    $newMsg = 'Message Passed: '.$msg;
    return $newMsg;
}
function getItemCount($upc){
    print 'Message Passed: ';
//in reality, this data would be coming from a database
$items = array('12345'=>5,'19283'=>100,'23489'=>234);
return $items[$upc];
}

ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$server = new SoapServer("catalog.wsdl");
$server->addFunction("getItemCount");
$server->addFunction("sayHello");
$server->setClass("MySoapServer");
$server->handle();
?>