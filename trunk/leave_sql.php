<?php

/*
 * Insert a new record in REQUEST table from 
 * user-entered form data
 */
require_once 'bin/common.php';
$mysqli = connectToSQL();

$ID = $mysqli->real_escape_string(strtoupper($_POST['ID']));
$usedate = $mysqli->real_escape_string($_POST['usedate']);
$hours = $mysqli->real_escape_string($_POST['hours']);
$type = $mysqli->real_escape_string($_POST['type']);
$comment = $mysqli->real_escape_string($_POST['comment']);
$reqdate = $mysqli->real_escape_string(date("Y-m-d")); //current date in SQL date format YYYY-MM-DD

//query to insert the record

$myq="INSERT INTO REQUEST (ID, USEDATE, HOURS, TIMETYPEID, NOTE, APPROVE, REQDATE)
        VALUES ('$ID', '$usedate', '$hours', '$type', '$comment', '0', '$reqdate')";
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
