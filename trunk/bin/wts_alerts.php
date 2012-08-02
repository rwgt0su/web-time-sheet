<?php

function myAlerts($config){
    if(isValidUser()){
        //popUpMessage('You have an Alert! <a href="?approve=true">Go To Request</a>');
    }
    if($config->adminLvl >=50){
        $dismiss = isset($_POST['verifyUserAlertBtn']) ? true : false;
        $dismiss = isset($_SESSION['dismissVerifyUser']) ? true : $dismiss;
        if(!$dismiss){
            $mysqli = $config->mysqli;
            $myq = "SELECT E.IDNUM, E.ID, E.LNAME, E.FNAME, E.RADIO, E.SUPV, E.HOMEPH, E.CELLPH, E.WORKPH, E.DOB, E.EMERGCON, D.DESCR
                FROM `EMPLOYEE` E
                LEFT JOIN DIVISION AS D USING (DIVISIONID)
                LEFT JOIN EMPLOYEE AS SUP ON E.IDNUM=SUP.IDNUM
                WHERE E.IS_VERIFY =  0
                ORDER BY E.LNAME";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);

            if($result->num_rows > 0){
                popUpMessage('<div align="center"><form name="verifyAlert" method="POST" action="?userVerify=true">
                    You Have New Users to Verify
                    <input type="submit" name="verifyUserAlertBtn" value="Go To and Dismiss Alert" />
                    </form></div>');
            }
        }
        else{
            $_SESSION['dismissVerifyUser'] = "1";
        }
    }
}
function myAlertsLogout(){
    unset ($_SESSION['dismissVerifyUser']);
}
?>
