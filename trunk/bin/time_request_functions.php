<?php

/*
 * Functions related to the gain/use time sub-system
 */
?>

<?php
function displayLeaveForm($config){
  
$mysqli = $config->mysqli;
$postUseDate = isset($_POST['usedate']) ? $_POST['usedate'] : false;
    If(!$postUseDate)
        $isDateUse = false;
    Else
        $isDateUse = true;
if (isset($_POST['submit'])) {
    //Get all passed variables
    $postID = isset($_POST['ID']) ? $_POST['ID'] : false;
    
    $postThruDate = isset($_POST['thrudate']) ? $_POST['thrudate'] : false;
    $shiftLength = isset($_POST['shift']) ? $_POST['shift'] : '';
    
    $postBeg1 = isset($_POST['beg1']) ? $_POST['beg1'] : false;
    $postBeg2 = isset($_POST['beg2']) ? $_POST['beg2'] : false;
    if(!empty($postBeg1) && !empty($postBeg2))
        $postBegin = $postBeg1.$postBeg2;
    else
        $postBegin = false;
    
    $postEnd1 = isset($_POST['end1']) ? $_POST['end1'] : false;
    $postEnd2 = isset($_POST['end2']) ? $_POST['end2'] : false;
    if(!empty($postEnd1) && !empty($postEnd2))
        $postEnding = $postEnd1.$postEnd2;
    else
        $postEnding = false;
    
    $type = isset($_POST['type']) ? $mysqli->real_escape_string($_POST['type']) : false;
    $comment = isset($_POST['comment']) ? $mysqli->real_escape_string($_POST['comment']) : false;
    $calloff = isset($_POST['calloff']) ? $_POST['calloff'] : 'NO';
    $subtype = isset($_POST['subtype']) ? $mysqli->real_escape_string($_POST['subtype']) : false;
    $auditid = isset($_POST['userName']) ? strtoupper($_SESSION['userName']) : false;

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
            for($i=0; $i <= $daysOff; $i++){
                $myq="INSERT INTO REQUEST (ID, USEDATE, BEGTIME, ENDTIME, HOURS, TIMETYPEID, SUBTYPE, NOTE, STATUS, REQDATE, AUDITID, IP, CALLOFF)
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
                    echo '<h3>Request accepted. The reference number for this request is <b>' 
                        . $mysqli->insert_id . '</b></h3>.';
                }
            }//end for loop
        }//end blank start or end time
        else
            echo '<font color="red" >Must provide a valid Start and End time!</font><br /><br />';
    }//end blank use date submission verification
    else{
        echo '<font color="red" >Must provide a valid Date!</font><br /><br />';
    }
} //end of 'is submit pressed?'

?>
<h2>Employee Request</h2>
      
 <form name="leave" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
     <?php  
        $type  = isset($_GET['type']) ? $_GET['type'] : ''; 
        $myq = "SELECT DESCR FROM TIMETYPE WHERE TIMETYPEID='".$type."'";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $typeDescr = $result->fetch_assoc();
        
        if (!empty($type)) { //$_GET['type'] is set
            //hidden field with type set
            echo "<p><h3>Type of Request: </h3>" . $typeDescr['DESCR'] . "</p>";
            echo "<input type='hidden' name='type' value='".$type."'>";
            //subtype choice
            echo "Subtype: ";
            $myq = "SELECT NAME FROM SUBTYPE";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            ?> <select name="subtype"> <?php
            while($row = $result->fetch_assoc()) {
                if ($row['NAME'] == 'NONE') 
                    echo '<option value="NONE" selected="selected">NONE</option>';
                else
                    echo '<option value="' . $row["NAME"] . '">'. $row["NAME"] . '</option>';
            }
            echo "</select> </br>"; 
   
            if ( $_SESSION['admin'] == 0) //if normal user, allow only their own user name
                echo "<p>User ID: ".$_SESSION['userName']."<input type='hidden' name='ID' value='".$_SESSION['userIDnum']."'></p>";
            else { //allow any user to be picked for a calloff entry
                echo "User: ";
                dropDownMenu($mysqli, 'FULLNAME', 'EMPLOYEE', $_SESSION['userName'], 'ID');
            }
            ?>
            <p>Date of use/accumulation: <?php displayDateSelect('usedate','date_1', $postUseDate , !$isDateUse); ?>
                 Through date (optional): <?php displayDateSelect('thrudate','date_2'); ?></p>
            <p>Start time: <?php showTimeSelector("beg"); ?>
            <?php 

            if($type == 'PR') {
                    echo "<input type='radio' name='shift' value='8'>8 hour shift";
                    echo "<input type='radio' name='shift' value='12'>12 hour shift";
                    echo "</br>(Personal time must be used for an entire shift.)";
            }
            else {
                ?> End time: <?php showTimeSelector("end"); ?></p> <?php
            }

            ?> 


            </br>
            <p>Comment: <input type="text" name="comment"></p>
            <p><input type="checkbox" name='calloff' value="YES">Check if calling in sick.</p>

            <p><input type="submit" name="submit" value="Submit"></p>  

    </form> 


    <?php
        }
        else {
             //intitial choice of type
            //not hidden
            echo "<p><h3>Type of Request: </h3>";
            dropDownMenu($mysqli, 'DESCR', 'TIMETYPE', FALSE, 'type');
            echo "</p>";
        }
} // end displayLeaveForm()
?>

