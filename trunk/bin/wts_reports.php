<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function displayReportMenu($config){
    if($config->adminLvl >= 25){
        echo '<div class="divider"></div>';
        echo "<h2>Supervisor Menu</h2>";
        echo '<li><a href="?submittedRequests=true">Submitted Requests by Division and by Dates or Pay Period</a></li>';
        echo '<li><a href="?submittedRequestsNEW=true">NEW Submitted Requests by Division and by Dates or Pay Period</a></li>';
        echo '<li><a href="?subReqCal=true">Requests Calendar</a></li>';
        echo '<li><a href="?lookup=true">Submitted Request by Employee by Date</a></li>';
        echo '<li><a href="?hrEmpRep=true">Approved and Denied Requests by Employee by Payperiod</a></li>';
        echo '<li><a href="?sickEmpRep=true">Sick Request Reports by Date </a></li>';
        echo '<li><a href="?OTRep=true">OverTime Request Reports by Date </a></li>';
        echo '<li><a href="?SecLogRep=true">Secondary Log Reports by Date</a></li>';
        echo '</ul>';
    }
}

function reportsCal($config){
    if(isset($_POST['prevMonth'])){
        $month =isset($_POST['prevMon']) ? $_POST['prevMon'] : date('n');
        
    }
    elseif(isset($_POST['nextMonth'])){
        $month =isset($_POST['nextMon']) ? $_POST['nextMon'] : date('n');
    }
    else{
        $month =isset($_POST['curMon']) ? $_POST['curMon'] : date('n');
    }
    $year =isset($_POST['year']) ? $_POST['year'] : date('Y');
    
    $passedDates = "";
    
    viewClandar($config, $month, $year);
}
function hrPayrolReportByEmployee($config){    
//    Get Post Vars
    $viewBtn = false;
    $showAllStatus = isset($_POST['showAllStatus']) ? true : false;
    $hrReportTotalRows = isset($_POST['hrReportTotalRows']) ? $_POST['hrReportTotalRows'] : 0;
    ;
    if($hrReportTotalRows > 0){
        for($i=0; $i<=$hrReportTotalRows; $i++){
            if(isset($_POST['viewDetailsBtn'.$i])){
                $empID = $_POST['empID'.$i];
                $viewBtn = true;
                break;
            }
        }
    }
    if(isset($_POST['backBtn']))
        $viewBtn = false;
    
    $requests = new request_class();
    $timeReport = new request_reports($config);
     
    $timeReport->showTimeRequestFilterOptions($showCustomDates = true, $showPayPeriods = true, $showDivisions = true);    
        
        if($viewBtn){
            $hiddenInputs = '<input type="hidden" name="hrReportTotalRows" value="1" />
                <input type="hidden" name="viewDetailsBtn0" value="true" />
                <input type="hidden" name="empID0" value="'.$empID.'" />';
            echo $hiddenInputs;
            echo '<div align="center">';
            if($showAllStatus){
                echo '<input type="submit" name="showNormalStatus" value="Remove Pending Status" />';
                }
            else{
                $timeReport->filters .= " AND (STATUS = 'APPROVED' OR STATUS = 'DENIED')";
                echo '<input type="submit" name="showAllStatus" value="Include Pending Status" />';
            }
            //echo '<div align="center"><a href="'.$_SERVER['REQUEST_URI'].'">Back</a></div>';
//            echo '<form method="POST">';
            echo ' <input type="submit" name="backBtn" value="Back To Employee Counts" /></div><Br/>';
            $hiddenInputs = '<input type="hidden" name="viewDetailsBtn" value="true" />';
            $timeReport->filters .= $timeReport->db->getTimeRequestFiltersByEmpID($empID);
            $requests->showTimeRequestTable($config, $timeReport->filters, $orderBy = "ORDER BY REFER DESC", $hiddenInputs);
            //empTimeReportByPay($config, $startDate, $endDate, $empID);
        }
        else{
            $timeReport->filters .= " AND (STATUS = 'APPROVED' OR STATUS = 'DENIED')";
            $filters = $timeReport->filters;
            $filters .= $timeReport->db->getFilterHRStatus('0');  
            $timeReport->db->filters = $filters;
            $result = $timeReport->db->getHRTimeRequestCountsByEmp();

            $theTable = array(array());
            $x = 0;
            $theTable[$x][0] = "View";
            $theTable[$x][1] = "Munis #";
            $theTable[$x][2] = "Employee";
            $theTable[$x][3] = "Number of Requests";

            $lastUser = '';
            $lastUserRow = 0;
            $rowCount = 0;

            while($row = $result->fetch_assoc()) {
                    $x++;
                    $recordCounter = 1;
                    $lastUser = $row['Name'];

                    $theTable[$x][0] = '<input type="submit" name="viewDetailsBtn'.$rowCount.'" value="View" />
                        <input type="hidden" name="empID'.$rowCount.'" value="'.$row['EMP_ID'].'" />
                        ';
                    $theTable[$x][1] = $row['Munis'];
                    $theTable[$x][2] = $lastUser;
                    $theTable[$x][3] = $row['ReqNumbers'];
                    $rowCount++;

            }//end While loop
            echo '<h3>Pending HR Approval</h3>';
            showSortableTable($theTable, 1, "hrPending");
            $filters = $timeReport->filters;
            $filters .= $timeReport->db->getFilterHRStatus('1');  
            $timeReport->db->filters = $filters;
            $result = $timeReport->db->getHRTimeRequestCountsByEmp();

            $theTable = array(array());
            $x = 0;
            $theTable[$x][0] = "View";
            $theTable[$x][1] = "Munis #";
            $theTable[$x][2] = "Employee";
            $theTable[$x][3] = "Number of Requests";

            $lastUser = '';
            $lastUserRow = 0;
            $recordCounter = 0;

            while($row = $result->fetch_assoc()) {
                    $x++;
                    $recordCounter = 1;
                    $lastUser = $row['Name'];

                    $theTable[$x][0] = '<input type="submit" name="viewDetailsBtn'.$rowCount.'" value="View" />
                        <input type="hidden" name="empID'.$rowCount.'" value="'.$row['EMP_ID'].'" />
                        ';
                    $theTable[$x][1] = $row['Munis'];
                    $theTable[$x][2] = $lastUser;
                    $theTable[$x][3] = $row['ReqNumbers'];
                    $rowCount++;

            }//end While loop
            //echo 'number of rows: '.$x;
            echo '<h3>HR Approvals</h3>';
            showSortableTable($theTable, 1, "hrApprove");
            echo '<input type="hidden" name="hrReportTotalRows" value="'.$rowCount.'" />';
        }

    //show a print button. printed look defined by print.css
//    echo '<a href="javascript:window.print()">Print</a>';
    
}

