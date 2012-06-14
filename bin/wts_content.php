<?php

function displayContent($wts_content, $config){
    if($wts_content->isWelcome()){
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
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
    /*if(false){}
    else{
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }*/
    if($wts_content->isLogout()){
        logoutUser();
        echo '<meta http-equiv="refresh" content="3;url=/"/>';
        echo '<div class="post">You have logged out<div class="clear"></div></div><div class="divider"></div>';
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
    $hours = $mysqli->real_escape_string($_POST['hours']);
    $type = $mysqli->real_escape_string($_POST['type']);
    $comment = $mysqli->real_escape_string($_POST['comment']);
    $reqdate = $mysqli->real_escape_string(date("Y-m-d")); //current date in SQL date format YYYY-MM-DD
    $auditid = strtoupper($_SESSION['userName']);

    //query to insert the record

    $myq="INSERT INTO REQUEST (ID, USEDATE, HOURS, TIMETYPEID, NOTE, APPROVE, REQDATE, AUDITID, IP)
            VALUES ('$ID', '$usedate', '$hours', '$type', 
                    '$comment', '0', '$reqdate','$auditid',INET_ATON('${_SERVER['REMOTE_ADDR']}'))";
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
<form name="leave" method="post" action="/?leave=true">
    
	<h2>Employee Leave Request</h2>
        <table id="leave" border="1">
        <tr><th>User ID</th><th>Date of use/accumulation</th><th>Number of hours</th><th>Time type</th><th>Comment</th></tr>
	<tr><td><input type="text" name="ID" value="<?php echo $_SESSION['userName']; ?>"></td>
        <td><input type="text" name="usedate" value="<?php echo date("Y-m-d"); ?>"></td>
        <td><input type="text" name="hours"></td>
        <td> <select name="type">
        <?php
            //build a drop-down from query result
            $result->data_seek(0);  
            while ($row = $result->fetch_assoc()) 
               {
                 echo '<option value="' . $row['TIMETYPEID'] . '">' . $row['DESCR'] . '</option>';
               }
        ?>
               </select></td>
        <td><input type="text" name="comment"></td></tr>

	<p><input type="submit" name="submit" value="Submit"></p>
</form>
</body></html>
<?php //} ?>

 <?php } ?>

<?php
function displayPendingRequests(){   
/*
 * A report of recent leave requests with
 * different views according to admin level
 */

$mysqli = connectToSQL();
$admin = $_SESSION['admin'];

    switch($admin) { //switch to show different users different reports
        case 0: //normal user, list only user's own reqs

        $myq = "SELECT REFER 'Ref. No.', REQDATE 'Requested', USEDATE 'Used', HOURS 'Hrs',
                        T.DESCR 'Type', NOTE 'Comment', IF(APPROVE=1,'Yes','No') 'Approved?', 
                        AUDITID 'Last Mod.', REASON 'Reason' 
                    FROM REQUEST R, TIMETYPE T
                    WHERE ID='" . $_SESSION['userName'] .
                    "' AND R.TIMETYPEID=T.TIMETYPEID";
            
            break;

        case 25: //supervisor, list division
            //custom query goes here
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
resultTable($mysqli, $result, '/?pending=true');

//}//end else
if (isset($_POST['saveBtn'])) {
    for ($i=0; $i < $numOfCols; $i++)
        $newValue[$i] = $_POST["$i"];

$updateQuery="UPDATE REQUEST 
            SET TIMETYPEID='$newValue[2]', USEDATE='$newValue[4]', HOURS='$newValue[5]',
            NOTE='$newValue[6]', APPROVE='$newValue[7]', REASON='$newValue[8]',
            AUDITID='${_SESSION['userName']}'
            WHERE REFER='$newValue[0]'";
$result = $mysqli->query($updateQuery);
if (!$result) 
throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
}
?>

<form action="/?pending=true" method="post" name="editBtn">
    <p><input type="submit" name="editBtn" value="Edit"></p></form>

<?php } ?>