<?php
error_reporting(E_ALL);
ini_set('display_errors', True);


ini_set('soap.wsdl_cache_enabled', '0');
ini_set('soap.wsdl_cache_ttl', '0'); 


echo 'Attempting to make SOAP Connection';
echo '<br/>';
$client = new SoapClient("https://webportal.gtldc.net/InmatePin/InmateService.svc?wsdl");
//$client->__setLocation("https://webportal.gtldc.net/InmatePin/InmateService.svc/basic");
//$response = $client->MethodName(array( "paramName" => "paramValue" ... ));
echo 'Connection made';
echo '<br/>';

echo 'setting up security';
echo '<br/>';
 try {     
     $availFunctions = $client->__getFunctions();
     foreach ($availFunctions as $value) {
         //echo $value;
         //echo '<br/>';
     }
     
     echo '<br/>';
     $availClass = $client->__getTypes();
     foreach ($availClass as $value) {
         //echo $value;
         //echo '<br/>';
     }
     echo '<br/>';
     $securityID = '';
     $transID = '';
    //$SOAPsecurityID = new SoapVar("Guid", "{F0ACB09E-42BC-4EFB-B73F-B2207CE47973}");
    //$securityID = $client->guid("{F0ACB09E-42BC-4EFB-B73F-B2207CE47973}");
    $subID = "CI01";
    //$transID = $client->Guid->NewGuid();
    echo 'Security setting created';
    echo '<br/>';
    
    
    $params = array('transID'=>$transID, 'securityID'=>'{F0ACB09E-42BC-4EFB-B73F-B2207CE47973}', 'inmateid'=>"111111", 'subID'=>$subID, 'comment'=> "Test Search");
    $getInfo = $client->InmateGetInfo($params);

    //$getInfo = $client->InmateGetInfo($transID, $SOAPsecurityID, "111111", $subID, "Test Search");
    echo "First Name: " . $getInfo->FirstName . " Last Name: " . $getInfo->LastName . " Status: " . $getInfo->Active . " ID: " + $getInfo->IDNum . " date: " . $getInfo->SelfLearnStartDate;
    echo '<br/>';
  } 
  catch (SoapFault $exception) {

    echo $exception;      

  }

echo 'complete';
?>