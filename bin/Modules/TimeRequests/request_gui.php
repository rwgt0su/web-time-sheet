<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'bin/Modules/TimeRequests/request_class.php';
include_once 'bin/Modules/TimeRequests/request_form.php';
include_once 'bin/Modules/TimeRequests/request_db.php';

include_once 'bin/Modules/TimeRequests/request_approvals_class.php';
include_once 'bin/Modules/TimeRequests/request_reports_class.php';

function displaySubmittedRequestsNEW($config){
    $requestReports = new request_reports();
    $requestReports->config = $config;
    $hiddenInputs = '';
    $requestReports->showTimeRequestsByDate($hiddenInputs);
}
function displayNewTimeRequestForm($config){
    echo '<form method="POST" name="employeeTimeRequest">';
    $timeRequest = new time_request_form($config);
    $timeRequest->showTimeRequestForm();
}
function displayLeaveApprovalNEW($config){
    $requestReports = new request_reports();
    $requestReports->config = $config;
    $hiddenInputs = '';
    $requestReports->filters = getFilterByStatus($status = 'PENDING', $useOr = false);
    $requestReports->showTimeRequestsByDate($hiddenInputs);
    
}
function displayMySubmittedRequestsNEW($config){
    $requestReports = new request_reports();
    $requestReports->config = $config;
    $hiddenInputs = '';
    $requestReports->filters = getFilterEmpID($config, $_SESSION['userIDnum']);
    $requestReports->showTimeRequestsByDate($hiddenInputs, $showCustomDates = true, $showPayPeriods = true, $showDivisions = false);
}

?>
