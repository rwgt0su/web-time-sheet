<?php

/*
 * Functions related to the gain/use time sub-system
 */
?>

<?php
function displayLeaveForm(){
  
   
if (isset($_POST['submit'])) {
    $mysqli = connectToSQL();

    $ID = $mysqli->real_escape_string(strtoupper($_POST['ID']));
    $usedate = new DateTime($mysqli->real_escape_string($_POST['usedate']));
    
    if(isset($_POST['thrudate'])) {
        $thrudate = new DateTime($mysqli->real_escape_string($_POST['thrudate']));

        $daysOffInterval = $usedate->diff($thrudate); //number days in given range
        $daysOff = $daysOffInterval->format("%d");
    }
    else
        $daysOff = 0;    
    
    $shiftLength = isset($_POST['shift']) ? $_POST['shift'] : '';
    
    $beg = new DateTime($mysqli->real_escape_string($_POST['beg']));
    //setting end to beginning so I can add a shift to it if need be
    $end = new DateTime($mysqli->real_escape_string($_POST['beg']));
    
    if(empty($shiftLength)) //not using a shift length so take the entered time
        $end = new DateTime($mysqli->real_escape_string($_POST['end']));
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
    
    $type = $mysqli->real_escape_string($_POST['type']);
    $comment = $mysqli->real_escape_string($_POST['comment']);
    $calloff = isset($_POST['calloff']) ? $_POST['calloff'] : 'NO';
    $subtype = $mysqli->real_escape_string($_POST['subtype']);
    $auditid = strtoupper($_SESSION['userName']);

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
    }
} //end of 'is submit pressed?'

$mysqli = connectToSQL();
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
            echo "</select>"; 
   
            if ( $_SESSION['admin'] == 0) //if normal user, allow only their own user name
                echo "<p>User ID: ".$_SESSION['userName']."<input type='hidden' name='ID' value='".$_SESSION['userName']."'></p>";
            else { //allow any user to be picked for a calloff entry
                echo "User: ";
                dropDownMenu($mysqli, 'FULLNAME', 'EMPLOYEE', $_SESSION['userName'], 'ID');
            }
            ?>
            <p>Date of use/accumulation: <?php displayDateSelect('usedate','date_1'); ?>
                 Through date (optional): <?php displayDateSelect('thrudate','date_2'); ?></p>
            <p>Start time: <input type="text" name="beg">
            <?php 

            if($type == 'PR') {
                    echo "<input type='radio' name='shift' value='8'>8 hour shift";
                    echo "<input type='radio' name='shift' value='12'>12 hour shift";
                    echo "</br>(Personal time must be used for an entire shift.)";
            }
            else {
                ?> End time: <input type="text" name="end"></p> <?php
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
                        T.DESCR 'Type', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T
                    WHERE ID='" . $_SESSION['userName'] .
                    "' AND R.TIMETYPEID=T.TIMETYPEID
                    AND USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."' 
                    ORDER BY REFER";
            
            break;

        case 25: //supervisor, list by division
            $myq = "SELECT DISTINCT REFER 'RefNo', R.ID 'Employee', DATE_FORMAT(REQDATE,'%d %b %Y %H%i') 'Requested', 
                        DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                    WHERE R.TIMETYPEID=T.TIMETYPEID                   
                    AND USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."' 
                    AND E.DIVISIONID IN
                        (SELECT DIVISIONID 
                        FROM EMPLOYEE
                        WHERE ID='" . $_SESSION['userName'] . "')
                    ORDER BY REFER";
            break;
        case 50: //HR
            $myq = "SELECT REFER 'RefNo', DATE_FORMAT(REQDATE,'%d %b %Y %H%i') 'Requested', 
                        DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
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
if (!$result) 
    throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    
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
        
        $myq = "SELECT DISTINCT REFER 'RefNo', RADIO 'Radio', R.ID 'Employee', REQDATE 'Requested', USEDATE 'Used', BEGTIME 'Start',
                        ENDTIME 'End', HOURS 'Hrs',
                        T.DESCR 'Type', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                    WHERE R.TIMETYPEID=T.TIMETYPEID
                    AND   R.ID=E.ID
                    AND STATUS='PENDING'
                    AND E.DIVISIONID IN
                        (SELECT DIVISIONID 
                        FROM EMPLOYEE
                        WHERE ID='" . $_SESSION['userName'] . "')
                    ORDER BY RADIO DESC, REFER";
        echo $myq; //DEBUG

        $result = $mysqli->query($myq);
        if (!$result) 
            throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
       
        //build table
        resultTable($mysqli, $result);
        ?>
        
        <hr>
        <table>
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" name="approveBtn">
            <tr><th>Ref #</th><th>Approve?</th><th>Reason</th></tr>
            <?php
            $refs = array();
            $result->data_seek(0);
            for ($i = 0; $assoc = $result->fetch_assoc(); $i++) {
                $refs[$i] = $assoc['RefNo'];
                echo "<tr><td>$refs[$i]</td>
                    <td><input type='radio' name='approve$i' value='APPROVED' /> Approved 
                        <input type='radio' name='approve$i' value='DENIED'> Denied</td>
                        <td><input type='text' name='reason$i' size='50'/></td>";
            }
            ?>
           </table> <p><input type="submit" name="approveBtn" value="Approve"></p>
            </form>
        

        <?php 
        if (isset($_POST['approveBtn'])) {
            for ($j=0; $i > $j; $j++) {
                $approve = 'approve' . $j;
                $reason = 'reason' . $j;
                if (isset($_POST["$approve"])) {
                    $approveQuery="UPDATE REQUEST 
                                    SET STATUS='".$_POST["$approve"]."',
                                        REASON='".$mysqli->real_escape_string($_POST["$reason"])."',
                                        APPROVEDBY='".$_SESSION['userName']."' 
                                    WHERE REFER='$refs[$j]'";
                    echo $approveQuery; //DEBUG
                    $approveResult = $mysqli->query($approveQuery);
                    if (!$approveResult) 
                        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
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
                        T.DESCR 'Type', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                    WHERE R.TIMETYPEID=T.TIMETYPEID 
                    AND E.ID=R.ID
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
?>
