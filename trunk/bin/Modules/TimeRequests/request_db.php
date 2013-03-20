<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function getTimeRequestFiltersBetweenDates($config, $startDate, $endDate) {
    return "USEDATE BETWEEN '" . $config->mysqli->real_escape_string($startDate->format('Y-m-d')) . "' 
        AND '" . $config->mysqli->real_escape_string($endDate->format('Y-m-d')) . "'";
}

function getTimeRequestFiltersByEmpID($config, $empID) {
    return " REQUEST.IDNUM = '" . $config->mysqli->real_escape_string($empID) . "'";
}
function getFilterLoggedInUserRequestID($config) {
    return " REQUEST.IDNUM = '" . $config->mysqli->real_escape_string($_SESSION['userIDnum']) ."'";
}

function getApproveRequest($refNo, $status, $reason) {
    return "UPDATE REQUEST 
                    SET STATUS='" . $mysqli->real_escape_string($status) . "',
                        REASON='" . $mysqli->real_escape_string($reason) . "',
                        APPROVEDBY='" . $mysqli->real_escape_string($_SESSION['userIDnum']) . "', ApprovedTS=NOW() 
                    WHERE REFER='" . $config->mysqli->real_escape_string($refNo) . "'";
}

function getTimeRequestTable($config, $filters, $orderBy) {
    return "SELECT REFER 'RefNo', REQ.MUNIS 'Munis', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', 
                DATE_FORMAT(USEDATE,'%b %d, %Y - %a') 'Used', STATUS 'Status',
                    DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                    DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                    T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', 
                    APR.LNAME 'ApprovedBy', 
                    DATE_FORMAT(REQUEST.ApprovedTS,'%b %d, %Y') 'approveTS',
                    REASON 'Reason', HRAPP_IS 'HR_Approved', HR.LNAME 'HRLName', HR.FNAME 'HRFName', REQUEST.HR_NOTES AS 'HRNOTES'
                FROM REQUEST
                LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
                LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
                LEFT JOIN EMPLOYEE AS HR ON HR.IDNUM=REQUEST.HRAPP_ID
                INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=REQUEST.TIMETYPEID
                WHERE
                " . $filters . "
                " . $config->mysqli->real_escape_string($orderBy) . "
                ";
}

function getSendToPending($config, $refNo, $hrNotes) {
    "`HR_NOTES` = '" . $config->mysqli->real_escape_string($hrNotes) . "',";
    return "UPDATE REQUEST 
        SET STATUS='PENDING',
        `HRAPP_IS` = '0',
        " . $hrNotes . "
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
function getTimeTypes(){
    return "SELECT TIMETYPEID, DESCR FROM TIMETYPE";
}
function getExpungeRequest($config, $refNo, $reason){
    return "UPDATE REQUEST SET STATUS='EXPUNGED',
                    HRAPP_ID='0',
                    EX_REASON='" . $config->mysqli->real_escape_string() . "',
                    AUDITID='" . $config->mysqli->real_escape_string($_SESSION['userIDnum']) . "',
                    IP= INET_ATON('" . $config->mysqli->real_escape_string($_SERVER['REMOTE_ADDR']) . "')
                    WHERE REFER='" . $config->mysqli->real_escape_string($refNo) . "'";
}

?>
