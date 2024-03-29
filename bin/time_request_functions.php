<?php

/*
 * Functions related to the gain/use time sub-system
 */
?>

<?php
function displayLeaveForm($config){
  
    $mysqli = $config->mysqli;

    //check if we're coming from an edit button on the submitted report
    $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : false;
    $updatingRequest = isset($_POST['formName']) ?  $_POST['formName'] : false;
    $updatingRequest = isset($_POST['duplicateBtn']) ?  "duplicateRequest" : $updatingRequest;
    $findBtn = isset($_POST['findBtn']) ?  true : false;
    $requestAccepted = false;
    //echo "updatingRequest = $updatingRequest"; //DEBUG
   
    if($totalRows && $updatingRequest && !$findBtn) {
        for($i=0; $i<$totalRows; $i++){
            if(isset($_POST['editBtn'.$i]))
                    $referNum=$_POST['requestID'.$i];
        }
        if(!empty($referNum)){
            $myq='SELECT REQUEST.IDNUM, TIMETYPEID, BEGTIME, ENDTIME, NOTE, CALLOFF, USEDATE, SUBTYPE,
                LNAME, FNAME
                FROM REQUEST, EMPLOYEE
                WHERE EMPLOYEE.IDNUM=REQUEST.IDNUM
                AND REFER='.$config->mysqli->real_escape_string($referNum);
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            $row = $result->fetch_assoc();
            //set posts to pre-fill form from record we want to edit
            $_POST['referNum']= $referNum;
            $_POST['type'] = $row['TIMETYPEID'];   
            $_POST['ID'] = $row['IDNUM'];
            $_POST['beg1'] = substr($row['BEGTIME'],0,2);
            $_POST['beg2'] = substr($row['BEGTIME'],3,2);
            $_POST['end1'] = substr($row['ENDTIME'],0,2);
            $_POST['end2'] = substr($row['ENDTIME'],3,2);
            $_POST['comment'] = $row['NOTE'];
            $_POST['calloff'] = $row['CALLOFF'];
            $_POST['usedate'] = $row['USEDATE'];
            $_POST['subtype'] = $row['SUBTYPE'];
            $foundUserFNAME = $row['FNAME'];
            $foundUserLNAME = $row['LNAME'];
            $foundUserID = $row['IDNUM'];
            //var_dump($_POST);
        }
    } 
            
    //Get all passed variables
    $postID = isset($_POST['ID']) ? $_POST['ID'] : $_SESSION['userIDnum'];
    $postThruDate = isset($_POST['thrudate']) ? $_POST['thrudate'] : false;
    $shiftLength = isset($_POST['shift']) ? $_POST['shift'] : '';
    $postBeg1 = isset($_POST['beg1']) ? $_POST['beg1'] : null;
    $postBeg2 = isset($_POST['beg2']) ? $_POST['beg2'] : null;
    if(!empty($postBeg1) && !empty($postBeg2))
        $postBegin = $postBeg1.$postBeg2;
    else
        $postBegin = false;
    $postEnd1 = isset($_POST['end1']) ? $_POST['end1'] : null;
    $postEnd2 = isset($_POST['end2']) ? $_POST['end2'] : null;
    if(!empty($postEnd1) && !empty($postEnd2))
        $postEnding = $postEnd1.$postEnd2;
    else
        $postEnding = false;
    if(!isset($_POST['shift'])){
        if($postBegin==$postEnding){
            $postBegin = false;
            $postEnding = false;
        }
    }
    $type = isset($_POST['type']) ? $mysqli->real_escape_string($_POST['type']) : false;
    $comment = isset($_POST['comment']) ? $mysqli->real_escape_string($_POST['comment']) : false;
    $calloff = isset($_POST['calloff']) ? $_POST['calloff'] : 'NO';
    $auditid = $_SESSION['userIDnum'];
    $postUseDate = isset($_POST['usedate']) ? $_POST['usedate'] : false;
        If(!$postUseDate)
            $isDateUse = false;
        Else
            $isDateUse = true;
    $subtype = isset($_POST['subtype']) ? $mysqli->real_escape_string($_POST['subtype']) : 'NONE';
    

//Submit Button Pressed.  Add record to the database
if (isset($_POST['submit']) || isset($_POST['update'])) {


    $ID = $mysqli->real_escape_string(strtoupper($postID));
    $usedate = new DateTime($mysqli->real_escape_string($postUseDate));
    
    if(!$postThruDate) {
        $daysOff = 0;  
    }
    else{
        $thrudate = new DateTime($mysqli->real_escape_string($postThruDate));

        $daysOffInterval = $usedate->diff($thrudate); //number days in given range
        $daysOff = $daysOffInterval->format("%d");
    }
            

    $beg = new DateTime($mysqli->real_escape_string($postBegin));
    //setting end to beginning so I can add a shift to it if need be
    $end = new DateTime($mysqli->real_escape_string($postBegin));
    
    if(empty($shiftLength)) //not using a shift length so take the entered time
        $end = new DateTime($mysqli->real_escape_string($postEnding));
    else //add a shift to the start time
        $end->add(new DateInterval('PT'.$shiftLength.'H'));    
    
    if($end < $beg) {
        //add a day to $end if the times crossed midnight
        $end = $end->add(new DateInterval("P1D"));
    }
    
    //interval calculation in hours
    $endSec = strtotime($end->format("Y-m-d H:i:s"));  
    $begSec = strtotime($beg->format("Y-m-d H:i:s"));
    $hours = ($endSec - $begSec) / 3600;
    //SQL TIME format
    $beg = $beg->format("H:i:s");  
    $end = $end->format("H:i:s");
    
    
       
    if($isDateUse){
        if(!empty($postEnding) || !empty($postBegin)){
            //query to insert the record. loops until number of days is reached
            if(!isset($_POST['update'])){
                $confirmBtn = isset($_POST['confirmBtn']) ? true : false;
                $noBtn = isset($_POST['noBtn']) ? true : false;
                
                for($i=0; $i <= $daysOff; $i++){
                    //Check if useDate is already submitted
                    $myq ="SELECT `REFER` , `IDNUM`, `TIMETYPEID` , `USEDATE` , `ENDTIME` , `BEGTIME` , `SUBTYPE`
                        FROM `REQUEST`
                        WHERE `TIMETYPEID` LIKE '".$type."'
                        AND `IDNUM` = '".$ID."'
                        AND `USEDATE` = '".$usedate->format('Y-m-d')."'";
                    $result = $mysqli->query($myq);
                    SQLerrorCatch($mysqli, $result);
                    if($result->num_rows > 0 && !$confirmBtn && !$noBtn){
                        $refNums = "";
                        while($row = $result->fetch_assoc()) {
                             $refNums .= $row['REFER'].', ';
                        }

                        popUpMessage('<div align="center"><form method="POST" action="'.$_SERVER['REQUEST_URI'].'">                    
                            You already submitted for this type of request on '.$usedate->format('Y-m-d').'<br/>
                            Please see Reference Numbers: <br/>'.$refNums. '<br/><br/><h4>Are you sure you want to submit another?</h4>
                                <input type="submit" name="confirmBtn" value="Yes" /> <input type="submit" name="noBtn" value="No" />
                                <input type="hidden" name="type" value="'.$type.'" />
                                <input type="hidden" name="subtype" value="'.$subtype.'" />
                                <input type="hidden" name="shift" value="'.$shiftLength.'" />
                                <input type="hidden" name="ID" value="'.$ID .'" />
                                <input type="hidden" name="usedate" value="'.$postUseDate.'" />
                                <input type="hidden" name="thrudate" value="'.$postThruDate.'" />
                                <input type="hidden" name="beg1" value="'.$postBeg1.'" />
                                <input type="hidden" name="beg2" value="'.$postBeg2.'" />
                                <input type="hidden" name="end1" value="'.$postEnd1.'" />
                                <input type="hidden" name="end2" value="'.$postEnd2.'" />
                                <input type="hidden" name="comment" value="'.$comment.'" />
                                <input type="hidden" name="calloff" value="'.$calloff.'" />
                                <input type="hidden" name="submit" value="true" />
                                </form></div>');
                    }
                    else if($noBtn){
                        echo 'Canceled Submitting Request.';
                    }
                    else if( ($type == 'OT' || $type == 'AG') && strtotime($usedate->format('Y-m-d')) > strtotime(date('Y-m-d')) ){
                       echo '<font color="red">Can not submit for Overtime or Comp Time Gain unless it is on or after the date of use</font>'; 
                    }
                    else{
                        $myq="INSERT INTO REQUEST (IDNUM, USEDATE, BEGTIME, ENDTIME, 
                            HOURS, TIMETYPEID, SUBTYPE, NOTE, STATUS, REQDATE, 
                            AUDITID, IP, CALLOFF)
                                VALUES ('$ID', '".$usedate->format('Y-m-d')."', '$beg', '$end', '$hours', '$type', '$subtype', 
                                        '$comment', 'PENDING', NOW(),'$auditid',INET_ATON('${_SERVER['REMOTE_ADDR']}'), '$calloff')";
                        //echo $myq; //DEBUG
                        $usedate->modify("+1 day"); //add one more day for the next iteration if multiple days off
                        $result = $mysqli->query($myq);

                        //show SQL error msg if query failed
                        if (SQLerrorCatch($mysqli, $result)) {
                            echo 'Request not accepted.';
                        }
                        else {
                            $refInsert = $mysqli->insert_id;
                            addLog($config, 'New Time Request Submitted with Ref# '.$refInsert);
                            echo '<h3>Request accepted. The reference number for this request is <b>' 
                                .$refInsert. '</b>.</h3>';
                            $requestAccepted = true;
                        }
                    } //end validation check
                }//end for loop
            }
        }//end blank start or end time
        else
            echo '<font color="red" >Must provide a valid Start and End time!</font><br /><br />';
    }//end blank use date submission verification
    else
        echo '<font color="red" >Must provide a valid Date!</font><br /><br />';
     


//update an existing record instead of inserting a new one
    if(isset($_POST['update'])){
        $myq="UPDATE REQUEST SET USEDATE='".$config->mysqli->real_escape_string($usedate->format('Y-m-d'))."', 
                BEGTIME='".$config->mysqli->real_escape_string($beg)."', 
                ENDTIME='".$config->mysqli->real_escape_string($end)."', 
                HOURS='".$config->mysqli->real_escape_string($hours)."', 
                TIMETYPEID='".$config->mysqli->real_escape_string($type)."', 
                SUBTYPE='".$config->mysqli->real_escape_string($subtype)."', 
                NOTE='".$config->mysqli->real_escape_string($comment)."', 
                AUDITID='".$config->mysqli->real_escape_string($auditid)."', 
                IP=INET_ATON('".$config->mysqli->real_escape_string($_SERVER['REMOTE_ADDR'])."'), 
                CALLOFF='".$config->mysqli->real_escape_string($calloff)."'
                WHERE REFER=".$config->mysqli->real_escape_string($_POST['referNum']);
        //echo $myq; //DEBUG
                
                $result = $mysqli->query($myq);
                //show SQL error msg if query failed
                if (SQLerrorCatch($mysqli, $result)) {
                    echo 'Error: Request not updated.';
                }
                else {
                    addLog($config, 'Updated Time Request with Ref# '.$_POST['referNum']);
                    echo '<h3>Request updated successfully.</h3>';
                }
    }//end of "is update button pressed?"
} //end of 'is submit or update pressed?'  
if(!isset($_POST['searchBtn'])){
    ?>
    <h2>Employee Request</h2>
    <?php 
}
else{
    echo '<h3>Lookup User</h3>';
}
?>
      
 <form name="leave" id="leave" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
      <input type='hidden' name='formName' value='leave' />
     <?php  
     if ( isset($_POST['referNum']) )
         echo 'Reference Request #'.$_POST['referNum'].'<input type="hidden" name="referNum" value="'.$_POST['referNum'].'" />';
             
        $type  = isset($_POST['type']) ? $_POST['type'] : ''; 
        $myq = "SELECT DESCR FROM TIMETYPE WHERE TIMETYPEID='".$config->mysqli->real_escape_string($type)."'";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $typeDescr = $result->fetch_assoc();
        
        if (!empty($type)) { //$_POST['type'] is set
            //hidden field with type set
            echo "<input type='hidden' name='type' value='".$type."'>";
            
            //Lookup Users button pressed
            if(isset($_POST['searchBtn']) || isset($_POST['findBtn'])){
                //Save any inputed values
                echo '<input type="hidden" name="subtype" value="'.$subtype.'" />';
                echo '<input type="hidden" name="ID" value="'.$postID .'" />';
                echo '<input type="hidden" name="usedate" value="'.$postUseDate.'" />';
                echo '<input type="hidden" name="thrudate" value="'.$postThruDate.'" />';
                echo '<input type="hidden" name="beg1" value="'.$postBeg1.'" />';
                echo '<input type="hidden" name="beg2" value="'.$postBeg2.'" />';
                echo '<input type="hidden" name="end1" value="'.$postEnd1.'" />';
                echo '<input type="hidden" name="end2" value="'.$postEnd2.'" />';
                echo '<input type="hidden" name="comment" value="'.$comment.'" />';
                echo '<input type="hidden" name="calloff" value="'.$calloff.'" />';
                
                //Get additional search inputs
                $searchUser = isset($_POST['searchUser']) ? $_POST['searchUser'] : '';
                $isFullTime = isset($_POST['fullTime']) ? true : false;
                $isReserve = isset($_POST['reserve']) ? true : false;
                
                echo '<input type="checkbox" name="fullTime" ';
                if($isFullTime)
                    echo 'CHECKED';
                echo ' />Full Time Employee&nbsp;&nbsp;  ';
                echo '<input type="checkbox" name="reserve" ';
                if($isReserve)
                    echo 'CHECKED';
                echo ' />Reserves<br />';
                
                echo '<input type="text" name="searchUser" value="'.$searchUser.'" /><input type="submit" name="findBtn" value="Search" /><br /><br />';
                
                if( isset($_POST['findBtn'])){
                    $rowCount = 0;
                    if(!empty($searchUser) && $isFullTime)
                        $rowCount = selectUserSearch($config, $searchUser, $rowCount, true);
                    if($isReserve)
                        $rowCount2 = searchReserves($config, $searchUser, $rowCount);
                    else
                        $rowCount2 = $rowCount;
                    $rowCount3 = searchDatabase($config, $searchUser, $rowCount2);
                    $totalRowsFound = $rowCount + $rowCount2 +$rowCount3;
                    
                    echo '<input type="hidden" name="totalRows" value="'.$totalRowsFound.'" />';
                }//end lookup button pressed
            }//end search or lookup button pressed
            else{
                $foundUserFNAME = isset($foundUserFNAME) ? $foundUserFNAME : '';
                $foundUserLNAME = isset($foundUserLNAME) ? $foundUserLNAME : '';
                $foundUserName = isset($foundUserName) ? $foundUserName : '';
                $foundUserID = isset($foundUserID) ? $foundUserID : '';
                $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : '';
                if($totalRows > 0) {         
                    //get post info providied from search results
                    for($i=0;$i<=$totalRows;$i++){
                        if(isset($_POST['foundUser'.$i])) {
                            $foundUserFNAME = $_POST['foundUserFNAME'.$i];
                            $foundUserLNAME = $_POST['foundUserLNAME'.$i];
                            $foundUserName = $_POST['foundUserName'.$i];
                            $foundUserID = $_POST['foundUserID'.$i];

                            if(isset($_POST['isReserve'.$i]))
                                    echo '<input type="hidden" name="isReserve" value="true" />';
                            break;
                        }//end if
                    }//end for
                }
                //echo "<p><h3>Type of Request: </h3>" . $typeDescr['DESCR'] . "</p>";
                echo "<p><h3>Type of Request: </h3>"; 
                selectTimeType($config, "type", $type);
                echo "</p>";
                //subtype choice
                echo "Subtype: ";
                $myq = "SELECT NAME FROM SUBTYPE";
                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result);
                ?>  <select name="subtype"> <?php
                while($row = $result->fetch_assoc()) {
                    if (strcmp($row['NAME'],$subtype) == 0) 
                        echo '<option value="' . $row["NAME"] . '" SELECTED >'. $row["NAME"] . '</option>';
                    else
                        echo '<option value="' . $row["NAME"] . '">'. $row["NAME"] . '</option>';
                }
                echo "</select> </br>"; 

                if ( $_SESSION['admin'] < 25) //if normal user, allow only their own user name
                    echo "<p>User ID: ".$_SESSION['userName']."<input type='hidden' name='ID' value='".$_SESSION['userIDnum']."'></p>";
                else { //allow any user to be picked for a calloff entry
                    $isCallOff = "";
                    if(isset($_POST['calloff'])){
                        echo '<input type="checkbox" id="calloff" name="calloff" value="YES" CHECKED />';
                    }else
                        echo '<input type="checkbox" id="calloff" name="calloff" value="YES" />';
                    //echo 'onclick=\'addLookupButton("leave");\'';
                    echo 'Call Off (ie. REPORT OFF)<br/>';
                    echo "Employee: ";
                    //user ID passed from search
                    if($totalRows > 0) { 
                        echo '<input type="hidden" name="ID" value="'.$foundUserID.'" />'.$foundUserLNAME.', '.$foundUserFNAME;
                    }
                    else{
                        //dropDownMenu($mysqli, 'FULLNAME', 'EMPLOYEE', $postID, 'ID');
                        $myq = "SELECT `IDNUM` , `LNAME` , `FNAME` 
                            FROM `EMPLOYEE`
                            WHERE `IDNUM` = ".$config->mysqli->real_escape_string($postID);
                        $result = $mysqli->query($myq);
                        SQLerrorCatch($mysqli, $result);
                        $row = $result->fetch_assoc(); 
                        echo $row['LNAME'].', '.$row['FNAME']."<input type='hidden' name='ID' value='".$postID."'>";
                    }
                    echo ' <input type="submit" name="searchBtn" value="Lookup Employee" />';
                    ?>
                    <script language="JavaScript" type="text/javascript">   
                    function addLookupButton(formName) {
//                        var _form = document.getElementById(formName);
//                        var _calloff = document.getElementById('calloff');
//                        if(_calloff.checked){
//                            if(document.getElementById('jsearchBtn')){}
//                            else{
//                                var _search = document.createElement('input');
//                                _search.type = "submit";
//                                _search.name = "searchBtn";
//                                _search.value = "Lookup Employee";
//                                _search.id = "jsearchBtn";
//                                _search.onclick = function(){_form.submit()};
//                                //_form.appendChild(_search);
//                                _form.insertBefore(_search, _calloff);
//                            }   
//                        }
//                        else{
//                            if(document.getElementById('jsearchBtn')){
//                                var _oldSearch = document.getElementById('jsearchBtn');
//                                _form.removeChild(_oldSearch);
//                            }
//                        }
                    }
                    </script>
                    <?php
                        
                }
                
                ?>
                <p>Date of use/accumulation: <?php displayDateSelect('usedate','date_1', $postUseDate , true, !$isDateUse); ?>
                    Through date (optional): <?php displayDateSelect('thrudate','date_2'); ?></p>
                <p>Start time: <?php showTimeSelector("beg", $postBeg1, $postBeg2); ?>
                <?php 

                if($type == 'PR') {
                        echo "<input type='radio' name='shift' value='8'>8 hour shift";
                        echo "<input type='radio' name='shift' value='12'>12 hour shift";
                        echo "</br>(Personal time must be used for an entire shift.)";
                }
                else {
                    ?> End time: <?php showTimeSelector("end", $postEnd1, $postEnd2); ?></p> <?php
                }

                ?> 


                </br>
                <p>Comment: <textarea rows="3" cols="40" name="comment" ><?php echo $comment; ?></textarea></p>
                <?php 
            //popUpMessage($updatingRequest);
                if($updatingRequest==='submittedRequests' || $requestAccepted)
                    echo '<p><input type="hidden" name="formName" value="submittedRequests" />
                        <input type="submit" name="update" value="Update Request">
                        <input type="submit" name="duplicateBtn" value="Duplicate Request" />
                        <INPUT TYPE="button" value="Back to My Requests" onClick="parent.location=\'wts_index.php?myReq=true\'"></p>';
                else if(strpos($updatingRequest, 'hrEmpRep=true'))
                    echo '<p><input type="hidden" name="formName" value="submittedRequests" />
                        <input type="submit" name="update" value="Update Request">
                        <input type="submit" name="duplicateBtn" value="Duplicate Request" />
                        <INPUT TYPE="button" value="Back to Approvals" onClick="parent.location=\''.$updatingRequest.'\'"></p>';
                else
                    echo '<p><input type="submit" name="submit" value="Submit for Approval"></p>';
                ?>

        </form> 


        <?php
            }
        }
        else {
            
            //intitial choice of type
            echo "<p><h3>Type of Request: </h3>";
            dropDownMenu($mysqli, 'DESCR', 'TIMETYPE', FALSE, 'type');
            echo "</p>";
        }
} // end displayLeaveForm()

