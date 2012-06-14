<?php

/*
 * A report of recent leave requests with
 * different views according to admin level
 */

require_once 'bin/common.php';

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
if (isset($_POST['editBtn']))
                resultForm($mysqli, $result);
            else
                //build table
                resultTable($mysqli, $result);
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





