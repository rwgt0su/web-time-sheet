<?php

/*
 * Insert a new record in REQUEST table from 
 * user-entered form data
 */
require_once 'bin/common.php';

$ID = strtoupper($_POST['ID']);
$usedate = $_POST['usedate'];
$hours = $_POST['hours'];
$type = $_POST['type'];
$comment = $_POST['comment'];
$reqdate = date("YY-MM-DD"); //current date in SQL date format YYYY-MM-DD

//query to insert the record
$mysqli = connectToSQL();
$myq="INSERT INTO REQUEST VALUES (ID, USEDATE, HOURS, TIMETYPEID, NOTE, APPROVE, REQDATE)
        VALUES ('$ID', '$usedate', '$hours', '$type', '$comment', '0', $reqdate)";
$myq = $mysqli->real_escape_string($myq);
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
