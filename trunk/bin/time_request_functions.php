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
    $usedate = $usedate->format("Y-m-d"); //format user's date properly for SQL
    
    $beg = new DateTime($mysqli->real_escape_string($_POST['beg']));
    $end = new DateTime($mysqli->real_escape_string($_POST['end']));
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
    $auditid = strtoupper($_SESSION['userName']);
    
    if ($type == 'PR' && !($hours == 8 || $hours == 12) ) {
        exit ("Error: Personal time can only be used for an entire shift of 8 or 12 hours.");
    }
        
    
    //query to insert the record
    $myq="INSERT INTO REQUEST (ID, USEDATE, BEGTIME, ENDTIME, HOURS, TIMETYPEID, NOTE, STATUS, REQDATE, AUDITID, IP)
            VALUES ('$ID', '$usedate', '$beg', '$end', '$hours', '$type', 
                    '$comment', 'PENDING', NOW(),'$auditid',INET_ATON('${_SERVER['REMOTE_ADDR']}'))";
    //echo $myq; //DEBUG
    $result = $mysqli->query($myq);

    //show SQL error msg if query failed
    if (!$result) {
    throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    echo 'Request not accepted.';
    }
    else {
        echo '<h3>Request accepted. The reference number for this request is <b>' 
            . $mysqli->insert_id . '</b></h3>.';
            }
} //end of 'is submit pressed?'

$mysqli = connectToSQL();
?>
<html><body>
        <h2>Employee Request</h2>
 <!-- this worked until I moved things around. Now I have no idea why it stopped working! -->       
<form name="leave" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <p><h3>Type of Request: </h3> <?php dropDownMenu($mysqli, 'DESCR', 'TIMETYPE', FALSE, 'type'); ?></p>
        <p>User ID: <?php echo $_SESSION['userName']; ?> <input type="hidden" name="ID" value="<?php echo $_SESSION['userName']; ?>"></p>
        <p>Date of use/accumulation: <?php displayDateSelect('usedate'); ?></p>
        <?php 
        $type  = isset($_GET['type']) ? $_GET['type'] : '';
        if($type == 'PR') {
                echo "<input type='radio' name='shift' value='8'>8 hour shift";
                echo "<input type='radio' name='shift' value='12'>12 hour shift";
        }
        ?> 
        <p>Start time: <input type="text" name="beg">
        End time: <input type="text" name="end"></p>
        
        <p>Comment: <input type="text" name="comment"></p>
	
                
        <?php /*
            //build a drop-down from query result
            $result->data_seek(0);  
            while ($row = $result->fetch_assoc()) 
               {
                 echo '<option value="' . $row['TIMETYPEID'] . '">' . $row['DESCR'] . '</option>';
               }
       */ ?>
             
        
        
       
	<p><input type="submit" name="submit" value="Submit"></p>
        
</form>
</body></html>
<?php
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
    $uri = $_SERVER['REQUEST_URI']."&ppOffset="; //1st set

$startDate = new DateTime("{$ppArray['PPBEG']}");
if($ppOffset < 0)
    //backward in time by $ppOffset number of periods
    $startDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
else
    //forward in time by $ppOffset number of periods
    $startDate->add(new DateInterval("P".($ppOffset*14)."D"));
//set the result in SQL DATE format
$startDate = $startDate->format('Y-m-d');
//echo " START DATE = ".$startDate; //DEBUG

$endDate = new DateTime("{$ppArray['PPEND']}");
if($ppOffset < 0)
    //backward in time by $ppOffset number of periods
    $endDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
else
    //forward in time by $ppOffset number of periods
    $endDate->add(new DateInterval("P".($ppOffset*14)."D"));
//set the result in SQL DATE format
$endDate = $endDate->format('Y-m-d');
//echo " END DATE = ".$endDate; //DEBUG
?>
<p><div style="float:left"><a href="<?php echo $uri.($ppOffset-1); ?>">Previous</a></div>  
   <div style="float:right"><a href="<?php echo $uri.($ppOffset+1); ?>">Next</a></div></p>
<h3><center>Gain/Use Requests for pay period <?php echo $startDate; ?> through <?php echo $endDate; ?>.</center></h3>

<?php
    switch($admin) { //switch to show different users different reports
        case 0: //normal user, list only user's own reqs

        $myq = "SELECT REFER 'RefNo', REQDATE 'Requested', USEDATE 'Used', BEGTIME 'Start',
                        ENDTIME 'End', HOURS 'Hrs',
                        T.DESCR 'Type', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T
                    WHERE ID='" . $_SESSION['userName'] .
                    "' AND R.TIMETYPEID=T.TIMETYPEID
                    AND USEDATE BETWEEN '". $startDate."' AND '".$endDate."' 
                    ORDER BY REFER";
            
            break;

        case 25: //supervisor, list by division
            $myq = "SELECT DISTINCT REFER 'RefNo', R.ID 'Employee', REQDATE 'Requested', USEDATE 'Used', BEGTIME 'Start',
                        ENDTIME 'End', HOURS 'Hrs',
                        T.DESCR 'Type', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                    WHERE R.TIMETYPEID=T.TIMETYPEID                   
                    AND USEDATE BETWEEN '". $startDate."' AND '".$endDate."' 
                    AND E.DIVISIONID IN
                        (SELECT DIVISIONID 
                        FROM EMPLOYEE
                        WHERE ID='" . $_SESSION['userName'] . "')
                    ORDER BY REFER";
            break;
        case 50: //HR
            //custom query goes here
        case 99: //Sheriff
            //custom query goes here
        case 100: //full admin, complete raw dump
        
            $myq = "SELECT *
                    FROM REQUEST
                    WHERE USEDATE BETWEEN '". $startDate."' AND '".$endDate."'"; 
            break;
} //end switch
    
$result = $mysqli->query($myq);
if (!$result) 
    throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    
//build table
resultTable($mysqli, $result);

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
        
        $myq = "SELECT DISTINCT REFER 'RefNo', R.ID 'Employee', REQDATE 'Requested', USEDATE 'Used', BEGTIME 'Start',
                        ENDTIME 'End', HOURS 'Hrs',
                        T.DESCR 'Type', NOTE 'Comment', STATUS 'Status', 
                        APPROVEDBY 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T, EMPLOYEE E
                    WHERE R.TIMETYPEID=T.TIMETYPEID                   
                    AND STATUS='PENDING'
                    AND E.DIVISIONID IN
                        (SELECT DIVISIONID 
                        FROM EMPLOYEE
                        WHERE ID='" . $_SESSION['userName'] . "')
                    ORDER BY REFER";
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
?>
