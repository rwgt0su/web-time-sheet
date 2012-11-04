<?php

function myAlerts($config){
    if(isValidUser($config)){
        //popUpMessage('You have an Alert! <a href="?approve=true">Go To Request</a>');
    }
    alert_VerifyUsers($config);
    alert_PostPayrollValidation($config);
}
function myAlertsLogout(){
    unset ($_SESSION['dismissVerifyUser']);
}

function alert_VerifyUsers($config){
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
function alert_PostPayrollValidation($config){
    if($config->adminLvl == 50){
        $dismiss = isset($_POST['dismissPostValidBtn']) ? true : false;
        $dismiss = isset($_GET['postPayrollValid']) ? true : $dismiss;
        //No dismissal session variable for real time alerting
        //$dismiss = isset($_SESSION['dismissPostValid']) ? true : $dismiss;
        if(!$dismiss){
            $mysqli = $config->mysqli;
            //Get approved time request submitted to HR if date of use is prior to last pay period and 
            //current date is after end of payperiod
            
            //determine last day of last approved pay period
            $today = date('Y-m-d');
            $myq = "SELECT COUNT(REFER), MAX(USEDATE) 'endDate', MIN(USEDATE) 'startDate'
                FROM REQUEST
                WHERE (STATUS='APPROVED' OR STATUS='DENIED')
                AND HRAPP_IS = '0'
                AND USEDATE <= (SELECT PPEND FROM PAYPERIOD WHERE PPEND = (SELECT PPBEG-1 FROM PAYPERIOD WHERE '".$today."' BETWEEN PPBEG AND PPEND))";

            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result, $myq);

            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                popUpMessage('<div align="center"><form name="verifyAlert" method="POST" action="?hrEmpRep=true&cust=true&postPayrollValid=true">
                    New Time Request after validation!
                    <input type="submit" name="dismissPostValidBtn" value="Go to Alert" />
                    <input type="hidden" name="start" value="'.$row['startDate'].'" />
                    <input type="hidden" name="end" value="'.$row['endDate'].'" />
                    </form></div>', 'ALERT');
            }
        }
        else{
            //$_SESSION['dismissPostValid'] = "1";
        }
    }
}
?>