function empTimeReportByPay($config, $startDate, $endDate, $empID){
    $mysqli = $config->mysqli;
    $refNo = '';
    //Was Approve Button Pressed
    if(isset($_POST['totalRows'])){
        $totalRows = $_POST['totalRows'];
        for($i=0;$i<=$totalRows;$i++){
            if(isset($_POST['hrApprove'.$i])){
                $refNo = $_POST['refNo'.$i];
                break;
            }
            if(isset($_POST['pendingBtn'.$i])){
                $refNo = $_POST['refNo'.$i];
                $myq = $myq="UPDATE REQUEST 
                    SET STATUS='PENDING',
                    `HRAPP_IS` = '0',
                    APPROVEDBY=''
                    WHERE REFER=".$refNo;
                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result, $myq);
                addLog($config, 'Ref# '.$refNo.' status was changed to pending');
                break;
            }
        }
    }
    if(!empty($refNo)){
        $myq = "UPDATE REQUEST SET `TSTAMP` = NOW( ) ,
            `HRAPP_IS` = '1',
            `HRAPP_ID` = '".$_SESSION['userIDnum']."',
            `HRAPP_IP` = INET_ATON('".$_SERVER['REMOTE_ADDR']."'),
            `HRAPP_TIME` = NOW( ) WHERE `REQUEST`.`REFER` =".$refNo;
         $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        addLog($config, 'HR Approved Request with ref# '.$refNo);
    }
    
    $myq = "SELECT REFER 'RefNo', REQ.MUNIS 'Munis', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', 
                DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', STATUS 'Status',
                    DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                    DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                    T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', 
                    APR.LNAME 'ApprovedBy', 
                    DATE_FORMAT(REQUEST.ApprovedTS,'%a %b %d %Y') 'approveTS',
                    REASON 'Reason', HRAPP_IS 'HR_Approved', HR.LNAME 'HRLName', HR.FNAME 'HRFName'
                FROM REQUEST
                LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
                LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
                LEFT JOIN EMPLOYEE AS HR ON HR.IDNUM=REQUEST.HRAPP_ID
                INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=REQUEST.TIMETYPEID
                WHERE USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."'
                AND REQ.IDNUM='".$empID."'
                AND (STATUS='APPROVED' OR STATUS='DENIED')
                ";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);

        $theTable = array(array());
        $x = 0;
        $y = 0;
        if($config->adminLvl >=50 && $config->adminLvl !=75){
            $theTable[$x][$y] = "HR Approve"; $y++;
            $theTable[$x][$y] = "Expunge"; $y++;
        }
        $theTable[$x][$y] = "Ref #"; $y++;
        $theTable[$x][$y] = "Date of Use"; $y++;
        $theTable[$x][$y] = "Start Time"; $y++;
        $theTable[$x][$y] = "End Time"; $y++;
        $theTable[$x][$y] = "Hours"; $y++;
        $theTable[$x][$y] = "Type"; $y++;
        $theTable[$x][$y] = "Subtype"; $y++;
        $theTable[$x][$y] = "Call Off"; $y++;
        $theTable[$x][$y] = "Comment"; $y++;
        $theTable[$x][$y] = 'Status'; $y++;
        $theTable[$x][$y] = 'ApprovedBy'; $y++;
        $theTable[$x][$y] = 'Approved Time'; $y++;
        $theTable[$x][$y] = 'Reason';

        while($row = $result->fetch_assoc()) {
            $x++;
            $y=0;
            if($config->adminLvl >=50 && $config->adminLvl !=75){
                if(!$row['HR_Approved'])
                    $theTable[$x][$y] = '<input type="submit" name="hrApprove'.$x.'" value="Approve" />';
                else
                    $theTable[$x][$y] = '<div align="center"><h3><font color="red">Approved</font></h3></div>';
                $theTable[$x][$y] .= '<input type="submit" name="editBtn0" value="Edit/View" onClick="this.form.action=' . "'?leave=true'" . '; this.form.submit()" />'.
                     '<input type="hidden" name="formName" value="'.$_SERVER['REQUEST_URI'].'"/>
                      <input type="hidden" name="requestID0" value="'.$row['RefNo'].'" />
                      <input type="hidden" value="2" name="totalRows" />';$y++;
                $theTable[$x][$y] = '<br/><input type="submit" name="deleteBtn'.$x.'" value="Expunge" />';$y++;
            }
            $theTable[$x][$y] = '<input type="hidden" name="refNo'.$x.'" value="'.$row['RefNo'].'" />'.$row['RefNo']; $y++;
            $empMunis = $row['Munis'];
            $empName = $row['Name'];
            $theTable[$x][$y] = $row['Used']; $y++;
            $theTable[$x][$y] = $row['Start']; $y++;
            $theTable[$x][$y] = $row['End']; $y++;
            $theTable[$x][$y] = $row['Hrs']; $y++;
            $theTable[$x][$y] = $row['Type']; $y++;
            $theTable[$x][$y] = $row['Subtype']; $y++;
            $theTable[$x][$y] = $row['Calloff']; $y++;
            $theTable[$x][$y] = $row['Comment']; $y++;
            if($row['Status'] != 'PENDING' && $config->adminLvl >=25){
                $theTable[$x][$y] = $row['Status'].'<input type="submit" name="pendingBtn'.$x.'" value="Send to Pending" />';
            }
            else
                $theTable[$x][$y] = $row['Status']; 
            $y++;
            $theTable[$x][$y] = $row['ApprovedBy']; $y++;
            $theTable[$x][$y] = $row['approveTS']; $y++;
            $theTable[$x][$y] = $row['Reason'];
        }
        if(empty($empName)){
            $myq = "SELECT REQ.MUNIS 'Munis', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name'
                FROM EMPLOYEE REQ
                WHERE REQ.IDNUM='".$empID." LIMIT 1'
                ";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result, $myq);
            $row = $result->fetch_assoc();
            $empMunis = $row['Munis'];
            $empName = $row['Name'];
            
        }
        echo '<div align="center"><h3>Employee: '.$empName.'</h3>Munis# '.$empMunis.'</div>';
        
        showSortableTable($theTable, 7, "hrDetails", array(2));
        echo '<input type="hidden" name="totalRows" value="'.$x.'" />
              <input type="hidden" value="View" name="viewDetailsBtn">
              <input type="hidden" value="'.$empID.'" name="empID">';
        echo '</form>';
        //Show Hour Adjustment Table
        $totalsTable = array(array());
        $x = 0;

        $totalTable[$x][0] = "Type";
        $totalTable[$x][1] = "Hours Gained/Used";


        $myq = "SELECT HOURS 'Hrs', T.DESCR 'Type', R.TIMETYPEID 'timeType', HRAPP_IS 'HRApproved'
                FROM REQUEST R
                INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=R.TIMETYPEID
                WHERE USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."'
                AND R.IDNUM='".$empID."'
                AND STATUS='APPROVED'
                ORDER BY R.TIMETYPEID
                ";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $lastTimeType = '';
        while($row = $result->fetch_assoc()) {
            if($row['HRApproved']){
                if(strcmp($row['timeType'], $lastTimeType)==0){
                    $totalTable[$x][1] += $row['Hrs'];
                }
                else{
                    $x++;
                    $lastTimeType = $row['timeType'];
                    $totalTable[$x][0] = $row['Type'];
                    $totalTable[$x][1] = $row['Hrs'];
                }
            }
        }
    echo '<div id="wrapper">';

    $echo = '<table class="sortable">
            <tr>';
    for($y=0;$y<sizeof($totalTable[0]);$y++){
        $echo .= '<th>'.$totalTable[0][$y].'</th>';
    }
    $echo .= '</tr>
        ';
    $x=1;
    for($x;$x<sizeof($totalTable);$x++){
        $echo .= '<tr>';
        for($y=0;$y<sizeof($totalTable[$x]);$y++){
            $echo .= '<td>'.$totalTable[$x][$y].'</td>';
        }
        $echo .= '</tr>
            ';
    }
    $echo .= '</table></div>';
    echo $echo;
    
    $refNo = '';
    //Was Approve Button Pressed
    if(isset($_POST['totalRows'])){
        $totalRows = $_POST['totalRows'];
        for($i=0;$i<=$totalRows;$i++){
            if(isset($_POST['hrEdit'.$i])){
                $refNo = $_POST['refNo'.$i];
                echo '<input type="hidden"  name="editBtn1" value="Edit" />
                      <input type="hidden" name="requestID1" value="'.$refNo.'" />
                      <input type="hidden" value="2" name="totalRows" 
                        onLoad="this.form.action=' . "'?leave=true'" . '; this.form.submit()" />';
            }
            if(isset($_POST['deleteBtn'.$i])){
                $refNo = $_POST['refNo'.$i];
                echo '</form>';
                $extraInputs = '<input type="hidden" value="View" name="viewDetailsBtn" />
                    <input type="hidden" name="empID" value="'.$_POST['empID'].'" />
                     <input type="hidden" name="refNo'.$i.'" value="'.$refNo.'" />';
                expungeRequest($mysqli, $refNo, false, $deleteIndex=$i, $totalRows, $extraInputs);
            }
        }
    }

}
function sickReport($config){
    echo '<h3>Employee Sick Reports</h3>';
    if($config->adminLvl >=25){
        $mysqli = $config->mysqli;
        //Get variables
        $repYear = isset($_POST['repYear']) ? $_POST['repYear'] : $config->installYear;
        
       //Select year
        echo '<form method=POST>';
        echo '</div><div class="login"><table><tr><td>Report Year: <select name="repYear" onchange="this.form.submit()">';
        for($i=$config->installYear;$i<=date('Y'); $i++){
            echo '<option value="'.$i.'"';
            if($repYear == $i)
                echo ' SELECTED';
            echo '>'.$i.'</option>';
        }
        echo '</select></td>';                
        
        $startDate = new DateTime($repYear.'-01-01');
        $endDate = new DateTime($repYear.'-12-31');
        
        if(isset($_POST['viewDetailsBtn']) && !isset($_POST['backBtn'])){
            $empID = $_POST['empID'];
            echo '<td width=470 align=right><input type="submit" name="backBtn" value="Back to List" />
                    <input type="hidden" name="viewDetailsBtn" value="true" />
                    <input type="hidden" name="empID" value="'.$empID.'" />
                    </td></tr></table></div><div class="post">';
            empTimeReportByPay($config, $startDate, $endDate, $empID);
            echo '</form>';
            
        }
        else{
            if($config->adminLvl >= 25){
                echo '<td width=470 align=right>Choose a Division:
                <select name="divisionID" onchange="this.form.submit()">';

                if(isset($_POST['divisionID'])){
                    $myDivID = $_POST['divisionID'];
                }
                else{
                    if($config->adminLvl >= 50){
                        $myDivID = "All"; 
                    }
                    else{
                        $mydivq = "SELECT DIVISIONID FROM EMPLOYEE E WHERE E.IDNUM='" . $_SESSION['userIDnum']."'";
                        $myDivResult = $mysqli->query($mydivq);
                        SQLerrorCatch($mysqli, $myDivResult);
                        $temp = $myDivResult->fetch_assoc();
                        $myDivID = $temp['DIVISIONID'];
                    }
                }

                $alldivq = "SELECT * FROM `DIVISION` WHERE 1";
                $allDivResult = $mysqli->query($alldivq);
                SQLerrorCatch($mysqli, $allDivResult);
                while($Divrow = $allDivResult->fetch_assoc()) {
                    echo '<option value="'.$Divrow['DIVISIONID'].'"';
                    if($Divrow['DIVISIONID']==$myDivID)
                        echo ' SELECTED ';
                    echo '>'.$Divrow['DESCR'].'</option>';
                }
                if($config->adminLvl >= 25){
                    if(isset($_POST['divisionID'])){
                        if($myDivID == "All")
                            echo '<option value="All" SELECTED>All</option>';
                        else
                            echo '<option value="All">All</option>';
                    }
                    else if($myDivID == "All")
                        echo '<option value="All" SELECTED>All</option>';
                    else
                        echo '<option value="All">All</option>';
                }
                echo '</select></td>';
            }
            echo '</tr></table>';
            $isApproveStatus = isset($_POST['approvedStatus']) ? true : false;
                if(!isset($_POST['clicked']))
                    $isApproveStatus = true;
            $isPendingStatus = isset($_POST['pendingStatus']) ? true : false;
            
            echo '<div align=right><form method=POST><input type="hidden" name="clicked" value="true" />';
            
            //Status = approved
            echo '<input onChange="this.form.submit()" type="checkbox" value="true" name="approvedStatus"';
            if($isApproveStatus)
                echo ' CHECKED';
            echo ' />Status: Approved<Br/>';
                       
            //status = pending
            echo '<input onChange="this.form.submit()" type="checkbox" value="true" name="pendingStatus"';
            if($isPendingStatus)
                echo ' CHECKED';
            echo ' />Status: Pending<br/>';
            
            echo '</form></div></div><div class="post">';
              
            if($myDivID == "All")
                $myDivID = "";
            else
                $myDivID = "AND REQ.DIVISIONID='".$myDivID."'";
            
            $status = '';
            if($isApproveStatus && $isPendingStatus)
                $status = "AND (STATUS = 'APPROVED' OR STATUS = 'PENDING')";
            else if($isApproveStatus)
                $status = "AND STATUS = 'APPROVED'";
            else if($isPendingStatus)
                $status = "AND STATUS = 'PENDING'";
            else
                $status = "AND STATUS=''";
            
   
            $myq = "SELECT REFER 'RefNo', REQ.IDNUM 'REQID', REQ.MUNIS 'Munis', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', 
                        DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', STATUS 'Status',
                        DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', 
                        HRAPP_IS 'HR_Approved', HR.LNAME 'HRLName', HR.FNAME 'HRFName'
                    FROM REQUEST
                    LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
                    LEFT JOIN EMPLOYEE AS HR ON HR.IDNUM=REQUEST.IDNUM
                    INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=REQUEST.TIMETYPEID
                    WHERE USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."'
                    AND (REQUEST.TIMETYPEID='SK' OR SUBTYPE='8')
                    ".$myDivID."
                    ".$status."
                    ORDER BY REQ.LNAME";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);

            $theTable = array(array());
            $x = 0;
            $theTable[$x][0] = "View";
            $theTable[$x][1] = "Munis #";
            $theTable[$x][2] = "Employee";
            $theTable[$x][3] = "Number of Sick Requests";

            $lastUser = '';
            $lastUserRow = 0;
            $recordCounter = 0;

            while($row = $result->fetch_assoc()) {
                if(strcmp($lastUser, $row['Name'])==0){
                    $recordCounter++;
                    $theTable[$x][3] = $recordCounter;
                }
                else{
                    $x++;
                    $recordCounter = 1;
                    $lastUser = $row['Name'];

                    $theTable[$x][0] = '<form method="POST">
                        <input type="submit" name="viewDetailsBtn" value="View" />
                        <input type="hidden" name="empID" value="'.$row['REQID'].'" />
                        </form>';
                    $theTable[$x][1] = $row['Munis'];
                    $theTable[$x][2] = $lastUser;
                    $theTable[$x][3] = $recordCounter;

                }


            }//end While loop
            echo 'number of rows: '.$x;
            showSortableTable($theTable, 1);
        }

        
    }
    else{
        echo 'Access Denied';
    }
}

