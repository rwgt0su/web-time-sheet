<?php

/*
 * Insert a new record in REQUEST table from 
 * user-entered form data
 */
require_once 'bin/common.php';
$mysqli = connectToSQL();

$ID = $mysqli->real_escape_string(strtoupper($_POST['ID']));
$usedate = new DateTime($mysqli->real_escape_string($_POST['usedate']));
$usedate = $usedate->format("Y-m-d"); //format user's date properly
$hours = $mysqli->real_escape_string($_POST['hours']);
$type = $mysqli->real_escape_string($_POST['type']);
$comment = $mysqli->real_escape_string($_POST['comment']);
$reqdate = $mysqli->real_escape_string(date("Y-m-d")); //current date in SQL date format YYYY-MM-DD
$auditid = strtoupper($_SESSION['userName']);

//query to insert the record

$myq="INSERT INTO REQUEST (ID, USEDATE, HOURS, TIMETYPEID, NOTE, APPROVE, REQDATE, AUDITID, IP)
        VALUES ('$ID', '$usedate', '$hours', '$type', 
                '$comment', '0', '$reqdate','$auditid',INET_ATON('${_SERVER['REMOTE_ADDR']}'))";
echo $myq; //DEBUG
$result = $mysqli->query($myq);

//show SQL error msg if query failed
if (!$result) {
throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
echo 'Request not accepted.';
}
else {
    echo 'Request accepted. The reference number for this request is <b>' 
        . $mysqli->insert_id . '</b>.';
}
    
?>
