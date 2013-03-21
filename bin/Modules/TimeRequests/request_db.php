<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function getTimeRequestFiltersBetweenDates($config, $startDate, $endDate) {
    return " AND USEDATE BETWEEN '" . $config->mysqli->real_escape_string(date('Y-m-d', strtotime($startDate))) . "' 
        AND '" . $config->mysqli->real_escape_string(date('Y-m-d', strtotime($endDate))) . "'";
}

function getTimeRequestFiltersByEmpID($config, $empID) {
    return " AND REQUEST.IDNUM = '" . $config->mysqli->real_escape_string($empID) . "'";
}
function getFilterLoggedInUserRequestID($config) {
    return " AND REQUEST.IDNUM = '" . $config->mysqli->real_escape_string($_SESSION['userIDnum']) ."'";
}
function getCurrentPayPeriod(){
    return "SELECT PPBEG, PPEND FROM PAYPERIOD WHERE NOW() BETWEEN PPBEG AND PPEND";
}

function getApproveRequest($config, $refNo, $status, $reason) {
    return "UPDATE REQUEST 
                    SET STATUS='" . $config->mysqli->real_escape_string($status) . "',
                        REASON='" . $config->mysqli->real_escape_string($reason) . "',
                        APPROVEDBY='" . $config->mysqli->real_escape_string($_SESSION['userIDnum']) . "', ApprovedTS=NOW() 
                    WHERE REFER='" . $config->mysqli->real_escape_string($refNo) . "'";
}

function getTimeRequestTable($config, $filters, $orderBy, $limit = '') {
    return "SELECT REFER 'RefNo', REQ.MUNIS 'Munis', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', 
                    DATE_FORMAT(USEDATE,'%c-%d-%Y %a') 'Used', STATUS 'Status',
                    DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                    DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                    DATE_FORMAT(REQDATE,'%c-%d-%Y %H%i') 'Request_Date',
                    T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', 
                    APR.LNAME 'ApprovedBy', REQUEST.EX_REASON AS 'EXPUNGE_NOTES',
                    DATE_FORMAT(REQUEST.ApprovedTS,'%b %d, %Y') 'approveTS',
                    REASON 'Reason', HRAPP_IS 'HR_Approved', HR.LNAME 'HRLName', HR.FNAME 'HRFName', REQUEST.HR_NOTES AS 'HRNOTES'
                FROM REQUEST
                LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
                LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
                LEFT JOIN EMPLOYEE AS HR ON HR.IDNUM=REQUEST.HRAPP_ID
                INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=REQUEST.TIMETYPEID
                WHERE 1
                " . $filters . "
                " . $config->mysqli->real_escape_string($orderBy) . "
                " . $config->mysqli->real_escape_string($limit) . "  
                ";
}
function getLimitFilter($config, $prevNum, $limit){
    return " LIMIT ".$config->mysqli->real_escape_string($prevNum).",  ".$config->mysqli->real_escape_string($limit);
}
function getFilerDivision($config, $divID){
    return " AND REQ.DIVISIONID = '".$config->mysqli->real_escape_string($divID). "'";
}

function getSendToPending($config, $refNo, $hrNotes) {  
    return "UPDATE REQUEST 
        SET STATUS='PENDING',
        `HRAPP_IS` = '0',
        `HR_NOTES` = '" . $config->mysqli->real_escape_string($hrNotes) . "',
        APPROVEDBY=''
        WHERE REFER=" . $config->mysqli->real_escape_string($refNo);
}
function getHRApprovalByRef($config, $refNo, $reason){
    return "UPDATE REQUEST SET `TSTAMP` = NOW( ) ,
            `HRAPP_IS` = '1',
            `HRAPP_ID` = '" . $config->mysqli->real_escape_string($_SESSION['userIDnum']) . "',
            `HRAPP_IP` = INET_ATON('" . $config->mysqli->real_escape_string($_SERVER['REMOTE_ADDR']) . "'),
            `HR_NOTES` = '" . $config->mysqli->real_escape_string($reason) . "',
            `HRAPP_TIME` = NOW( ) WHERE `REQUEST`.`REFER` =" . $config->mysqli->real_escape_string($refNo);
}
function getShiftsByDivision($config, $divID){
    return "SELECT IDNUM, NAME, 
            DATE_FORMAT(BEGTIME,'%H%i') AS 'BEG_TIME',
            DATE_FORMAT(ENDTIME ,'%H%i') AS 'END_TIME'
        FROM SHIFT
        WHERE DIVISION_ID = '" . $config->mysqli->real_escape_string($divID) . "' AND
            ENDDATE IS NULL
        ORDER BY SORT";
}
function getShiftsByID($config, $ID){
    return "SELECT IDNUM, NAME, 
            DATE_FORMAT(BEGTIME,'%H%i') AS 'BEG_TIME',
            DATE_FORMAT(ENDTIME ,'%H%i') AS 'END_TIME'
        FROM SHIFT
        WHERE IDNUM = '" . $config->mysqli->real_escape_string($ID) . "' AND
            ENDDATE IS NULL
        ORDER BY SORT";
}
function getReqestsBetweenTimes($Start, $End, $useOR = false){
    if(!$useOR)
        $logic = " AND ";
    else
        $logic = " OR ";
    return  $logic . "TIME_FORMAT(BEGTIME, '%H%I') BETWEEN TIME_FORMAT('".$Start."00', '%H%I') AND TIME_FORMAT('".$End."00', '%H%I')";
    
}
function getTimeTypes(){
    return "SELECT TIMETYPEID, DESCR FROM TIMETYPE";
}
function getExpungeRequest($config, $refNo, $reason){
    return "UPDATE REQUEST SET STATUS='EXPUNGED',
                    HRAPP_ID='0',
                    EX_REASON='" . $config->mysqli->real_escape_string($reason) . "',
                    AUDITID='" . $config->mysqli->real_escape_string($_SESSION['userIDnum']) . "',
                    IP= INET_ATON('" . $config->mysqli->real_escape_string($_SERVER['REMOTE_ADDR']) . "')
                    WHERE REFER='" . $config->mysqli->real_escape_string($refNo) . "'";
}

?>