function overtimeReport($config){
    echo '<h3>Employee Overtime Reports</h3>';
    if($config->adminLvl >=25){
        $mysqli = $config->mysqli;
        //Get variables
        $repYear = isset($_POST['repYear']) ? $_POST['repYear'] : $config->installYear;
        
       //Select year
        echo '<form method=POST>';
        echo '</div><div class="login"><table><tr><td>Report Year: <select name="repYear" onchange="this.form.submit()">';
        for($i=$config->installYear;$i<=date('Y'); $i++){
            echo '<option value="'.$i.'"';
            if($repYear == $i)
                echo ' SELECTED';
            echo '>'.$i.'</option>';
        }
        echo '</select></td>';                
        
        $startDate = new DateTime($repYear.'-01-01');
        $endDate = new DateTime($repYear.'-12-31');
        
        if(isset($_POST['viewDetailsBtn']) && !isset($_POST['backBtn'])){
            $empID = $_POST['empID'];
            echo '<td width=470 align=right><input type="submit" name="backBtn" value="Back to List" />
                    <input type="hidden" name="viewDetailsBtn" value="true" />
                    <input type="hidden" name="empID" value="'.$empID.'" />
                    </td></tr></table></div><div class="post">';
            empTimeReportByPay($config, $startDate, $endDate, $empID);
            echo '</form>';
            
        }
        else{
            if($config->adminLvl >= 25){
                echo '<td width=470 align=right>Choose a Division:
                <select name="divisionID" onchange="this.form.submit()">';

                if(isset($_POST['divisionID'])){
                    $myDivID = $_POST['divisionID'];
                }
                else{
                    if($config->adminLvl >= 50){
                        $myDivID = "All"; 
                    }
                    else{
                        $mydivq = "SELECT DIVISIONID FROM EMPLOYEE E WHERE E.IDNUM='" . $_SESSION['userIDnum']."'";
                        $myDivResult = $mysqli->query($mydivq);
                        SQLerrorCatch($mysqli, $myDivResult);
                        $temp = $myDivResult->fetch_assoc();
                        $myDivID = $temp['DIVISIONID'];
                    }
                }

                $alldivq = "SELECT * FROM `DIVISION` WHERE 1";
                $allDivResult = $mysqli->query($alldivq);
                SQLerrorCatch($mysqli, $allDivResult);
                while($Divrow = $allDivResult->fetch_assoc()) {
                    echo '<option value="'.$Divrow['DIVISIONID'].'"';
                    if($Divrow['DIVISIONID']==$myDivID)
                        echo ' SELECTED ';
                    echo '>'.$Divrow['DESCR'].'</option>';
                }
                if($config->adminLvl >= 25){
                    if(isset($_POST['divisionID'])){
                        if($myDivID == "All")
                            echo '<option value="All" SELECTED>All</option>';
                        else
                            echo '<option value="All">All</option>';
                    }
                    else if($myDivID == "All")
                        echo '<option value="All" SELECTED>All</option>';
                    else
                        echo '<option value="All">All</option>';
                }
                echo '</select></td>';
            }
            echo '</tr></table>';
            $isApproveStatus = isset($_POST['approvedStatus']) ? true : false;
                if(!isset($_POST['clicked']))
                    $isApproveStatus = true;
            $isPendingStatus = isset($_POST['pendingStatus']) ? true : false;
            
            echo '<div align=right><form method=POST><input type="hidden" name="clicked" value="true" />';
            
            //Status = approved
            echo '<input onChange="this.form.submit()" type="checkbox" value="true" name="approvedStatus"';
            if($isApproveStatus)
                echo ' CHECKED';
            echo ' />Status: Approved<Br/>';
                       
            //status = pending
            echo '<input onChange="this.form.submit()" type="checkbox" value="true" name="pendingStatus"';
            if($isPendingStatus)
                echo ' CHECKED';
            echo ' />Status: Pending<br/>';
            
            echo '</form></div></div><div class="post">';
              
            if($myDivID == "All")
                $myDivID = "";
            else
                $myDivID = "AND REQ.DIVISIONID='".$myDivID."'";
            
            $status = '';
            if($isApproveStatus && $isPendingStatus)
                $status = "AND (STATUS = 'APPROVED' OR STATUS = 'PENDING')";
            else if($isApproveStatus)
                $status = "AND STATUS = 'APPROVED'";
            else if($isPendingStatus)
                $status = "AND STATUS = 'PENDING'";
            else
                $status = "AND STATUS=''";
            
   
            $myq = "SELECT REFER 'RefNo', REQ.IDNUM 'REQID', REQ.MUNIS 'Munis', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', 
                        DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', STATUS 'Status',
                        DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', 
                        HRAPP_IS 'HR_Approved', HR.LNAME 'HRLName', HR.FNAME 'HRFName'
                    FROM REQUEST
                    LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
                    LEFT JOIN EMPLOYEE AS HR ON HR.IDNUM=REQUEST.IDNUM
                    INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=REQUEST.TIMETYPEID
                    WHERE USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."'
                    AND REQUEST.TIMETYPEID='OT'
                    ".$myDivID."
                    ".$status."
                    ORDER BY REQ.LNAME";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);

            $theTable = array(array());
            $x = 0;
            $theTable[$x][0] = "View";
            $theTable[$x][1] = "Munis #";
            $theTable[$x][2] = "Employee";
            $theTable[$x][3] = "Number of Overtime Requests";

            $lastUser = '';
            $lastUserRow = 0;
            $recordCounter = 0;

            while($row = $result->fetch_assoc()) {
                if(strcmp($lastUser, $row['Name'])==0){
                    $recordCounter++;
                    $theTable[$x][3] = $recordCounter;
                }
                else{
                    $x++;
                    $recordCounter = 1;
                    $lastUser = $row['Name'];

                    $theTable[$x][0] = '<form method="POST">
                        <input type="submit" name="viewDetailsBtn" value="View" />
                        <input type="hidden" name="empID" value="'.$row['REQID'].'" />
                        </form>';
                    $theTable[$x][1] = $row['Munis'];
                    $theTable[$x][2] = $lastUser;
                    $theTable[$x][3] = $recordCounter;

                }


            }//end While loop
            echo 'number of rows: '.$x;
            showSortableTable($theTable, 1);
        }

        
    }
    else{
        echo 'Access Denied';
    }
}
function empTimeReportByPayNew($config, $startDate, $endDate){
                         
    $filters = "WHERE ";
    $filters .= getTimeRequestFilterBetweenDates($config, $startDate, $endDate);
    $filters .= " AND (STATUS='APPROVED' OR STATUS='DENIED')";
    $hiddenInputs = '';
    showTimeRequestTable($config, $filters, $orderBy = "ORDER BY REFER DESC", $hiddenInputs);
}
?>
