<?php

/*
 * A report of recent leave requests with
 * different views according to admin level
 */

require_once 'bin/common.php';

$mysqli = connectToSQL();
$myq = "SELECT REFER 'Ref. No.', REQDATE 'Requested', USEDATE 'Used', HOURS 'Hrs',
            T.DESCR 'Type', NOTE 'Comment', IF(APPROVE=1,'Yes','No') 'Approved?', 
            AUDITID 'Last Mod.', REASON 'Reason' 
        FROM REQUEST R, TIMETYPE T
        WHERE ID=" . $_SESSION['userName'] .
        "AND R.TIMETYPEID=T.TIMETYPEID";
$result = $mysqli->query($myq);
if (!$result) 
throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");

//build table
resultTable($mysqli, $result);



?>

