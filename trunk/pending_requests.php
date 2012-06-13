<?php

/*
 * A report of recent leave requests with
 * different views according to admin level
 */

require_once 'bin/common.php';
popUpMessage("HELLO WORLD!");
$admin = $_SESSION['admin'];

switch($admin) {
    case 0: //normal user, list only user's own reqs

        $mysqli = connectToSQL();
        $myq = "SELECT REFER 'Ref. No.', REQDATE 'Requested', USEDATE 'Used', HOURS 'Hrs',
                    T.DESCR 'Type', NOTE 'Comment', IF(APPROVE=1,'Yes','No') 'Approved?', 
                    AUDITID 'Last Mod.', REASON 'Reason' 
                FROM REQUEST R, TIMETYPE T
                WHERE ID='" . $_SESSION['userName'] .
                "' AND R.TIMETYPEID=T.TIMETYPEID";
        popUpMessage("$myq"); //DEBUG

        $result = $mysqli->query($myq);
        if (!$result) 
        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");

        //build table
        resultTable($mysqli, $result);
        break;
    
    case 25: //supervisor, list division
    case 50: //HR
    case 99: //Sheriff
    case 100: //full admin, complete raw dump
        $mysqli = connectToSQL();
        $myq = "SELECT *
                FROM REQUEST";
        //echo $myq . "</br>"; //DEBUG

        $result = $mysqli->query($myq);
        if (!$result) 
        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");

        //build table
        resultTable($mysqli, $result);
        echo '</hr>';
        resultForm($mysqli, $result);
        break;
        
}



?>