function displaySubmittedRequests($config){  
    /*
    * A report of recent leave requests with
    * different views according to admin level
    */

    $mysqli = connectToSQL();
    $admin = $_SESSION['admin'];
    
    if(isset($_POST['totalRows'])){
        $totalRows = $_POST['totalRows'];
        for($i=0;$i<=$totalRows;$i++){
            if(isset($_POST['pendingBtn'.$i])){
                $refNo = $_POST['requestID'.$i];
                $myq = $myq="UPDATE REQUEST 
                    SET STATUS='PENDING',
                    `HRAPP_IS` = '0',
                    APPROVEDBY=''
                    WHERE REFER=".$config->mysqli->real_escape_string($refNo);
                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result, $myq);
                addLog($config, 'Ref# '.$refNo.' status was changed to pending');
                break;
            }
        }
    }

    //what pay period are we currently in?
    $payPeriodQuery = "SELECT * FROM PAYPERIOD WHERE NOW() BETWEEN PPBEG AND PPEND";
    $ppResult = $mysqli->query($payPeriodQuery);
    $ppArray = $ppResult->fetch_assoc();

    /* $ppOffset stands for the number of pay periods to adjust the query by 
    * relative to the current period
    */
    $ppOffset = isset($_GET['ppOffset']) ? $_GET['ppOffset'] : '0';
    //set the right URI for link
    if(isset($ppOffset))
        //strip off the old GET variable and its value
        $uri =  preg_replace("/&ppOffset=.*/", "", $_SERVER['REQUEST_URI'])."&ppOffset=";
    else
        $uri = $_SERVER['REQUEST_URI']."&ppOffset="; //1st time set

    $startDate = new DateTime("{$ppArray['PPBEG']}");
    if($ppOffset < 0)
        //backward in time by $ppOffset number of periods
        $startDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
    else
        //forward in time by $ppOffset number of periods
        $startDate->add(new DateInterval("P".($ppOffset*14)."D"));

    $endDate = new DateTime("{$ppArray['PPEND']}");
    if($ppOffset < 0)
        //backward in time by $ppOffset number of periods
        $endDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
    else
        //forward in time by $ppOffset number of periods
        $endDate->add(new DateInterval("P".($ppOffset*14)."D"));

    ?>
    <p><a href="<?php echo $_SERVER['REQUEST_URI'].'&cust=true'; ?>">Use Custom Date Range</a></br>
    <?php 
    echo "<form name='custRange' action='".$_SERVER['REQUEST_URI']."' method='post'>";
    if (isset($_GET['cust'])) {
        
        echo "<p> Start";
        if ( isset($_POST['start']) && isset($_POST['end']) ) {
            displayDateSelect('start', 'date_1', $_POST['start'],false,false);   
            echo "End";
            displayDateSelect('end', 'date_2',$_POST['end'],false,false);
        }
        else{
            displayDateSelect('start', 'date_1', false,false,true);   
            echo "End";
            displayDateSelect('end', 'date_2',false,false,true);
        }
        echo "<input type='submit' value='Go' /></p>";
    }
        if($admin >= 25){
            echo '<div align="center">
            Show Submitted Requests for the following division: 
            <select name="divisionID" onchange="this.form.submit()">';

            if(isset($_POST['divisionID'])){
                $myDivID = $_POST['divisionID'];
            }
            else{
                if($admin >= 50){
                    $myDivID = "All"; 
                }
                else{
                    $mydivq = "SELECT DIVISIONID FROM EMPLOYEE E WHERE E.IDNUM='" . $config->mysqli->real_escape_string($_SESSION['userIDnum'])."'";
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
            if($admin >= 25){
                if(isset($_POST['divisionID'])){
                    if($myDivID == "All")
                        echo '<option value="All" SELECTED>All</option>';
                    else
                        echo '<option value="All">All</option>';
                }
                else
                    echo '<option value="All">All</option>';
            }
            echo '</select><br/><br/>';
            
            $shiftTimeID = showShiftDropDown($config, $myDivID, $onChangeSubmit=true);
            echo '</div>';
    }
        echo "</form>";
        //overwrite current period date variables with 
        //those provided by user
        if ( isset($_POST['start']) && isset($_POST['end']) ) {
            $startDate =  new DateTime( $_POST['start'] );
            $endDate =  new DateTime( $_POST['end'] );
            ?> <h3><center>Gain/Use Requests for <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3> <?php
        }
    
    else {
    ?>
    <p><div style="float:left"><a href="<?php echo $uri.($ppOffset-1); ?>">Previous</a></div>  
    <div style="float:right"><a href="<?php echo $uri.($ppOffset+1); ?>">Next</a></div></p>
    <h3><center>Gain/Use Requests for pay period <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3>
    <?php 
    } 

    switch($admin) { //switch to show different users different reports
            case 0: //normal user, list only user's own reqs

            $myq = "SELECT REFER 'RefNo', DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                            DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                            DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                            APR.LNAME 'ApprovedBy', REASON 'Reason', HRAPP_IS 'isHRApproved' 
                        FROM REQUEST
                        LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
                        INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=REQUEST.TIMETYPEID
                        WHERE REQUEST.IDNUM=" . $_SESSION['userIDnum'] . 
                        " AND USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                            AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."' 
                        ORDER BY REFER";

                break;

            case 25: //supervisor, list by division
                $myq = "SELECT DISTINCT REFER 'RefNo', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Employee', DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                            DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                            DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                            APR.LNAME 'ApprovedBy', REASON 'Reason', HRAPP_IS 'isHRApproved'  
                        FROM REQUEST R
                        INNER JOIN EMPLOYEE AS REQ ON REQ.IDNUM=R.IDNUM
                        LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=R.APPROVEDBY
                        INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=R.TIMETYPEID                         
                        WHERE USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                            AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."' 
                        AND REQ.DIVISIONID IN
                            (SELECT DIVISIONID 
                            FROM EMPLOYEE E
                            WHERE E.IDNUM='" . $config->mysqli->real_escape_string($_SESSION['userIDnum']) . "')
                        ORDER BY REFER";
                break;
            case 50: //HR
                $myq = "SELECT REFER 'RefNo', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Employee', DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                            DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                            DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                            APR.LNAME 'ApprovedBy', REASON 'Reason', HRAPP_IS 'isHRApproved'   
                        FROM REQUEST R
                        INNER JOIN EMPLOYEE AS REQ ON REQ.IDNUM=R.IDNUM
                        LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=R.APPROVEDBY
                        INNER JOIN TIMETYPE AS T ON R.TIMETYPEID=T.TIMETYPEID
                        WHERE USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                            AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."' 
                        ORDER BY REFER";
                break;
            case 99: //Sheriff
                //custom query goes here
                $myq = "SELECT REFER 'RefNo', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Employee', DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                            DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                            DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                            APR.LNAME 'ApprovedBy', REASON 'Reason', HRAPP_IS 'isHRApproved'   
                        FROM REQUEST R
                        INNER JOIN EMPLOYEE AS REQ ON REQ.IDNUM=R.IDNUM
                        LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=R.APPROVEDBY
                        INNER JOIN TIMETYPE AS T ON R.TIMETYPEID=T.TIMETYPEID
                        WHERE USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                            AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."' 
                        ORDER BY REFER";
                break;
            case 100: //full admin, complete raw dump

                $myq = "SELECT REFER 'RefNo', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Employee', DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                            DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                            DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                            APR.LNAME 'ApprovedBy', REASON 'Reason', HRAPP_IS 'isHRApproved'   
                        FROM REQUEST R
                        INNER JOIN EMPLOYEE AS REQ ON REQ.IDNUM=R.IDNUM
                        LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=R.APPROVEDBY
                        INNER JOIN TIMETYPE AS T ON R.TIMETYPEID=T.TIMETYPEID
                        WHERE USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                            AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."' 
                        ORDER BY REFER";
                break;
    } //end switch
    $divisionID = isset($_POST['divisionID']) ? $_POST['divisionID'] : false;
    if(isset($_POST['divisionID'])){
        if($divisionID == "All"){
            $myq = "SELECT DISTINCT REFER 'RefNo', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Employee', DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                            DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                            DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                            APR.LNAME 'ApprovedBy', REASON 'Reason', HRAPP_IS 'isHRApproved'   
                        FROM REQUEST R
                        INNER JOIN EMPLOYEE AS REQ ON REQ.IDNUM=R.IDNUM
                        LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=R.APPROVEDBY
                        INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=R.TIMETYPEID                         
                        WHERE USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                            AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."' 
                        ORDER BY REFER";
        }
        else{
            $myq = "SELECT DISTINCT REFER 'RefNo', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Employee', DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                            DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                            DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                            APR.LNAME 'ApprovedBy', REASON 'Reason', HRAPP_IS 'isHRApproved'   
                        FROM REQUEST R
                        INNER JOIN EMPLOYEE AS REQ ON REQ.IDNUM=R.IDNUM
                        LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=R.APPROVEDBY
                        INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=R.TIMETYPEID                         
                        WHERE USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                            AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."' 
                        AND REQ.DIVISIONID IN (".
                            $config->mysqli->real_escape_string($divisionID).
                        ") ORDER BY REFER";
        }
    }

    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);

    $fieldCount = $result->field_count;
    //load array for table
    //$theTable = array(array());

    //open form
    ?> <form name="submittedRequests" method="POST"> <input type="hidden" name="formName" value="submittedRequests"/> 
    <?php 

    echo '<link rel="stylesheet" href="templetes/DarkTemp/styles/tableSort.css" />
            <script type="text/javascript" src="bin/jQuery/js/tableSort.js"></script>
                <div id="wrapper">';

    echo '<table class="sortable" id="sorter"><tr>';
            //get field info  
    if($admin>=25)
        echo '<th>Edit</th><th>Delete</th>';

    for($y=0; $finfo = $result->fetch_field();$y++) {
        //assign field names as table header (row 0)
        echo '<th>'.(string)$finfo->name.'</th>';
    }
    echo '</tr>';

    for($x=1; $resultArray = $result->fetch_array(MYSQLI_BOTH); $x++) { //record loop
        $leaveStatus = isset($resultArray['Status']) ? $resultArray['Status'] : '';
        $leaveSTATUS = isset($resultArray['STATUS']) ? $resultArray['STATUS'] : '';
        
        if($leaveStatus=='EXPUNGED' || $leaveSTATUS=='EXPUNGED'){
            if($admin>=50)
                echo '<tr style="text-decoration:line-through" >';
        }
        else
            echo '<tr >';

        if($admin>=25){
            for($y=0; $y<$fieldCount+2; $y++){ //field loop    
                if($leaveStatus=='EXPUNGED' || $leaveSTATUS=='EXPUNGED'){
                    if($admin>=50){
                        //edit button that redirects to request page
                        if($y==0)
                        echo '<td><input type="submit"  name="editBtn'.$x.'" value="Edit" onClick="this.form.action=' . "'?leave=true'" . '" />
                            <input type="hidden" name="requestID'.$x.'" value="'.$resultArray[0].'" /></td>';
                        //delete button
                        else if($y==1)
                            echo '<td><button type="submit"  name="unDeleteBtn'.$x.'" value="'.$resultArray[0].'" onClick="this.form.action=' . $_SERVER['REQUEST_URI'] . ';this.form.submit()" >unDelete</button></td>';
                        else //load results
                            echo '<td>'. $resultArray[$y-2].'</td>';
                    }
                }
                else{
                    //Do not allow supervisors to edit once HR approves
                    //edit button that redirects to request page
                    if($y==0){
                        if(!$resultArray['isHRApproved'])
                            echo '<td><input type="submit"  name="editBtn'.$x.'" value="Edit" onClick="this.form.action=' . "'?leave=true'" . '" />
                                <input type="hidden" name="requestID'.$x.'" value="'.$resultArray[0].'" /></td>';
                        else if($admin>=50)
                            echo '<td><input type="submit"  name="editBtn'.$x.'" value="Modify Approval" onClick="this.form.action=' . "'?leave=true'" . '" />
                                <input type="hidden" name="requestID'.$x.'" value="'.$resultArray[0].'" /></td>';
                        else
                            echo '<td></td>';
                    }
                    //delete button
                    else if($y==1){
                        if(!$resultArray['isHRApproved'] || $admin>=50)
                            echo '<td><button type="submit"  name="deleteBtn'.$x.'" value="'.$resultArray[0].'" onClick="this.form.action=' . $_SERVER['REQUEST_URI'] . ';this.form.submit()" >Delete</button></td>';
                        else
                            echo '<td></td>';
                    }
                    else{ //load results
                        if(($resultArray[$y-2] == 'APPROVED' || $resultArray[$y-2] == 'DENIED') && !$resultArray['isHRApproved']){
                            echo '<td>'. $resultArray[$y-2].'<input type="submit" name="pendingBtn'.$x.'" value="Send to Pending" /></td>';
                        }
                        else{
                            echo '<td>'. $resultArray[$y-2].'</td>';
                        }
                    }
                }//end if EXPUNGED
            }//end for loop
        } //end admin table
    }//end array loading

            echo  '<input type="hidden" name="totalRows" value="'.$x.'" />';
            echo '</tr>';
            echo '</table></form></div>
                <script type="text/javascript">
                    var sorter=new table.sorter("sorter");
                    sorter.init("sorter",2);
                </script>';

    //check if we're deleting a record
    for($i=0; $i<$x; $i++){
        if(isset($_POST['deleteBtn'.$i])){
            $refToDelete = $_POST['deleteBtn'.$i];
            //procede w delete
            expungeRequest($mysqli, $refToDelete, false, $deleteIndex=$i, $totalRows=$x);
        }
    }//end of deleteBtn checking loop
    for($i=0; $i<$x; $i++){
        if(isset($_POST['unDeleteBtn'.$i])){
            $refToDelete = $_POST['unDeleteBtn'.$i];
            //procede w delete
            expungeRequest($mysqli, $refToDelete, $unExpunge=true);
        }
    }//end of deleteBtn checking loop



    //showSortableTable($theTable, 0);

    //build table
    //resultTable($mysqli, $result);
    //show a print button. printed look defined by print.css
    echo '<a href="javascript:window.print()">Print</a>';
} //end displaySubmittedRequests()

function approvePOSTLeaveRequests($config){
    $mysqli = $config->mysqli;
    if (isset($_POST['approveBtn'])) {
        echo '<h3>';
        for ($j=0; $j < $_POST['totalRows']; $j++) {
            if (isset($_POST['approve' . $j])) {
               approveLeaveRequest($config, $_POST['refNum'.$j],$_POST['approve'.$j], $_POST['reason'.$j]);
                
            }
        }
    }
}
function approveLeaveRequest($config, $refNo, $status, $reason){
    $mysqli = $config->mysqli;
    $refNo = $mysqli->real_escape_string($refNo);
    $approveQuery="UPDATE REQUEST 
                    SET STATUS='".$mysqli->real_escape_string($status)."',
                        REASON='".$mysqli->real_escape_string($reason)."',
                        APPROVEDBY='".$mysqli->real_escape_string($_SESSION['userIDnum'])."', ApprovedTS=NOW() 
                    WHERE REFER='".$config->mysqli->real_escape_string($refNo)."'";
    //echo $approveQuery; //DEBUG
    $approveResult = $mysqli->query($approveQuery);
    $logMsg = 'Approved Time Request with Ref# '.$refNo;
    addLog($config, $logMsg);
    if(!SQLerrorCatch($mysqli, $approveResult, $approveQuery)){
            echo '<h6>'.$status." Reference ".$refNo."</h6>";
    }
}
function displayLeaveApproval($config){ 
    $mysqli = $config->mysqli;
    ?><form method="post" name="approveBtn"><?php
    
    echo '<h3>Leave Requests Pending Approval</h3>';
    
    approvePOSTLeaveRequests($config);
    /*
    * Form used to approve leave
    * 
    */
    
    $admin = $_SESSION['admin'];
    if($admin >= 25) { 
        $divisionID = isset($_POST['divisionID']) ? $_POST['divisionID'] : false;
        
        $mysqli = connectToSQL();
        
        echo '<div align="center"><a href="?lookup=true">Request Lookup by Employee</a><br/><br />
            Show Submitted Requests for the following division: 
            <select name="divisionID" onchange="this.form.submit()">';

        if(isset($_POST['divisionID'])){
            $divisionID = $_POST['divisionID'];
        }
        else{
            if($admin >= 50){
                $divisionID = "All"; 
            }
            else{
                $mydivq = "SELECT DIVISIONID FROM EMPLOYEE E WHERE E.IDNUM='" . $config->mysqli->real_escape_string($_SESSION['userIDnum'])."'";
                $myDivResult = $mysqli->query($mydivq);
                SQLerrorCatch($mysqli, $myDivResult);
                $temp = $myDivResult->fetch_assoc();
                $divisionID = $temp['DIVISIONID'];
            }
        }

        $alldivq = "SELECT * FROM `DIVISION` WHERE 1";
        $allDivResult = $mysqli->query($alldivq);
        SQLerrorCatch($mysqli, $allDivResult);
        while($Divrow = $allDivResult->fetch_assoc()) {
            echo '<option value="'.$Divrow['DIVISIONID'].'"';
            if($Divrow['DIVISIONID']==$divisionID)
                echo ' SELECTED ';
            echo '>'.$Divrow['DESCR'].'</option>';
        }
        if($divisionID == "All")
            echo '<option value="All" SELECTED>All</option>';
        else
            echo '<option value="All">All</option>';

        echo '</select>';

        echo    '</div><br />';
        
        //Page Breaks Setup
        $prevNum = isset($_POST['prevNum']) ? $_POST['prevNum'] : "0";
        $nextNum = isset($_POST['nextNum']) ? $_POST['nextNum'] : "25";
        $limit= isset($_POST['limit']) ? $_POST['limit'] : "25";

        if(isset($_POST['prevBtn'])){
            $prevNum = $prevNum - $limit;
            $nextNum = $nextNum - $limit;
        }
        if(isset($_POST['nextBtn'])){
            $prevNum = $prevNum + $limit;
            $nextNum = $nextNum + $limit;
        }

        //$shift = isset($_POST['shiftID']) ? $_POST['shiftID'] : '%';
        //  i did add this to a where clause, didn't seem to work: AND E.ASSIGN LIKE '%".$shift."%'

        if(strcmp($divisionID, "All") == 0){
            $myq = "SELECT *                         
                        FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                        WHERE R.TIMETYPEID=T.TIMETYPEID
                        AND   R.IDNUM=E.IDNUM
                        AND STATUS='PENDING'";
        }
        else{
            $myq = "SELECT *                         
                        FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                        WHERE R.TIMETYPEID=T.TIMETYPEID
                        AND   R.IDNUM=E.IDNUM
                        AND STATUS='PENDING'
                        AND E.DIVISIONID IN (".$config->mysqli->real_escape_string($divisionID).")";
        }
        
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $totalRows = $result->num_rows;
        
        if(strcmp($divisionID, "All") == 0){
            $myq = "SELECT DISTINCT REFER 'RefNo', RADIO 'Radio', CONCAT_WS(', ',LNAME,FNAME) 'Employee', 
                            DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                            DATE_FORMAT(USEDATE,'%b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                            DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', NOTE 'Comment', STATUS 'Status'                         
                        FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                        WHERE R.TIMETYPEID=T.TIMETYPEID
                        AND   R.IDNUM=E.IDNUM
                        AND STATUS='PENDING'
                        ORDER BY REFER 
                        LIMIT ".$config->mysqli->real_escape_string($prevNum).",  ".$config->mysqli->real_escape_string($limit);
        }
        else{
            $myq = "SELECT DISTINCT REFER 'RefNo', RADIO 'Radio', CONCAT_WS(', ',LNAME,FNAME) 'Employee', 
                            DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                            DATE_FORMAT(USEDATE,'%b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                            DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', NOTE 'Comment', STATUS 'Status'                         
                        FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                        WHERE R.TIMETYPEID=T.TIMETYPEID
                        AND   R.IDNUM=E.IDNUM
                        AND STATUS='PENDING'
                        AND E.DIVISIONID IN (".$config->mysqli->real_escape_string($divisionID).")
                        ORDER BY REFER 
                        LIMIT ".$config->mysqli->real_escape_string($prevNum).",  ".$config->mysqli->real_escape_string($limit);
        }
        
        
        //echo $myq; //DEBUG

        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);        
        
        //build table
        //resultTable($mysqli, $result);

        $x = 0;
        $y = 0;
        $theTable = array(array());
        
        $echo  = '<table><tr>';
        $result->data_seek(0);
        while($finfo = $result->fetch_field()){
            $echo .= "<th>".$finfo->name."</th>";
            $theTable[$x][$y] = $finfo->name; $y++;
        }
        $x++;
        $echo .= '</tr>';

        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $result->data_seek(0);
        
        $rowCount=0;
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $echo .= "<tr>";
            $refNo = '';
            //$refs[$rowCount] = $row[0]; //save ref # in an array
            for ($i = 0; $i < $mysqli->field_count; $i++) {
                $echo .=  "<td style='white-space: nowrap'>";
                if($i==0){
                    $refNo = $row[$i];
                    $echo .=  '<input type="hidden" name="refNum'.$rowCount.'" value="'.$row[$i].'" />';
                    $theTable[$x][$i] = '<input type="hidden" name="refNum'.$rowCount.'" value="'.$row[$i].'" />'.$row[$i];
                }else{
                    $echo .=  "$row[$i]</td>"; 
                    $theTable[$x][$i] = $row[$i];
                }
            }
            $echo .=  "</tr>";
            $echo .=  "<td style='white-space: nowrap'></td><td>";
            
            $echo .=  "<input type='radio' name='approve$rowCount' value='APPROVED' /> Approved</td> 
                    <td style='white-space: nowrap'><input type='radio' name='approve$rowCount' value='DENIED'> Denied</td>
                    <td style='white-space: nowrap' colspan='8'>Reason:<input type='text' name='reason$rowCount' size='50'/></td>";
            $x++;
            $y = 0;
            $theTable[$x][$y] = ''/*$refNo*/; $y++;
            $theTable[$x][$y] = "<input type='radio' name='approve$rowCount' value='APPROVED' /> Approved"; $y++;
            $theTable[$x][$y] = "<input type='radio' name='approve$rowCount' value='DENIED'> Denied"; $y++;
            $theTable[$x][$y] = "<td colspan='8'>Reason:<input type='text' name='reason$rowCount' size='50'/>"; $y++;
            $theTable[$x][$y] = ''; $y++;
            
            $rowCount++;
            $x++;
        }
        
        echo '<hr />';
        echo '<div align="center">Number of entries found in the reserve database is: ' . $totalRows.'</div>';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<input type="hidden" name="searchFullTime" value="false" />';
        echo '<input type="hidden" name="searchReserves" value="checked" />';
        echo '<input type="hidden" name="prevNum" value="'.$prevNum.'" />';
        echo '<input type="hidden" name="nextNum" value="'.$nextNum.'" />';
        $lastRec = $prevNum + $limit;
        echo '<br/>';
        echo 'Showing Records '. $prevNum . ' to ' .$lastRec;
        //Spacing characters
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        if(!$prevNum > 0){
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        echo 'Records: <select name="limit" onChange="this.form.submit()" >
            <option value="25"';
        if(strcmp($limit, "25") ==0)
            echo ' SELECTED';
        echo '>25</option>
            <option value="50"';
        if(strcmp($limit, "50") ==0)
            echo ' SELECTED';
        echo '>50</option>
            </select>';
        if($prevNum > 0)
            echo '<input type="submit" name="prevBtn" value="Previous" />';
        if($limit == $rowCount)
            echo '<input type="submit" name="nextBtn" value="Next" />';
        echo '<br/>';
        //echo $echo;
        showSortableTable($theTable, 1, $tableID = 'timeApprove',$rowsToSortNext = array(), $noSort = true);
        
        echo '<input type="hidden" name="totalRows" value="'.$rowCount.'" />';

       echo '</table> <p><input type="submit" name="approveBtn" value="Save"></p></form>';

    }
    else
        echo "Permission Denied.";   
} // end displayLeaveApproval()

/* This function will display all requests for the queried user
 * within a given date range.
 * Intended for use by supervisors and above.
 * Possibly add call off functionality
 */
function displayRequestLookup($config) {
   
    $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : '';
    $foundUserFNAME = '';
    $foundUserLNAME = '';
    $foundUserName = '';
    $foundUserID = '' ;
    if($totalRows > 0) {          
        //get post info providied from search results
        for($i=1;$i<=$totalRows;$i++){
            if(isset($_POST['foundUser'.$i])) {
                $foundUserFNAME = $_POST['foundUserFNAME'.$i];
                $foundUserLNAME = $_POST['foundUserLNAME'.$i];
                $foundUserName = $_POST['foundUserName'.$i];
                $foundUserID = $_POST['foundUserID'.$i];

                if(isset($_POST['isReserve'.$i]))
                        echo '<input type="hidden" name="isReserve" value="true" />';
                break;
            }//end if
        }//end for
    }
    
    if( isValidUser($config) && (isset($_POST['lname']) || isset($_POST['editBtn'])) ) {
        if(isset($_POST['lname'])) {
            $lname = $_SESSION['lname'] = strtoupper( $_POST['lname'] );
            $startDate = $_SESSION['start'] = new DateTime ($_POST['start']);
            $endDate = $_SESSION['end'] = new DateTime ($_POST['end']);
        }
        else {
            $lname = $_SESSION['lname'];
            $startDate = $_SESSION['start'];
            $endDate = $_SESSION['end'];
        }
            
        $mysqli = $config->mysqli;
        //query for all time requests if no date selected
        if( !empty($_POST['start']) && !empty($_POST['end']))
            $myq = "SELECT DISTINCT REFER 'RefNo', CONCAT_WS(', ', REQ.LNAME, REQ.FNAME) 'Employee', DATE_FORMAT(REQDATE,'%a %b %d %Y') 'Requested',
                            DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', BEGTIME 'Start',
                            ENDTIME 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                            APR.LNAME 'ApprovedBy', REASON 'Reason' 
                        FROM REQUEST R
                        INNER JOIN TIMETYPE AS T ON R.TIMETYPEID=T.TIMETYPEID
                        LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=R.IDNUM
                        LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=R.APPROVEDBY
                        WHERE USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                            AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."' 
                        AND REQ.LNAME LIKE '%".$config->mysqli->real_escape_string($lname)."%'";
        
        else
            $myq = "SELECT DISTINCT REFER 'RefNo', CONCAT_WS(', ', REQ.LNAME, REQ.FNAME) 'Employee', DATE_FORMAT(REQDATE,'%a %b %d %Y') 'Requested',
                            DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', BEGTIME 'Start',
                            ENDTIME 'End', HOURS 'Hrs',
                            T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                            APR.LNAME 'ApprovedBy', REASON 'Reason' 
                        FROM REQUEST R
                        INNER JOIN TIMETYPE AS T ON R.TIMETYPEID=T.TIMETYPEID
                        LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=R.IDNUM
                        LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=R.APPROVEDBY                  
                        WHERE REQ.LNAME LIKE '%".$config->mysqli->real_escape_string($lname)."%'";
        //popUpMessage($myq); //DEBUG
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        resultTable($mysqli, $result);
        echo "<a href='".$_SERVER['REQUEST_URI']."'>Back to Search</a>";
        
        
    }
    else {
        ?>
        <form name="lookup" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <input type="hidden" name="formName" value="lookup" />
            <input type="hidden" name="searchReserves" value="false" />
        <h1>Lookup Requests by Employee</h1>
        
        <p>Search by last name:
            
            <input type="text" name="lname" value="<?php echo $foundUserLNAME; ?>" /> or <?php displayUserLookup($config); ?></p>
        <p>Date range: From <?php   displayDateSelect('start','date_1'); ?>
            to <?php   displayDateSelect('end','date_2'); ?></p>
        <p>(Leave date range blank to show requests for all time.)</p>
        
        <p><input type="submit" name="Submit" value="Search"></p>
        </form>
        <?php
    }
}
/* This funciton will display a report summarizing all
 * of the time used/gained for a pay period
 * for entry into MUNIS.
 */
function displayTimeUseReport ($config) {
    //what pay period are we currently in?
    $mysqli = $config->mysqli;
    
    $payPeriodQuery = "SELECT * FROM PAYPERIOD WHERE NOW() BETWEEN PPBEG AND PPEND";
    $ppResult = $mysqli->query($payPeriodQuery);
    $ppArray = $ppResult->fetch_assoc();

    /* $ppOffset stands for the number of pay periods to adjust the query by 
    * relative to the current period
    */
    $ppOffset = isset($_GET['ppOffset']) ? $_GET['ppOffset'] : '0';
    //set the right URI for link
    if(isset($ppOffset))
        //strip off the old GET variable and its value
        $uri =  preg_replace("/&ppOffset=.*/", "", $_SERVER['REQUEST_URI'])."&ppOffset=";
    else
        $uri = $_SERVER['REQUEST_URI']."&ppOffset="; //1st time set

    $startDate = new DateTime("{$ppArray['PPBEG']}");
    if($ppOffset < 0)
        //backward in time by $ppOffset number of periods
        $startDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
    else
        //forward in time by $ppOffset number of periods
        $startDate->add(new DateInterval("P".($ppOffset*14)."D"));

    $endDate = new DateTime("{$ppArray['PPEND']}");
    if($ppOffset < 0)
        //backward in time by $ppOffset number of periods
        $endDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
    else
        //forward in time by $ppOffset number of periods
        $endDate->add(new DateInterval("P".($ppOffset*14)."D"));

    ?>
    <p><a href="<?php echo $_SERVER['REQUEST_URI'].'&cust=true'; ?>">Use Custom Date Range</a></br>
    <?php 
    if (isset($_GET['cust'])) {
        echo "<form name='custRange' action='".$_SERVER['REQUEST_URI']."' method='post'>";
        echo "<p> Start";
        displayDateSelect('start', 'date_1');   
        echo "End";
        displayDateSelect('end', 'date_2');
        echo "<input type='submit' value='Go' /></p></form>";
        //overwrite current period date variables with 
        //those provided by user
        if ( isset($_POST['start']) && isset($_POST['end']) ) {
            $startDate =  new DateTime( $_POST['start'] );
            $endDate =  new DateTime( $_POST['end'] );
            ?> <h3><center>Time Gained/Used from <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3> <?php
        }
    }
    else {
    ?>
    <p><div style="float:left"><a href="<?php echo $uri.($ppOffset-1); ?>">Previous</a></div>  
    <div style="float:right"><a href="<?php echo $uri.($ppOffset+1); ?>">Next</a></div></p>
    <h3><center>Time Gained/Used in pay period <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3>
    <?php
   
    $myq = "SELECT DIVISIONID 'Div', MUNIS, CONCAT_WS(', ',LNAME,FNAME) 'Name', T.DESCR 'Type', CAST(SUM(HOURS) as DECIMAL(5,2)) 'Total (hrs)'
            FROM REQUEST R, EMPLOYEE E, TIMETYPE T
            WHERE R.IDNUM=E.IDNUM AND T.TIMETYPEID = R.TIMETYPEID
            AND USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."'
            AND STATUS='APPROVED'
            GROUP BY E.DIVISIONID, R.IDNUM, R.TIMETYPEID
            ORDER BY E.DIVISIONID, LNAME";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    resultTable($mysqli, $result);
    }
    //show a print button. printed look defined by print.css
    echo '<a href="javascript:window.print()">Print</a>';
    
}

function approvedTimeUseReport ($config) {
    //what pay period are we currently in?
    $mysqli = $config->mysqli;
    
    $payPeriodQuery = "SELECT * FROM PAYPERIOD WHERE NOW() BETWEEN PPBEG AND PPEND";
    $ppResult = $mysqli->query($payPeriodQuery);
    $ppArray = $ppResult->fetch_assoc();

    /* $ppOffset stands for the number of pay periods to adjust the query by 
    * relative to the current period
    */
    $ppOffset = isset($_GET['ppOffset']) ? $_GET['ppOffset'] : '0';
    //set the right URI for link
    if(isset($ppOffset))
        //strip off the old GET variable and its value
        $uri =  preg_replace("/&ppOffset=.*/", "", $_SERVER['REQUEST_URI'])."&ppOffset=";
    else
        $uri = $_SERVER['REQUEST_URI']."&ppOffset="; //1st time set

    $startDate = new DateTime("{$ppArray['PPBEG']}");
    if($ppOffset < 0)
        //backward in time by $ppOffset number of periods
        $startDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
    else
        //forward in time by $ppOffset number of periods
        $startDate->add(new DateInterval("P".($ppOffset*14)."D"));

    $endDate = new DateTime("{$ppArray['PPEND']}");
    if($ppOffset < 0)
        //backward in time by $ppOffset number of periods
        $endDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
    else
        //forward in time by $ppOffset number of periods
        $endDate->add(new DateInterval("P".($ppOffset*14)."D"));

    ?>
    <p><a href="<?php echo $_SERVER['REQUEST_URI'].'&cust=true'; ?>">Use Custom Date Range</a></br>
    <?php 
    if (isset($_GET['cust'])) {
        echo "<form name='custRange' action='".$_SERVER['REQUEST_URI']."' method='post'>";
        echo "<p> Start";
        displayDateSelect('start', 'date_1');   
        echo "End";
        displayDateSelect('end', 'date_2');
        echo "<input type='submit' value='Go' /></p></form>";
        //overwrite current period date variables with 
        //those provided by user
        if ( isset($_POST['start']) && isset($_POST['end']) ) {
            $startDate =  new DateTime( $_POST['start'] );
            $endDate =  new DateTime( $_POST['end'] );
            ?> <h3><center>Time Gained/Used from <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3> <?php
        }
    }
    else {
    ?>
    <p><div style="float:left"><a href="<?php echo $uri.($ppOffset-1); ?>">Previous</a></div>  
    <div style="float:right"><a href="<?php echo $uri.($ppOffset+1); ?>">Next</a></div></p>
    <h3><center>Time Gained/Used in pay period <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3>
    <?php
   
    $myq = "SELECT REFER 'RefNo', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', 
                    DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                    DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                    T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', 
                    APR.LNAME 'ApprovedBy', REASON 'Reason' 
            FROM REQUEST
            LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
            LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
            INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=REQUEST.TIMETYPEID
            WHERE USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."'
            AND STATUS='APPROVED'
            ORDER BY REQ.LNAME";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    echo "<h2>Approved Requests</h2>";
    resultTable($mysqli, $result);
    }
    //show a print button. printed look defined by print.css
    echo '<a href="javascript:window.print()">Print</a>';
    
}

function MUNISreport($config) {
    //a table looking similar to MUNIS entry for ease of use
    if(isset($_POST['ppselect'])) {
        $mysqli = $config->mysqli;
        $myq = "SELECT * FROM PAYPERIOD WHERE ID='".$config->mysqli->real_escape_string($_POST['ppselect'])."'";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $selectedPP = $result->fetch_assoc();
        $day = new DateTime($selectedPP['PPBEG']);
        
        echo "<table><tr>";
        for($i=0;$i<14; $i++){
            if($i!=0)
                $day->add(new DateInterval("P1D"));
            
            echo "<th>".$day->format('D m/d')."</th>";
            $queryDate[$i] = $day->format('Y-m-d'); //store each day in SQL format
        }
        echo "</tr>";
        echo "<tr>";
        foreach($queryDate as $date) {
            $myq = "SELECT LNAME, FNAME, TIMETYPEID, HOURS
                    FROM EMPLOYEE E, REQUEST R
                    WHERE E.IDNUM=R.IDNUM
                    AND USEDATE='".$config->mysqli->real_escape_string($date)."'"
                    ." ";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            echo "<td>";
            while($row = $result->fetch_assoc()){
                echo $row['TIMETYPEID'].":".$row['HOURS'];
            }
            echo "</td>";
        }
        echo "</tr>";
        echo "</table>";
    }
    else{
    echo "Choose a payperiod: ";
    $mysqli = $config->mysqli;
    $myq = "SELECT ID FROM PAYPERIOD WHERE NOW() BETWEEN PPBEG AND PPEND";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $currentPP = $result->fetch_assoc();
    
    $myq = "SELECT * FROM PAYPERIOD";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    ?><form method="post">
        <select name="ppselect">
            <?php while($row = $result->fetch_assoc()){
                    echo "<option value= '".$row['ID']."' ";
                    if($row['ID']===$currentPP['ID'])
                        echo "selected='selected' >";
                    else
                        echo ">";
                    
                        echo $row['ID']." </option>";
                  } ?>
        </select>
        <input type="submit" name="Submit" value="Go">
    </form>
    
<?php
    }
}

function expungeRequest($mysqli, $referNum, $unExpunge=false, $delBtnIndex=false, $totalRows=false, $extraInputs='') {
    $confirmBtn = isset($_POST['confirmBtn']) ? true : false;
    
    if($unExpunge){
        if(!isset($_POST['okBtn'])){
            $myq="UPDATE REQUEST 
                SET STATUS='PENDING'
                WHERE REFER=".$mysqli->real_escape_string($referNum);
            $result = $mysqli->query($myq);

            if(!SQLerrorCatch($mysqli, $result, $myq, $debug=false)){
                $configNew = new Config();
                $configNew->setAdmin(isset($_SESSION['admin']) ? $_SESSION['admin'] : -1); 
                popUpMessage ('Request '.$referNum.' Has been placed back into PENDING State. 
                        <div align="center"><form method="POST">
                        '.$extraInputs.'                    
                        <input type="submit" name="okBtn" value="OK" />
                        </form></div>');
                addLog($configNew->mysqli, 'UnExpunged Time Request with Ref# '.$referNum);
            }
        }
    }
    else{
        
            if($confirmBtn && !empty($_POST['expungedReason']) && $_SESSION['admin']){
                $myq="UPDATE REQUEST 
                    SET STATUS='EXPUNGED',
                    HRAPP_ID='0',
                    EX_REASON='".$mysqli->real_escape_string($_POST['expungedReason'])."',
                    AUDITID='".$mysqli->real_escape_string($_SESSION['userIDnum'])."',
                    IP= INET_ATON('".$mysqli->real_escape_string($_SERVER['REMOTE_ADDR'])."')
                    WHERE REFER='".$mysqli->real_escape_string($referNum)."'";
                $result = $mysqli->query($myq);

                if(!SQLerrorCatch($mysqli, $result, $myq, $debug=false)){
                    $configNew = new Config();
                    $configNew->setAdmin(isset($_SESSION['admin']) ? $_SESSION['admin'] : -1);
                    addLog($configNew, 'Expunged Time Request with Ref# '.$referNum);
                    popUpMessage ('Request '.$referNum.' expunged. 
                                <div align="center"><form method="POST" action="'.$_SERVER['REQUEST_URI'].'">
                                '.$extraInputs.'                     
                                <input type="submit" name="okBtn" value="OK" />
                                </form></div>');
                }
            }
            else{
                if(!isset($_POST['okBtn'])){
                $result ="";
                if(isset($_POST['expungedReason'])){
                    if(empty($_POST['expungedReason']))
                        $result = '<font color="red">Requires a Reason</font><br/>';
                }
                $echo = '<div align="center"><form method="POST" action="'.$_SERVER['REQUEST_URI'].'">
                    <input name="deleteBtn'.$delBtnIndex.'" type="hidden" value="'.$referNum.'" />
                    <input type="hidden" name="totalRows" value="'.$totalRows.'" />
                    Request '.$referNum.' to be expunged<br/>   '.$result.'
                    Reason:<textarea name="expungedReason"></textarea><br/>
                    <input type="submit" name="confirmBtn" value="CONFIRM EXPUNGE" />
                    <input type="submit" name="okBtn" value="CANCEL" />
                    '.$extraInputs.' 
                    </form></div>';
                popUpMessage ($echo);
                }
            }
        }  
}

function displayMySubmittedRequests($config){   
/*
 * A report of recent leave requests with
 * different views according to admin level
 */

$mysqli = $config->mysqli;
$admin = $config->adminLvl;

//what pay period are we currently in?
$payPeriodQuery = "SELECT * FROM PAYPERIOD WHERE NOW() BETWEEN PPBEG AND PPEND";
$ppResult = $mysqli->query($payPeriodQuery);
$ppArray = $ppResult->fetch_assoc();

/* $ppOffset stands for the number of pay periods to adjust the query by 
 * relative to the current period
 */
$ppOffset = isset($_GET['ppOffset']) ? $_GET['ppOffset'] : '0';
//set the right URI for link
if(isset($ppOffset))
    //strip off the old GET variable and its value
    $uri =  preg_replace("/&ppOffset=.*/", "", $_SERVER['REQUEST_URI'])."&ppOffset=";
else
    $uri = $_SERVER['REQUEST_URI']."&ppOffset="; //1st time set

$startDate = new DateTime("{$ppArray['PPBEG']}");
if($ppOffset < 0)
    //backward in time by $ppOffset number of periods
    $startDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
else
    //forward in time by $ppOffset number of periods
    $startDate->add(new DateInterval("P".($ppOffset*14)."D"));

$endDate = new DateTime("{$ppArray['PPEND']}");
if($ppOffset < 0)
    //backward in time by $ppOffset number of periods
    $endDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
else
    //forward in time by $ppOffset number of periods
    $endDate->add(new DateInterval("P".($ppOffset*14)."D"));

?>
<p><a href="<?php echo $_SERVER['REQUEST_URI'].'&cust=true'; ?>">Use Custom Date Range</a></br>
<?php 
if (isset($_GET['cust'])) {
    echo "<form name='custRange' action='".$_SERVER['REQUEST_URI']."' method='post'>";
    echo "<p> Start";
    if ( isset($_POST['start']) && isset($_POST['end']) ) {
        displayDateSelect('start', 'date_1', $_POST['start'],false,false);   
        echo "End";
        displayDateSelect('end', 'date_2',$_POST['end'],false,false);
    }
    else{
        displayDateSelect('start', 'date_1', false,false,true);   
        echo "End";
        displayDateSelect('end', 'date_2',false,false,true);
    }
    echo "<input type='submit' value='Go' /></p></form>";
    //overwrite current period date variables with 
    //those provided by user
    if ( isset($_POST['start']) && isset($_POST['end']) ) {
        $startDate =  new DateTime( $_POST['start'] );
        $endDate =  new DateTime( $_POST['end'] );
        ?> <h3><center>Gain/Use Requests for <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3> <?php
    }
}
else {
?>
<p><div style="float:left"><a href="<?php echo $uri.($ppOffset-1); ?>">Previous</a></div>  
   <div style="float:right"><a href="<?php echo $uri.($ppOffset+1); ?>">Next</a></div></p>
<h3><center>Gain/Use Requests for pay period <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3>
<?php 
} ?>

<?php


        $myq = "SELECT REFER 'RefNo', DATE_FORMAT(REQDATE,'%b %d %Y %H%i') 'Requested', 
                        DATE_FORMAT(USEDATE,'%a %b %d %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                        APR.LNAME 'ApprovedBy', REASON 'Reason', HRAPP_IS 'HRApproved' 
                    FROM REQUEST
                    LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
                    INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=REQUEST.TIMETYPEID
                    WHERE REQUEST.IDNUM=" . $config->mysqli->real_escape_string($_SESSION['userIDnum']) . 
                    " AND USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
                    AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."' 
                    ORDER BY REFER";
    
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);

    $fieldCount = $result->field_count;
    //load array for table
    //$theTable = array(array());

    //open form
    ?> <form name="submittedRequests" method="POST"> <input type="hidden" name="formName" value="submittedRequests"/> 
    <?php 

    echo '<link rel="stylesheet" href="templetes/DarkTemp/styles/tableSort.css" />
            <script type="text/javascript" src="bin/jQuery/js/tableSort.js"></script>
                <div id="wrapper">';

    echo '<table class="sortable" id="sorter"><tr>';
            //get field info

    $echo = '';
    for($y=0; $finfo = $result->fetch_field();$y++) {
        //assign field names as table header (row 0)

        $echo .= '<th>'. $finfo->name.'</th>';
    }
    $echo .= '</tr>';
    if($admin < 25){
            $echo = '<th>Edit</th>'.$echo;
    }
    else{
        $echo = '<th>Edit</th><th>Delete</th>'.$echo;

    }

    for($x=1; $resultArray = $result->fetch_array(MYSQLI_BOTH); $x++) { //record loop
        $leaveStatus = isset($resultArray['Status']) ? $resultArray['Status'] : '';
        $leaveSTATUS = isset($resultArray['STATUS']) ? $resultArray['STATUS'] : '';

        if($leaveStatus=='EXPUNGED' || $leaveSTATUS=='EXPUNGED'){
            $echo .= '<tr style="text-decoration:line-through" >';
        }
        else
            $echo .= '<tr >';

        if($admin>0){
            for($y=0; $y<$fieldCount+2; $y++){ //field loop    
                //edit button that redirects to request page
                if($y==0){
                    if(!$resultArray['HRApproved'] && !($leaveStatus=='EXPUNGED' || $leaveSTATUS=='EXPUNGED'))
                        $echo .= '<td><input type="submit"  name="editBtn'.$x.'" value="Edit" onClick="this.form.action=' . "'?leave=true'" . '" />
                            <input type="hidden" name="requestID'.$x.'" value="'.$resultArray[0].'" /></td>';
                    else
                        $echo .= '<td></td>';
                }
                //delete button
                else if($y==1){
                    if(!$resultArray['HRApproved'] && !($leaveStatus=='EXPUNGED' || $leaveSTATUS=='EXPUNGED'))
                        $echo .='<td><button type="submit"  name="deleteBtn'.$x.'" value="'.$resultArray[0].'" onClick="this.form.action=' . $_SERVER['REQUEST_URI'] . ';this.form.submit()" >Delete</button></td>';
                    else
                        $echo .= '<td></td>';
                }
                else //load results
                    $echo .= '<td>'. $resultArray[$y-2].'</td>';

            }
        } //end admin table
        else { //no edit capabilities
            if($leaveStatus=='PENDING' || $leaveSTATUS=='PENDING' && !($leaveStatus=='EXPUNGED' || $leaveSTATUS=='EXPUNGED'))
                    $echo .= '<td><input type="submit"  name="editBtn'.$x.'" value="Edit" onClick="this.form.action=' . "'?leave=true'" . '" />
                        <input type="hidden" name="requestID'.$x.'" value="'.$resultArray[0].'" /></td>';
            else
                $echo .= '<td></td>';
            for($y=0; $y<$fieldCount; $y++){ //field loop 
                //load results
                $echo .= '<td>'. $resultArray[$y].'</td>';        
            }
        }
    }//end array loading


            $echo .=   '<input type="hidden" name="totalRows" value="'.$x.'" />';
            $echo .=  '</tr>';
            $echo .=  '</table></form></div>
                <script type="text/javascript">
                    var sorter=new table.sorter("sorter");
                    sorter.init("sorter",2);
                </script>';
            echo $echo;

    //check if we're deleting a record
    for($i=0; $i<$x; $i++){
        if(isset($_POST['deleteBtn'.$i])){
            $refToDelete = $_POST['deleteBtn'.$i];
            //procede w delete
            expungeRequest($mysqli, $refToDelete, false, $deleteIndex=$i, $totalRows=$x);
        }
    }//end of deleteBtn checking loop



    //showSortableTable($theTable, 0);

    //build table
    //resultTable($mysqli, $result);
    //show a print button. printed look defined by print.css
    echo '<a href="javascript:window.print()">Print</a>';
} //end displaySubmittedRequests()

function selectTimeType($config, $inputName, $selected=false, $onChangeSubmit=false){
    //assumes to be part of a form
    //provides a drop down selection for time type.
    $mysqli = $config->mysqli;
    if($onChangeSubmit)
        echo '<select name="'.$inputName.'" onchange="this.form.submit()">';
    else
        echo '<select name="'.$inputName.'" >';
    
    $myq = "SELECT TIMETYPEID, DESCR FROM TIMETYPE";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    
    while($row = $result->fetch_assoc()){
        if($row['TIMETYPEID'] == $selected)
            echo '<option value="'.$row['TIMETYPEID'].'" SELECTED>'.$row['DESCR'].'</option>';
        else
            echo '<option value="'.$row['TIMETYPEID'].'">'.$row['DESCR'].'</option>';
        
    }
    
    echo '</select>';
}
function showTimeRequestTable($config, $filters, $orderBy = "ORDER BY REFER DESC", $hiddenInput=''){
    $mysqli = $config->mysqli;
    
    if(isset($_POST['timeRequestTableRows'])){
        $totalRows = $_POST['timeRequestTableRows'];
        $btnPushed = false;
        for($i=0;$i<=$totalRows;$i++){
            if(isset($_POST['pendingBtn'.$i])){
                $refNo = $_POST['refNo'.$i];
                $hrNotes = isset($_POST['hrReason'.$i]) ? $_POST['hrReason'.$i] : '';
                sendRequestToPending($config, $refNo, $hrNotes);
                $btnPushed = true;
            }
            elseif(isset($_POST['approve'.$i])){
                $postReason = isset($_POST['reason'.$i]) ? $_POST['reason'.$i] : '';
                approveLeaveRequest($config, $_POST['refNo'.$i], "APPROVED", $postReason);
                $btnPushed = true;
                
            }
            elseif(isset($_POST['deny'.$i])){                
                approveLeaveRequest($config, $_POST['refNo'.$i], "DENIED", $_POST['reason'.$i]);
                $btnPushed = true;
            }
            elseif(isset($_POST['hrApproveBtn'.$i])){
                $hrNotes = isset($_POST['hrReason'.$i]) ? $_POST['hrReason'.$i] : isset($_POST['hrOldNotes'.$i]) ? $_POST['hrOldNotes'.$i] : '';
                hrApproveLeaveRequest($config, $_POST['refNo'.$i], $hrNotes);
                $btnPushed = true;
            }
            elseif(isset($_POST['expungeBtn'.$i]) || isset($_POST['unExpungeBtn'.$i])){
                $toExpungeRefNo = $_POST['refNo'.$i];
                $toExpungeIndex = $i;
                $toExpungeTotalRows = $totalRows;
                $toExpunge = true;
                $toUnExpunge = false;
                if(isset($_POST['unExpungeBtn'.$i]))
                        $toUnExpunge = true;
               $btnPushed = true; 
            }
            if($btnPushed){
                echo "<script language=\"javascript\" >
                    document.body.onload = new  function () {
                        window.location.hash = '#editBtn".$i."';
                    }
                </script>
                ";
                break;
            }
        }
    }
    if($config->adminLvl < 25){
        //only allow to search own reference numbers
        $filters = "'WHERE REQUEST.IDNUM = '".$config->mysqli->real_escape_string($_SESSION['userIDnum']);
    }
    $myq = "SELECT REFER 'RefNo', REQ.MUNIS 'Munis', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', 
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
                ".$filters."
                ".$config->mysqli->real_escape_string($orderBy)."
                ";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result, $myq, $debug=FALSE);

        $theTable = array(array());
        $x = 0;
        $y = 0;

        $theTable[$x][$y] = "Actions"; $y++;
        $theTable[$x][$y] = "Ref#"; $y++;
        $theTable[$x][$y] = "Employee"; $y++;
        $theTable[$x][$y] = "Date_of_Use"; $y++;
        $theTable[$x][$y] = "Start Time"; $y++;
        $theTable[$x][$y] = "End Time"; $y++;
        $theTable[$x][$y] = "Hours"; $y++;
        $theTable[$x][$y] = "Type"; $y++;
        $theTable[$x][$y] = "Subtype"; $y++;
        $theTable[$x][$y] = "Call Off"; $y++;
        $theTable[$x][$y] = "Comment"; $y++;
        $theTable[$x][$y] = 'Status'; $y++;
        $theTable[$x][$y] = 'Approved By'; $y++;
        $theTable[$x][$y] = 'Approved Time'; $y++;
        $theTable[$x][$y] = 'Reason';$y++;
        $theTable[$x][$y] = 'HR Approval';$y++;
        $theTable[$x][$y] = 'HR Notes';$y++;
        $x++;

        while($row = $result->fetch_assoc()) {
            $y=0;
            
            $theTable[$x][$y] = '<input type="submit" id="editBtn'.$x.'" name="editBtn'.$x.'" value="Edit/View" onClick="this.form.action=' . "'?leave=true'" . '; this.form.submit()" />'.
                 '<input type="hidden" name="requestID'.$x.'" value="'.$row['RefNo'].'" />';
            if($row['Status'] == "EXPUNGED")
                $theTable[$x][$y] .= '';
            else{
                if(!$row['HR_Approved'])
                      $theTable[$x][$y] .= '<input type="submit" name="expungeBtn'.$x.'" value="Delete" />';
                if($row['HR_Approved'] && $config->adminLvl >=50 && $config->adminLvl !=75)
                      $theTable[$x][$y] .= '<input type="submit" name="expungeBtn'.$x.'" value="Delete" />';
            }
            $y++;
            $theTable[$x][$y] = '<input type="hidden" name="refNo'.$x.'" value="'.$row['RefNo'].'" />'.$row['RefNo']; $y++;
            $empMunis = $row['Munis'];
            $empName = $row['Name'];
            $theTable[$x][$y] = $empName; $y++;
            $theTable[$x][$y] = $row['Used']; $y++;
            $theTable[$x][$y] = $row['Start']; $y++;
            $theTable[$x][$y] = $row['End']; $y++;
            $theTable[$x][$y] = $row['Hrs']; $y++;
            $theTable[$x][$y] = $row['Type']; $y++;
            $theTable[$x][$y] = $row['Subtype']; $y++;
            $theTable[$x][$y] = $row['Calloff']; $y++;
            $theTable[$x][$y] = $row['Comment']; $y++;
            if($row['Status'] != 'PENDING' && $config->adminLvl >=25){
                $theTable[$x][$y] = $row['Status'];
                if(!empty($row['Reason']))
                    $theTable[$x][$y] .= '<br/><font color="darkred">'.$row['Reason'].'</font>';
                if(!$row['HR_Approved'])
                    $theTable[$x][$y] .= '<Br/><input type="submit" name="pendingBtn'.$x.'" value="Send to Pending" />';
                elseif($row['HR_Approved'] && $config->adminLvl >=50 && $config->adminLvl !=75)
                    $theTable[$x][$y] .= '<Br/><input type="submit" name="pendingBtn'.$x.'" value="Send to Pending" />';
            }
            elseif ($row['Status'] == 'PENDING' && $config->adminLvl >=25){
                $theTable[$x][$y] = $row['Status'];
                $theTable[$x][$y] .= "<br/><input type='submit' name='approve$x' value='APPROVED' size='15'/> ";
                $theTable[$x][$y] .= "<input type='submit' name='deny$x' value='DENIED' size='15'><br/>";
                $theTable[$x][$y] .= 'Reason:<br/><textarea rows="2" cols="21" name="reason'.$x.'" ></textarea>';
            }
            else{
                 $theTable[$x][$y] = $row['Status'].'</br><font color="darkred">'.$row['Reason'].'</font>';
            }
            $y++;
            $theTable[$x][$y] = $row['ApprovedBy']; $y++;
            $theTable[$x][$y] = $row['approveTS']; $y++;
            $theTable[$x][$y] = $row['Reason'];$y++;
            if(!$row['HR_Approved'] && $row['Status'] != "DENIED"){
                    $theTable[$x][$y] = 'Pending';
                if($row['Status'] == "APPROVED" && $config->adminLvl >=50 && $config->adminLvl !=75){
                    $theTable[$x][$y] = '<font color="darkred">Pending</font>';
                    $theTable[$x][$y] .= '<input type="submit" name="hrApproveBtn'.$x.'" value="HR Approve" />';$y++;
                    $theTable[$x][$y] = '<textarea rows="2" cols="21" name="hrReason'.$x.'" ></textarea>';
                }
                else{
                    $y++;
                    $theTable[$x][$y] = '';
                }
            }
            elseif($row['Status'] == "DENIED"){
                $theTable[$x][$y] = 'No Action Required';$y++;
                $theTable[$x][$y] = '<font color="darkred">
                    <input type="hidden" name="hrOldNotes'.$x.'" value="'.$row['HRNOTES'].'" />'.$row['HRNOTES'].'</font>';
            }                
            else{
                $theTable[$x][$y] = '<div align="center"><h3><font color="darkred">Approved</font></h3></div>';$y++;
                $theTable[$x][$y] = '<font color="darkred">
                    <input type="hidden" name="hrOldNotes'.$x.'" value="'.$row['HRNOTES'].'" />'.$row['HRNOTES'].'</font>';
            }
            $y++;
            
            

            $x++;
        }
        if($config->adminLvl >=50 && $config->adminLvl !=75)
            showSortableTable($theTable, 2, "timeRequestTable");
        else
            showSortableTable($theTable, 2, "timeRequestTable");
        echo '<input type="hidden" name="timeRequestTableRows" value="'.$x.'" />';
        
        if($toExpunge){
            echo '</form>';
            $hiddenInput .= '<input type="hidden" name="timeRequestTableRows" value="2" />
                <input type="hidden" name="expungeBtn1" value="true" />
                <input type="hidden" name="refNo1" value="'.$toExpungeRefNo.'" />
                ';
           expungeRequest($config->mysqli, $toExpungeRefNo, $toUnExpunge, $toExpungeIndex, $toExpungeTotalRows, $hiddenInput);
           echo '<form method=POST name="requestTable">';
        }
        
}
function getTimeRequestFilterByEmpID($config, $empID){
    return "REQUEST.IDNUM = '".$config->mysqli->real_escape_string($empID)."'";
}
function getTimeRequestFilterBetweenDates($config, $startDate, $endDate){
    return "USEDATE BETWEEN '". $config->mysqli->real_escape_string($startDate->format('Y-m-d'))."' 
        AND '".$config->mysqli->real_escape_string($endDate->format('Y-m-d'))."'";
}
function  showShiftDropDown($config, $divID, $onchangeSubmit){
    $shiftTimeID = '';

    if(isset($_POST['shiftTime'])){
        $shiftTimeID = $config->mysqli->real_escape_string($_POST['shiftTime']); 
    }
    else{
        $currentTime = time();
        $dayShiftStart = mktime(7,00,0);
        $nightShiftStart = mktime(19,00,0);
        
        if($currentTime < $dayShiftStart || $currentTime >= $nightShiftStart){
            //Between midnight and 0659 or 1900 and midnight
            $shiftTimeID = "2";
        }
        elseif($currentTime >= $dayShiftStart && $currentTime < $nightShiftStart){
            //Between 0700 and 1859
            $shiftTimeID = "1";
        }
    }
        
    //Get all possible values
    $myq = "SELECT IDNUM, NAME, 
            DATE_FORMAT(BEGTIME,'%H%i') AS 'BEG_TIME',
            DATE_FORMAT(ENDTIME ,'%H%i') AS 'END_TIME'
        FROM SHIFT
        WHERE DIVISION_ID = '".$config->mysqli->real_escape_string($divID)."' AND
            ENDDATE IS NULL
        ORDER BY SORT";
    $result = $config->mysqli->query($myq);
    SQLerrorCatch($config->mysqli, $result, $myq, $debug=FALSE);
    if($result->num_rows > 0){
        echo '<select name="shiftTime"';
        if($onchangeSubmit)
            echo ' onchange="this.form.submit()" ';
        echo '>';

        while($row = $result->fetch_assoc()) {
            if($shiftTimeID == $row['IDNUM'])
                echo '<option value="'.$row['IDNUM'].'" SELECTED>'.$row['NAME'].' ('.$row['BEG_TIME'].'-'.$row['END_TIME'].')</option>';
            else
                echo '<option value="'.$row['IDNUM'].'">'.$row['NAME'].' ('.$row['BEG_TIME'].'-'.$row['END_TIME'].')</option>';

        }

        echo '</select>';
    }
    
    return $shiftTimeID;
}
function hrApproveLeaveRequest($config, $refNo, $reason){
    $mysqli = $config->mysqli;
    $refNo = $mysqli->real_escape_string($refNo);
    $approveQuery= "UPDATE REQUEST SET `TSTAMP` = NOW( ) ,
            `HRAPP_IS` = '1',
            `HRAPP_ID` = '".$config->mysqli->real_escape_string($_SESSION['userIDnum'])."',
            `HRAPP_IP` = INET_ATON('".$config->mysqli->real_escape_string($_SERVER['REMOTE_ADDR'])."'),
            `HR_NOTES` = '".$config->mysqli->real_escape_string($reason)."',
            `HRAPP_TIME` = NOW( ) WHERE `REQUEST`.`REFER` =".$config->mysqli->real_escape_string($refNo);
    $approveResult = $mysqli->query($approveQuery);
    $logMsg = 'Approved Time Request with Ref# '.$refNo;
    addLog($config, $logMsg);
    if(!SQLerrorCatch($mysqli, $approveResult, $approveQuery,$debug=false)){
            echo '<h6>HR Approval for Reference '.$refNo."</h6>";
    }
    
}
function sendRequestToPending($config, $refNo, $hrNotes=''){
    if(!empty($hrNotes))
        $updateNotes = "`HR_NOTES` = '".$config->mysqli->real_escape_string($hrNotes)."',";
    $myq = $myq="UPDATE REQUEST 
        SET STATUS='PENDING',
        `HRAPP_IS` = '0',
        ".$hrNotes."
        APPROVEDBY=''
        WHERE REFER=".$config->mysqli->real_escape_string($refNo);
    $result = $config->mysqli->query($myq);
    SQLerrorCatch($config->mysqli, $result, $myq, $debug=false); 
    addLog($config, 'Ref# '.$refNo.' status was changed to pending');
}


?>
