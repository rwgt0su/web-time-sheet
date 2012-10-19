<?php
function displayPosByDateByDiv($config){
    $empClass = new wts_employee();
    $empClass->setMySQLI($config);
    $empClass->lName = "Test";
    $empClass->fName = "fistName";
    $empClass->username = "fTest";
    $empClass->addEmployee();
    $empClass->disableEmployee();
    $empClass->getEmpByID("2");
    $empClass->lName = "Test";
    $empClass->fName = "fistName";
    $empClass->username = "fTest";
    $empClass->updateEmployee();
    
}
function displayPosDetails($config){
    
}

?>