<?php
function displaySubmittedRequests(){   
/*
 * A report of recent leave requests with
 * different views according to admin level
 */

$mysqli = connectToSQL();
$admin = $_SESSION['admin'];

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
    displayDateSelect('start', 'date_1');   
    echo "End";
    displayDateSelect('end', 'date_2');
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

    switch($admin) { //switch to show different users different reports
        case 0: //normal user, list only user's own reqs

        $myq = "SELECT REFER 'RefNo', DATE_FORMAT(REQDATE,'%d %b %Y %H%i') 'Requested', 
                        DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                    WHERE R.IDNUM='" . $_SESSION['userIDnum'] .
                    "' AND R.TIMETYPEID=T.TIMETYPEID AND R.IDNUM=E.IDNUM 
                    AND USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."' 
                    ORDER BY REFER";
            
            break;

        case 25: //supervisor, list by division
            $myq = "SELECT DISTINCT REFER 'RefNo', CONCAT_WS(', ',LNAME,FNAME) 'Employee', DATE_FORMAT(REQDATE,'%d %b %Y %H%i') 'Requested', 
                        DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                    WHERE R.TIMETYPEID=T.TIMETYPEID AND R.IDNUM=E.IDNUM               
                    AND USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."' 
                    AND E.DIVISIONID IN
                        (SELECT DIVISIONID 
                        FROM EMPLOYEE E
                        WHERE E.IDNUM='" . $_SESSION['userIDnum'] . "')
                    ORDER BY REFER";
            break;
        case 50: //HR
            $myq = "SELECT REFER 'RefNo', DATE_FORMAT(REQDATE,'%d %b %Y %H%i') 'Requested', 
                        DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T
                    WHERE R.TIMETYPEID=T.TIMETYPEID
                    AND USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."' 
                    ORDER BY REFER";
            break;
        case 99: //Sheriff
            //custom query goes here
            break;
        case 100: //full admin, complete raw dump
        
            $myq = "SELECT *
                    FROM REQUEST
                    WHERE USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."'"; 
            break;
} //end switch
    
$result = $mysqli->query($myq);
SQLerrorCatch($mysqli, $result);
    
//build table
resultTable($mysqli, $result);
//show a print button. printed look defined by print.css
echo '<a href="javascript:window.print()">Print</a>';
} //end displaySubmittedRequests()
?>

