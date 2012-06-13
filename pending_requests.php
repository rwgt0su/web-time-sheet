<?php

/*
 * A report of recent leave requests with
 * different views according to admin level
 */

require_once 'bin/common.php';

$mysqli = connectToSQL();
$admin = $_SESSION['admin'];
 
/*if (isset($_POST['editBtn'])){
    resultForm($mysqli, $result);
   }
else {*/

    switch($admin) {
        case 0: //normal user, list only user's own reqs

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
        
            $myq = "SELECT *
                    FROM REQUEST";
            //echo $myq . "</br>"; //DEBUG
            popUpMessage("$myq"); //DEBUG

            $result = $mysqli->query($myq);
            if (!$result) 
                throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
            if (isset($_POST['editBtn']))
                resultForm($mysqli, $result);
            else
                //build table
                resultTable($mysqli, $result);


            break;

    } //end switch
//}//end else
?>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>?editBtn=true" method="post" name="editBtn">
    <p><input type="submit" name="editBtn" value="Edit"></p></form>





