<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
Class request_db {
    public $config;
    public $filters;
    
    public function request_db($config){
        $this->config = $config;
        $this->filters = '';
    }
    public function getTimeTypes(){
        $myq = "SELECT `IDNUM`, `DESCR`, `SORT_ORDER`, `VACATION`, `PERSONAL`, `SICK`,
                    `AT_USED`, `AT_GAIN`, `OVERTIME_PAY`, `STRAIGHT`, `LEAVE_NO_PAY`, 
                    `LIMIT_8_12`, `CALENDAR_LIMIT_DAYS`, `CONSECUTIVE_LIMIT_DAYS`, `AUTO_CALC_HOURS`, 
                    ELEVATE_SUPERVISORS
                FROM WTS_TIMETYPES 
                WHERE ENABLED = 1
                ".$this->filters."
                ORDER BY SORT_ORDER";
        $result = getQueryResult($this->config, $myq, $debug = false);
        
        return $result;
    }
    public function getSubTimeTypes($subTypeInfo){       
        //get column names to display        
        $colq = "SELECT IDNUM, DESCR, `COL_NAME`
            FROM `WTS_SUBTIMETYPES`
            WHERE ENABLED = 1";
        $colR = getQueryResult($this->config, $colq, $debug = false);
        if($colR->num_rows > 0){
            $columnFilter = '(';
            $x=0;
            while($col = $colR->fetch_assoc()){        
                //Get column name results
                if($subTypeInfo[$col['COL_NAME']] == '1'){
                    if($x == 0)
                        $columnFilter .= "COL_NAME = '".$col['COL_NAME']."'";
                    else
                        $columnFilter .= " OR COL_NAME = '".$col['COL_NAME']."'";
                    $x++;
                }
            }
            $columnFilter .= ')';
            $colq .= " AND " . $columnFilter . " ORDER BY SORT_ORDER"; 
        }
        
        return getQueryResult($this->config, $colq, $debug = false);;
    }
    public function getTimeRequestByID($reqID = ''){
        if(!empty($reqID))
            $this->filters .= " AND REQUEST.REFER = '".$reqID."' ";
        $myq = "SELECT REFER 'RefNo', REQ.IDNUM as 'Requester', REQ.MUNIS 'Munis', 
                    CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', REQ.IDNUM 'EMP_ID',  
                    DATE_FORMAT(USEDATE,'%c-%d-%Y %a') 'Used', 
                    DATE_FORMAT(USEDATE,'%c/%d/%Y') 'UsedReqForm',
                    STATUS 'Status', 
                    DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                    DATE_FORMAT(ENDTIME,'%H%i') 'End',
                    DATE_FORMAT(BEGTIME,'%H') 'Start1',DATE_FORMAT(BEGTIME,'%i') 'Start2',
                    DATE_FORMAT(ENDTIME,'%H') 'End1', DATE_FORMAT(ENDTIME,'%i') 'End2',
                    HOURS 'Hrs',
                    DATE_FORMAT(REQDATE,'%c-%d-%Y %H%i') 'Request_Date',
                    IF(REQUEST.TIMETYPEID IS NULL, T.DESCR, OLDT.DESCR) 'Type',
                    IF(REQUEST.TIMETYPEID IS NULL, TIMETYPES_ID, IF(OLDSUB.NEWSUB_ID IS NULL, OLDT.NEWTYPE_ID, OLDSUB.NEWSUB_ID)) 'TIMETYPES_ID', 
                    IF(REQUEST.SUBTYPE IS NULL, SUB.DESCR, REQUEST.SUBTYPE) 'Subtype',
                    SUBTYPE_ID, 
                    T.ELEVATE_SUPERVISORS 'ELEVATE_SUPERVISORS',
                    CALLOFF 'Calloff', NOTE 'Comment', 
                    CONCAT_WS(', ', APR.LNAME,APR.FNAME) 'ApprovedBy', REQUEST.EX_REASON AS 'EXPUNGE_NOTES',
                    DATE_FORMAT(REQUEST.ApprovedTS,'%b %d, %Y') 'approveTS',
                    REASON 'Reason', HRAPP_IS 'HR_Approved', HR.LNAME 'HRLName', HR.FNAME 'HRFName', REQUEST.HR_NOTES AS 'HRNOTES'
                FROM REQUEST
                LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
                LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
                LEFT JOIN EMPLOYEE AS HR ON HR.IDNUM=REQUEST.HRAPP_ID
                LEFT JOIN TIMETYPE AS OLDT ON OLDT.TIMETYPEID = REQUEST.TIMETYPEID
                LEFT JOIN SUBTYPE AS OLDSUB ON OLDSUB.IDNUM=REQUEST.SUBTYPE
                LEFT JOIN WTS_TIMETYPES AS T ON T.IDNUM=REQUEST.TIMETYPES_ID
                LEFT JOIN WTS_SUBTIMETYPES AS SUB ON SUB.IDNUM=REQUEST.SUBTYPE_ID
                WHERE 1 ".$this->filters;
        $result = getQueryResult($this->config, $myq, $debug = false);
        return $result;
    }
    public function getFilterHRStatus($status){
        return " AND (STATUS='APPROVED' OR STATUS='DENIED')
                    AND HRAPP_IS=".$this->config->mysqli->real_escape_string($status);
    }
    public function getFilterRequestTypeForDateByEmp($empID, $type, $date){
        return " AND `TIMETYPES_ID` = '" . $type . "'
                        AND REQUEST.IDNUM = '" . $empID . "'
                        AND `USEDATE` = '" . date('Y-m-d', strtotime($date)) . "'";
    }
    public function getAddNewRequest($reqClass, $submitForDate){
        $myq = "INSERT INTO REQUEST (IDNUM, USEDATE, BEGTIME, ENDTIME, 
                HOURS, TIMETYPES_ID, SUBTYPE_ID, NOTE, STATUS, REQDATE, HR_NOTES, 
                AUDITID, IP)
                    VALUES ('".$reqClass->empID."', '" . date('Y-m-d', $submitForDate) . "',
                        '".$reqClass->begTime1.$reqClass->begTime2."00', '".$reqClass->endTime1.$reqClass->endTime2."00',
                        '".$reqClass->shiftHours."', '".$reqClass->subTypeID."', '".$reqClass->typeID."', 
                        '".$reqClass->empComment."', 'PENDING', NOW(), '".$reqClass->hrNotes."', 
                        '".$_SESSION['userIDnum']."',INET_ATON('".$_SERVER['REMOTE_ADDR']."'))";
        $result = getQueryResult($this->config, $myq, $debug = false);
        return $result;
    }
    public function getUpdateRequestByID($refNo, $useDate, $begTime, $endTime, $shifthours, $typeID, $subTypeID, $empComment){
        $myq="UPDATE REQUEST SET USEDATE='".$this->config->mysqli->real_escape_string(date('Y-m-d', strtotime($useDate)))."', 
                BEGTIME='".$this->config->mysqli->real_escape_string($begTime.'00')."', 
                ENDTIME='".$this->config->mysqli->real_escape_string($endTime.'00')."', 
                HOURS='".$this->config->mysqli->real_escape_string($shifthours)."', 
                TIMETYPES_ID='".$this->config->mysqli->real_escape_string($typeID)."', 
                SUBTYPE_ID='".$this->config->mysqli->real_escape_string($subTypeID)."', 
                NOTE='".$this->config->mysqli->real_escape_string($empComment)."', 
                AUDITID='".$this->config->mysqli->real_escape_string($_SESSION['userIDnum'])."', 
                IP=INET_ATON('".$this->config->mysqli->real_escape_string($_SERVER['REMOTE_ADDR'])."')
                WHERE REFER=".$this->config->mysqli->real_escape_string($refNo);
        
        $result = getQueryResult($this->config, $myq, $debug = false);
        return $result;
    }
    public function getTimeRequestFiltersBetweenDates($startDate, $endDate) {
        return " AND USEDATE BETWEEN '" . $this->config->mysqli->real_escape_string(date('Y-m-d', strtotime($startDate))) . "' 
            AND '" . $this->config->mysqli->real_escape_string(date('Y-m-d', strtotime($endDate))) . "'";
    }

    public function getTimeRequestFiltersByEmpID($empID) {
        return " AND REQUEST.IDNUM = '" . $this->config->mysqli->real_escape_string($empID) . "'";
    }
    public function getTimeRequestFiltersByType($typeID) {
        return " AND REQUEST.TIMETYPES_ID = '" . $this->config->mysqli->real_escape_string($typeID) . "'";
    }
    public function getTimeRequestFiltersBySubType($SubTypeID) {
        return " AND REQUEST.SUBTYPE_ID = '" . $this->config->mysqli->real_escape_string($SubTypeID) . "'";
    }
    public function getHRTimeRequestCountsByEmp(){
        $myq = "SELECT REFER 'RefNo', REQ.MUNIS 'Munis', 
                    CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', REQ.IDNUM 'EMP_ID',
                    COUNT(REFER) as 'ReqNumbers'
                FROM REQUEST
                LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
                LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
                LEFT JOIN EMPLOYEE AS HR ON HR.IDNUM=REQUEST.HRAPP_ID
                LEFT JOIN TIMETYPE AS OLDT ON OLDT.TIMETYPEID = REQUEST.TIMETYPEID
                LEFT JOIN SUBTYPE AS OLDSUB ON OLDSUB.IDNUM=REQUEST.SUBTYPE
                LEFT JOIN WTS_TIMETYPES AS T ON T.IDNUM=REQUEST.TIMETYPES_ID
                LEFT JOIN WTS_SUBTIMETYPES AS SUB ON SUB.IDNUM=REQUEST.SUBTYPE_ID
                WHERE 1 ".$this->filters ."
                GROUP BY REQ.IDNUM
                ORDER BY 'Name'";
        $result = getQueryResult($this->config, $myq, $debug = false);
        return $result;
    }
    public function getFilterByStatus($status, $useOr){
        if(!$useOr)
            $logic = " AND ";
        else
            $logic = " OR ";
        return $logic."STATUS = '".$status."'";
    }
    
}//End Request DB Class

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
    return "SELECT REFER 'RefNo', REQ.IDNUM as 'Requester', REQ.MUNIS 'Munis', 
                    CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', REQ.IDNUM 'EMP_ID',
                    CONCAT_WS(', ',AUDIT.LNAME,AUDIT.FNAME) 'Audit_Name',
                    DATE_FORMAT(USEDATE,'%c-%d-%Y %a') 'Used', 
                    DATE_FORMAT(USEDATE,'%c/%d/%Y') 'UsedReqForm',
                    STATUS 'Status', 
                    DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                    DATE_FORMAT(ENDTIME,'%H%i') 'End',
                    DATE_FORMAT(BEGTIME,'%i') 'Start1',DATE_FORMAT(BEGTIME,'%H') 'Start2',
                    DATE_FORMAT(ENDTIME,'%i') 'End1', DATE_FORMAT(ENDTIME,'%H') 'End2',
                    HOURS 'Hrs',
                    DATE_FORMAT(REQDATE,'%c-%d-%Y %H%i') 'Request_Date',
                    IF(REQUEST.SUBTYPE IS NULL, T.DESCR, REQUEST.SUBTYPE) 'Type',
                    IF(REQUEST.TIMETYPEID IS NULL, TIMETYPES_ID, IF(OLDSUB.NEWSUB_ID IS NULL, OLDT.NEWTYPE_ID, OLDSUB.NEWSUB_ID)) 'TIMETYPES_ID', 
                    IF(REQUEST.TIMETYPEID IS NULL, SUB.DESCR, OLDT.DESCR) 'Subtype',
                    SUBTYPE_ID, 
                    T.ELEVATE_SUPERVISORS 'ELEVATE_SUPERVISORS',
                    CALLOFF 'Calloff', NOTE 'Comment', 
                    CONCAT_WS(', ', APR.LNAME,APR.FNAME) 'ApprovedBy', REQUEST.EX_REASON AS 'EXPUNGE_NOTES',
                    DATE_FORMAT(REQUEST.ApprovedTS,'%b %d, %Y') 'approveTS',
                    REASON 'Reason', HRAPP_IS 'HR_Approved', HR.LNAME 'HRLName', HR.FNAME 'HRFName', REQUEST.HR_NOTES AS 'HRNOTES'
                FROM REQUEST
                LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
                LEFT JOIN EMPLOYEE AS AUDIT ON AUDIT.IDNUM=REQUEST.AUDITID
                LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
                LEFT JOIN EMPLOYEE AS HR ON HR.IDNUM=REQUEST.HRAPP_ID
                LEFT JOIN TIMETYPE AS OLDT ON OLDT.TIMETYPEID = REQUEST.TIMETYPEID
                LEFT JOIN SUBTYPE AS OLDSUB ON OLDSUB.IDNUM=REQUEST.SUBTYPE
                LEFT JOIN WTS_TIMETYPES AS T ON T.IDNUM=REQUEST.TIMETYPES_ID
                LEFT JOIN WTS_SUBTIMETYPES AS SUB ON SUB.IDNUM=REQUEST.SUBTYPE_ID
                WHERE 1
                " . $filters . "
                " . $config->mysqli->real_escape_string($orderBy) . "
                " . $config->mysqli->real_escape_string($limit) . "  
                ";
}
function getFilterEmpID($config, $empID){
    return " AND REQ.IDNUM = '".$config->mysqli->real_escape_string($empID). "'";
}
function getLimitFilter($config, $prevNum, $limit){
    return " LIMIT ".$config->mysqli->real_escape_string($prevNum).",  ".$config->mysqli->real_escape_string($limit);
}
function getFilerDivision($config, $divID){
    if($divID != 'All')
        $return = " AND REQ.DIVISIONID = '".$config->mysqli->real_escape_string($divID). "'";
    else
        $return = '';
    return $return;
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
function getReqestsPastMidightTimes($Start, $End){
    return " AND (TIME_FORMAT(BEGTIME, '%H%I') >= TIME_FORMAT('".$Start."00', '%H%I') OR TIME_FORMAT(BEGTIME, '%H%I') <= TIME_FORMAT('".$End."00', '%H%I'))";
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
function getEmployeeInfo($config, $empID){
    return "SELECT EMP.IDNUM AS 'empID', D.DESCR AS 'DESCR', RANK.DESCR AS 'RANK', CONCAT_WS(', ',EMP.LNAME,EMP.FNAME) 'Name'
        FROM EMPLOYEE AS EMP
        LEFT JOIN `DIVISION` AS D ON EMP.DIVISIONID=D.DIVISIONID 
        LEFT JOIN GRADE AS RANK ON RANK.ABBREV=EMP.GRADE
        
    WHERE EMP.IDNUM='" . $config->mysqli->real_escape_string($empID) . "'";
}
function getFilterByRefNo($config, $refNo){
    return " AND REQUEST.REFER = '" . $config->mysqli->real_escape_string($refNo) . "'"; 
}
function getFilterByStatus($status, $useOr){
    if(!$useOr)
        $logic = " AND ";
    else
        $logic = " OR ";
    return $logic."STATUS = '".$status."'";
}

?>
