<?php

/*
 * A form to collect data from end user 
 * to begin the leave request process
 * 
 */
require_once 'bin/common.php';
?>


    
<?php    
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
<form name="leave" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?submit=true">
	<h1>Employee Leave Request</h1>
        
	<p>User ID:<input type="text" name="ID" value="<?php echo $_SESSION['userName']; ?>"></p>
        <p>Date of use/accumulation<input type="text" name="usedate" value="YYYY-MM-DD"></p>
        <p>Number of hours:<input type="text" name="hours"></p>
        <p>Time type: <select name="type">
        <?php
            //build a drop-down from query result
            $result->data_seek(0);  
            while ($row = $result->fetch_assoc()) 
               {
                 echo '<option value="' . $row['TIMETYPEID'] . '">' . $row['DESCR'] . '</option>';
               }
        ?>
               </select></p>
        <p>Comment:<input type="text" name="comment"></p>

	<p><input type="submit" name="submit" value="Submit"></p>
</form>
</body></html>
<? //} ?>