<?php
function displayLeaveApproval(){   
    /*
    * Form used to approve leave
    * 
    */
    $admin = $_SESSION['admin'];
    if($admin >= 25) { 

        $mysqli = connectToSQL();
        
        $myq = "SELECT DISTINCT REFER 'RefNo', RADIO 'Radio', CONCAT_WS(', ',LNAME,FNAME) 'Employee', 
                        DATE_FORMAT(REQDATE,'%d %b %Y %H%i') 'Requested', 
                        DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', SUBTYPE 'Subtype', NOTE 'Comment', STATUS 'Status'                         
                    FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                    WHERE R.TIMETYPEID=T.TIMETYPEID
                    AND   R.IDNUM=E.IDNUM
                    AND STATUS='PENDING'
                    AND E.DIVISIONID IN
                        (SELECT DIVISIONID 
                        FROM EMPLOYEE
                        WHERE IDNUM='" . $_SESSION['userIDnum'] . "')
                    ORDER BY RADIO DESC, REFER";
        //echo $myq; //DEBUG

        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
       
        //build table
        //resultTable($mysqli, $result);
        ?>
        
        <hr>
        <table>
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" name="approveBtn">
            <!-- <tr><th>Ref #</th><th>Approve?</th><th>Reason</th></tr> -->
            <tr>
            <?php    
            $result->data_seek(0);
            while($finfo = $result->fetch_field()){
                echo "<th>".$finfo->name."</th>";
                } ?>
            </tr>
            <?php
            $refs = array();
            $result->data_seek(0);
            /*for ($i = 0; $assoc = $result->fetch_assoc(); $i++) {
                $refs[$i] = $assoc['RefNo'];
                echo "<tr><td>$refs[$i]</td>
                    <td><input type='radio' name='approve$i' value='APPROVED' /> Approved 
                        <input type='radio' name='approve$i' value='DENIED'> Denied</td>
                        <td><input type='text' name='reason$i' size='50'/></td>";
            }*/
            //new & improved
            $rowCount = 0;
            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                echo "<tr>";
                $refs[$rowCount] = $row[0]; //save ref # in an array
                for ($i = 0; $i < $mysqli->field_count; $i++) {
                    echo "<td style='white-space: nowrap'>$row[$i]</td>";                                      
                }
                echo "</tr>";
                echo "<td style='white-space: nowrap'></td><td><input type='radio' name='approve$rowCount' value='APPROVED' /> Approved</td> 
                        <td style='white-space: nowrap'><input type='radio' name='approve$rowCount' value='DENIED'> Denied</td>
                        <td style='white-space: nowrap' colspan='8'>Reason:<input type='text' name='reason$rowCount' size='50'/></td>";
                $rowCount++;
            }
            ?>
           </table> <p><input type="submit" name="approveBtn" value="Save"></p>
            </form>
        

        <?php 
        if (isset($_POST['approveBtn'])) {
            for ($j=0; $rowCount > $j; $j++) {
                $approve = 'approve' . $j;
                $reason = 'reason' . $j;
                if (isset($_POST["$approve"])) {
                    $approveQuery="UPDATE REQUEST 
                                    SET STATUS='".$_POST["$approve"]."',
                                        REASON='".$mysqli->real_escape_string($_POST["$reason"])."',
                                        APPROVEDBY='".$_SESSION['userIDnum']."' 
                                    WHERE REFER='$refs[$j]'";
                    echo $approveQuery; //DEBUG
                    $approveResult = $mysqli->query($approveQuery);
                    SQLerrorCatch($mysqli, $approveResult);
                }
            }
        }

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
   
    if( isValidUser() && (isset($_POST['lname']) || isset($_POST['editBtn'])) ) {
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
        $myq = "SELECT DISTINCT REFER 'RefNo', R.ID 'Employee', REQDATE 'Requested', USEDATE 'Used', BEGTIME 'Start',
                        ENDTIME 'End', HOURS 'Hrs',
                        T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                    WHERE R.TIMETYPEID=T.TIMETYPEID 
                    AND E.IDNUM=R.IDNUM
                    AND USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."' 
                    AND LNAME LIKE '%".$lname."%'";
        //popUpMessage($myq); //DEBUG
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        resultTable($mysqli, $result);
        
    }
    else {
        ?>
        <form name="lookup" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<h1>Lookup Requests by Employee</h1>
        
	<p>Search by last name:<input type="text" name="lname"></p>
        <p>Date range: From <?php   displayDateSelect('start','date_1'); ?>
            to <?php   displayDateSelect('end','date_2'); ?></p>

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
   
    $myq = "SELECT DIVISIONID 'Div', MUNIS, CONCAT_WS(', ',LNAME,FNAME) 'Name', R.ID, T.DESCR 'Type', CAST(SUM(HOURS) as DECIMAL(5,2)) 'Total (hrs)'
            FROM REQUEST R, EMPLOYEE E, TIMETYPE T
            WHERE R.IDNUM=E.IDNUM AND T.TIMETYPEID = R.TIMETYPEID
            AND USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."'
            AND STATUS='APPROVED'
            GROUP BY E.DIVISIONID, R.ID, R.TIMETYPEID
            ORDER BY E.DIVISIONID, LNAME";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    resultTable($mysqli, $result);
    }
    //show a print button. printed look defined by print.css
    echo '<a href="javascript:window.print()">Print</a>';
    
}

function MUNISreport($config) {
    //a table looking similar to MUNIS entry for ease of use
    if(isset($_POST['ppselect'])) {
        $mysqli = $config->mysqli;
        $myq = "SELECT * FROM PAYPERIOD WHERE ID='".$_POST['ppselect']."'";
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
                    AND USEDATE='".$date."'"
                    ."AND LNAME='CHACHKO'";
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
?>
