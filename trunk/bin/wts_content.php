<?php

function displayContent($wts_content, $config){
    if($wts_content->isHome){
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
        displayAnnounce($config);
    }
    if($wts_content->isWelcome()){
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isAnounceAdmin){
        ?>
        <div class="post"><?php displayAdminAnnounce($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isDBTest()){
        ?>
        <div class="post"><?php displayDBTest(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isLeaveForm){
        ?>
        <div class="post"><?php displayLeaveForm(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isPending){
        ?>
        <div class="post"><?php displayPendingRequests(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isLeaveApproval){
        ?>
        <div class="post"><?php displayLeaveApproval(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isInsertUser){
        ?>
        <div class="post"><?php displayInsertUser(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isUserMenu){
        ?>
        <div class="post"><?php displayUserMenu($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isLogout()){
        logoutUser("You have logged out");
    }
    if($wts_content->isSearching){
        ?>
        <div class="post"><?php searchPage(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isUpdateProfile){
        ?>
        <div class="post"><?php displayUpdateProfile(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
         
}

function displayWelcome($config){
    ?>
    <div class="thumbnail"><img src="/style/WellingtonBadge.gif" alt="" /></div>
    <h3><?php echo $config->getTitle(); ?></h3> 
    <p>Welcome to the Mahoning County Sheriff's Office Web Portal</p>
    <?php
}

function displayDBTest(){
    //establish connetcion to DB
    $mysqli = new mysqli("localhost", "web", "10paper", "test");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    echo $mysqli->host_info . "\n";

    $myq = "SELECT * FROM EMP;";
    $result = $mysqli->query($myq);
    ?>
    <script language="JavaScript" type="text/javascript">
function addRowToTable()
{
  var tbl = document.getElementById('tblSample');
  var lastRow = tbl.rows.length;
  // if there's no header row in the table, then iteration = lastRow + 1
  var iteration = lastRow;
  var row = tbl.insertRow(lastRow);
  
  // left cell
  var cellLeft = row.insertCell(0);
  var textNode = document.createTextNode(iteration);
  cellLeft.appendChild(textNode);
  
  // right cell
  var cellRight = row.insertCell(1);
  var newCode1 = "Event: <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp&nbsp&nbsp<input type=\"text\"size=\"35\"name=\"event" + iteration + "\" value=\"\" />";
  var newCode0 = "<br />Start Time: <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp&nbsp&nbsp<input type=\"text\"size=\"35\"name=\"time" + iteration + "\" value=\"\" />";
  var newCode2 = "<br />Description:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<textarea name=\"description" +iteration + "\" cols=80 rows=3></textarea>";
  var newCode3 = "<br />URL: <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<input name=\"url" + iteration + "\" type\"text\" size=\"35\" value=\"\" />";
  cellRight.innerHTML = newCode1 +newCode0 + newCode2 + newCode3;
  
}
function keyPressTest(e, obj)
{
  var validateChkb = document.getElementById('chkValidateOnKeyPress');
  if (validateChkb.checked) {
    var displayObj = document.getElementById('spanOutput');
    var key;
    if(window.event) {
      key = window.event.keyCode; 
    }
    else if(e.which) {
      key = e.which;
    }
    var objId;
    if (obj != null) {
      objId = obj.id;
    } else {
      objId = this.id;
    }
    displayObj.innerHTML = objId + ' : ' + String.fromCharCode(key);
  }
}
function removeRowFromTable()
{
  var r=confirm("Are You Sure You Want To Remove The Previous Event?");
  if (r==true)
    {
      var tbl = document.getElementById('tblSample');
  	  var lastRow = tbl.rows.length;
  	  if (lastRow > 2) tbl.deleteRow(lastRow - 1);
    }
  else
    {
    }

}
function openInNewWindow(frm)
{
  // open a blank window
  var aWindow = window.open('', 'TableAddRowNewWindow',
   'scrollbars=yes,menubar=yes,resizable=yes,toolbar=no,width=400,height=400');
   
  // set the target to the blank window
  frm.target = 'TableAddRowNewWindow';
  
  // submit
  frm.submit();
}
function validateRow(frm)
{
  var chkb = document.getElementById('chkValidate');
  if (chkb.checked) {
    var tbl = document.getElementById('tblSample');
    var lastRow = tbl.rows.length - 1;
    var i;
    for (i=1; i<=lastRow; i++) {
      var aRow = document.getElementById('txtRow' + i);
      if (aRow.value.length <= 0) {
        alert('Row ' + i + ' is empty');
        return;
      }
    }
  }
  openInNewWindow(frm);
}
</script>
    Contents of employee table</br>
    <form id="edit_list" action="/?dbtest=true" method="POST">
    <table border="1" id="tblSample">
    <tr>
    <th>ID</th>
    <th>First</th>
    <th>Last</th>
    </tr>

    <?php
    $result->data_seek(0);  //moves internal pointer to 0, fetch starts here
    while ($row = $result->fetch_assoc()) //fetch assoc array && pointer++
    {
            echo "<tr><td>" . $row['EID'] . "</td><td>" . $row['FNAME'] . "</td><td>" . $row['LNAME'] . "</td></tr>";
    }
    ?>
    <div align="middle"><input type="button" value="Add" onclick="addRowToTable();" />
    </table>
    </form>
    <?php
}

function displayLeaveForm(){
  
   
if (isset($_POST['submit'])) {
    $mysqli = connectToSQL();

    $ID = $mysqli->real_escape_string(strtoupper($_POST['ID']));
    $usedate = new DateTime($mysqli->real_escape_string($_POST['usedate']));
    $usedate = $usedate->format("Y-m-d"); //format user's date properly for SQL
    
    $beg = new DateTime($mysqli->real_escape_string($_POST['beg']));
    $end = new DateTime($mysqli->real_escape_string($_POST['end']));
    //interval calculation in hours
    $endSec = strtotime($end->format("H:i:s"));  
    $begSec = strtotime($beg->format("H:i:s"));
    $hours = ($endSec - $begSec) / 3600;
    //SQL TIME format
    $beg = $beg->format("H:i:s");  
    $end = $end->format("H:i:s");
    
    $type = $mysqli->real_escape_string($_POST['type']);
    $comment = $mysqli->real_escape_string($_POST['comment']);
    $reqdate = $mysqli->real_escape_string(date("Y-m-d")); //current date in SQL date format YYYY-MM-DD
    $auditid = strtoupper($_SESSION['userName']);
    
    if ($type == 'PR' && !($hours == 8 || $hours == 12) ) {
        exit ("Error: Personal time can only be used for an entire shift of 8 or 12 hours.");
    }
        
    
    //query to insert the record
    $myq="INSERT INTO REQUEST (ID, USEDATE, BEGTIME, ENDTIME, HOURS, TIMETYPEID, NOTE, STATUS, REQDATE, AUDITID, IP)
            VALUES ('$ID', '$usedate', '$beg', '$end', '$hours', '$type', 
                    '$comment', 'PENDING', '$reqdate','$auditid',INET_ATON('${_SERVER['REMOTE_ADDR']}'))";
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
}
//else { //submit not pressed
//if (!isset($_POST['submit'])) {
    //query to get currently available types of time 
$mysqli = connectToSQL();
$myq="SELECT TIMETYPEID, DESCR FROM TIMETYPE";
$result = $mysqli->query($myq);

//show SQL error msg if query failed
if (!$result) 
    throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
?>
<html><body>
<form name="leave" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    
	<h2>Employee Leave Request</h2>
        
        <p>User ID: <input type="text" name="ID" value="<?php echo $_SESSION['userName']; ?>"></p>
        <p>Date of use/accumulation: <input type="text" name="usedate" value="<?php echo date("Y-m-d"); ?>"></p>
        <p>Start time: <input type="text" name="beg">
        End time: <input type="text" name="end"></p>
        <p>Time type: <?php dropDownMenu($mysqli, 'DESCR', 'TIMETYPE', FALSE, 'type'); ?></p>
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

 <?php } ?>

<?php
function displayPendingRequests(){   
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

$startDate = new DateTime("${ppArray['PPBEG']}");
if($ppOffset < 0)
    //backward in time by $ppOffset number of periods
    $startDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
else
    //forward in time by $ppOffset number of periods
    $startDate->add(new DateInterval("P".($ppOffset*14)."D"));
//set the result in SQL DATE format
$startDate = $startDate->format('Y-m-d');
//echo " START DATE = ".$startDate; //DEBUG

$endDate = new DateTime("${ppArray['PPEND']}");
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
                    FROM REQUEST";
            break;
} //end switch
    
$result = $mysqli->query($myq);
if (!$result) 
    throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    
$numOfCols = $mysqli->field_count;

//build table
resultTable($mysqli, $result);
/*
if (isset($_POST['saveBtn'])) {
    for ($i=0; $i < $numOfCols; $i++)
        $newValue[$i] = $_POST["$i"]; //this needs re-written for assoc array

switch($admin) {
    case 0:
        $updateQuery="UPDATE REQUEST R, TIMETYPE T
            SET R.TIMETYPEID = T.TIMETYPEID,  USEDATE='$newValue[2]', HOURS='$newValue[3]',
            NOTE='$newValue[5]', AUDITID='${_SESSION['userName']}'
            WHERE REFER='$newValue[0]'
            AND T.DESCR ='$newValue[4]'";
            break;
    case 100:
        $updateQuery="UPDATE REQUEST 
            SET TIMETYPEID='$newValue[2]', USEDATE='$newValue[4]', HOURS='$newValue[5]',
            NOTE='$newValue[6]', APPROVE='$newValue[7]', REASON='$newValue[8]',
            AUDITID='${_SESSION['userName']}'
            WHERE REFER='$newValue[0]'";
        break;
}
$result = $mysqli->query($updateQuery);
if (!$result) 
throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
}*/
?>

<?php } ?>

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
}
?>

<?php
function displayInsertUser(){

    if (isset($_POST['submit'])) {
        $ID=$_POST['ID'];
        $pass1=$_POST['pass1'];
        $pass2=$_POST['pass1'];
        $adminLvl=$_POST['adminLvl'];

        $msg = registerUser($ID,$pass1,$pass2,$adminLvl);
        
        if(empty($msg))
            echo "New user <b>".strtoupper($ID)."</b> added successfully.";
    }
        
?>
        
<form name="insert" method="post" action="/?newuser=true">
        <p>Add a new user:</p>
        <p>Login ID:<input type="text" name="ID"></p>
	<p>Password:<input type="password" name="pass1"></p>
        <p>Re-type password:<input type="password" name="pass2"></p>
        <p>Admin Level:<input type="text" name="adminLvl"></p>
        
        <p><input type="submit" name="submit" value="Submit"></p>
</form>
        
<?php        
}      
?>
