<?php # HelloClient.php
# Copyright (c) 2005 by Dr. Herong Yang
#
error_reporting(E_ALL);
ini_set('display_errors', True);

ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache
$client = new SoapClient("catalog.wsdl");
$return = $client->getItemCount('12345');
print_r($return);

$return = $client->sayHello('Hello World!');
echo $return;

?>