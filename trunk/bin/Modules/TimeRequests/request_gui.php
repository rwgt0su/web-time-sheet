<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'bin/Modules/TimeRequests/request_class.php';
include_once 'bin/Modules/TimeRequests/request_db.php';

include_once 'bin/Modules/TimeRequests/request_approvals_class.php';
include_once 'bin/Modules/TimeRequests/request_reports_class.php';

function displaySubmittedRequestsNEW($config){
    $requestReports = new request_reports();
    $requestReports->config = $config;
    $hiddenInputs = '';
    $requestReports->showTimeRequestsByDate($hiddenInputs);
}

?>
