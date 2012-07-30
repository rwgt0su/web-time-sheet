<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function displaySecondaryLog($config){
    ?>
    <h2>Secondary Employment Daily Logs</h2>
    <form name="secLogOpt" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
        
        <?php
        //Get variables
        $dateSelect = isset($_POST['dateSelect']) ? $_POST['dateSelect'] : false;
        $changeDateBtn = isset($_POST['changeDate']) ? True : false;
        $editSelect = isset($_POST['editRows']) ? $_POST['editRows'] : false;
        $addBtn = isset($_POST['addBtn']) ? True : false;
        $editBtn = isset($_POST['editBtn']) ? True : false;
        $secLogID = isset($_POST['secLogID']) ? $_POST['secLogID'] : false;
        $rowNum = isset($_POST['rowNum ']) ? $_POST['rowNum '] : false;
        
        if($changeDateBtn){
            $dateSelect = false;
            $editSelect = false;
        }
        if(!$dateSelect){
            echo 'Select Date: ';
            displayDateSelect("dateSelect", "dateSel",false,false,true);
            echo '<input type=submit name="goBtn" value="Go" /><br />'; 
            
        }
        else{
            echo '<h3>Date: '.$dateSelect.'';
            echo '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />
                <input type="submit" name="changeDate" value="Change Date" /></h3>';
        }
        if(isset($_POST['editRows'])){
            for ($i=0; $i <= $editSelect; $i++){
                if(isset($_POST['secLogID'.$i]))
                    $secLogID = $_POST['secLogID'.$i];
            }
            if(!empty($secLogID))
                showSecLogDetails($config, $secLogID, true);
            else
                echo 'Error getting Reference Number';
        }
        if(isset($_POST['goBtn'])){
            if($config->adminLvl < 25){
                //non supervisor logs
                showSecLog($config, $dateSelect, false);
            }
            else{
                //supervisor logs
                showSecLog($config, $dateSelect, true);
            }
        }
        if($addBtn){
            showSecLogDetails($config, $secLogID, false);
        }
        if($editBtn){
            if($config->adminLvl < 25){
                //Non supervisor Log details
                showSecLogDetails($config, $secLogID, false);
            }
            else{
                //Supervisor Log Details
                showSecLogDetails($config, $secLogID, true);
            }
        }
        ?>
    </form>
    <br />
    <br />
    <?php
    
}
function showSecLog($config, $dateSelect, $secLogID){
    $mysqli = $config->mysqli;
    /*$myq = "SELECT *
        FROM `SECLOG`
        WHERE `SHIFTDATE` = '".Date('Y-m-d', strtotime($dateSelect))."'";*/
    /*query unions the results of joins on two different tables (EMPLOYEE and RESERVE)
      depending on the value of SECLOG.IS_RESERVE*/
  $myq =  "SELECT CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', SEC.RADIO, TIMEIN,
                CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', LOCATION, S.CITY,
                SHIFTSTART, SHIFTEND, DRESS, TIMEOUT, 
                CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID', 
                CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', SUP_TIME,
                PHONE, S.IDNUM
            FROM SECLOG S
            INNER JOIN EMPLOYEE AS SEC ON S.DEPUTYID=SEC.IDNUM
            LEFT JOIN EMPLOYEE AS LOGIN ON S.AUDIT_IN_ID=LOGIN.IDNUM
            LEFT JOIN EMPLOYEE AS LOGOUT ON S.AUDIT_OUT_ID=LOGOUT.IDNUM
            LEFT JOIN EMPLOYEE AS SUP ON S.SUP_ID=SUP.IDNUM
            WHERE `SHIFTDATE` = '".Date('Y-m-d', strtotime($dateSelect))."' 
            AND S.IS_RESERVE=0
            

            UNION

            SELECT CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', SEC.RADIO, TIMEIN,
                CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', LOCATION, S.CITY,
                SHIFTSTART, SHIFTEND, DRESS, TIMEOUT, 
                CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID', 
                CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', SUP_TIME,
                PHONE, S.IDNUM
            FROM SECLOG S
            INNER JOIN RESERVE AS SEC ON S.DEPUTYID=SEC.IDNUM
            LEFT JOIN EMPLOYEE AS LOGIN ON S.AUDIT_IN_ID=LOGIN.IDNUM
            LEFT JOIN EMPLOYEE AS LOGOUT ON S.AUDIT_OUT_ID=LOGOUT.IDNUM
            LEFT JOIN EMPLOYEE AS SUP ON S.SUP_ID=SUP.IDNUM
            WHERE `SHIFTDATE` = '".Date('Y-m-d', strtotime($dateSelect))."' 
            AND S.IS_RESERVE=1
            ORDER BY IDNUM";

    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $echo = '<table>';
    if($config->adminLvl >= 25){
        //resultTable($mysqli, $result, 'false');
        $i=0;
        $echo = '<table><tr><td>Edit</td><td>Deputy</td><td>Radio#</td><td>Log In</td><td>C/Deputy</td><td>Site Name/Address</td>
            <td>City/Twp</td><td>Shift Start</td><td>Shift End</td><td>Dress</td><td>Log Off</td><td>C/Deputy</td>
            <td>Supervisor</td><td>Sign Off</td></tr>';

        while($row = $result->fetch_assoc()) {
            $echo .= '<tr><td><input type="hidden" name="editRows" value="'.$i.'" />
                <input type="radio" name="secLogID'.$i.'" value="'.$row['IDNUM'].'" onclick="this.form.submit();" /></td>
                <td>'.$row['DEPUTYID'].'</td>
                <td>'.$row['RADIO'].'</td>
                <td>'.$row['TIMEIN'].'</td>
                <td>'.$row['AUDIT_IN_ID'].'</td>
                <td>'.$row['LOCATION'].'</td>
                <td>'.$row['CITY'].'</td>
                <td>'.$row['SHIFTSTART'].'</td>
                <td>'.$row['SHIFTEND'].'</td>
                <td>'.$row['DRESS'].'</td>
                <td>'.$row['TIMEOUT'].'</td>
                <td>'.$row['AUDIT_OUT_ID'].'</td>
                <td>'.$row['SUP_ID'].'</td>
                <td>'.$row['SUP_TIME'].'</td>
                </tr>';
            $i++;
        }
    }
    else{
       $echo = '<table><tr><td>Deputy</td><td>Radio#</td><td>Log In</td><td>C/Deputy</td><td>Site Name/Address</td>
            <td>City/Twp</td><td>Contact#</td><td>Shift Start</td><td>Shift End</td></tr>';
       $i=0;
       
        while($row = $result->fetch_assoc()) {
            $echo .= '<tr>  <td>'.$row['DEPUTYID'].'</td>
                            <td>'.$row['RADIO'].'</td>
                            <td>'.$row['TIMEIN'].'</td>
                            <td>'.$row['AUDIT_IN_ID'].'</td>
                            <td>'.$row['LOCATION'].'</td>
                            <td>'.$row['CITY'].'</td>
                            <td>'.$row['PHONE'].'</td>
                            <td>'.$row['SHIFTSTART'].'</td>
                            <td>'.$row['SHIFTEND'].'</td>
                            <input type="hidden" name="secLogID'.$i.'" value="'.$row['IDNUM'].'" />
                            </tr>';
            $i++;
        } 
    }
    //$echo .= '<input type="hidden" name="rowNum" value="'.$i.'" /></table>';
    $echo .= '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />';
    $echo .= '<input type="submit" name="addBtn" value="New Log In" /></table>';
    echo $echo;
    
}
function showSecLogDetails($config, $secLogID, $isEditing=false){
    $addSecLog = isset($_POST['addSecLog']) ? true : false;
    $logoutSecLog = isset($_POST['logoutSecLog']) ? true : false;
    
    if($addSecLog){
        //get passed values
        $deputy = isset($_POST['deputy']) ? $_POST['deputy'] : '';
        $radioNum = isset($_POST['radioNum']) ? $_POST['radioNum'] : '';
        $address = isset($_POST['address']) ? $_POST['address'] : '';
        $city = isset($_POST['city']) ? $_POST['city'] : '';
        $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
        $shiftStart1 = isset($_POST['shiftStart1']) ? $_POST['shiftStart1'] : '';
        $shiftStart2 = isset($_POST['shiftStart2']) ? $_POST['shiftStart2'] : '';
        $shiftStart = $shiftStart1.$shiftStart2."00";
        $shiftEnd1 = isset($_POST['shiftEnd1']) ? $_POST['shiftEnd1'] : '';
        $shiftEnd2 = isset($_POST['shiftEnd2']) ? $_POST['shiftEnd2'] : '';
        $shiftEnd = $shiftEnd1.$shiftEnd2."00";
        $dress = isset($_POST['dress']) ? $_POST['dress'] : '';
        
        //add to database
        $mysqli = $config->mysqli;
        $myq = "INSERT INTO `PAYROLL`.`SECLOG` ( `IDNUM` ,`DEPUTYID` ,`RADIO` ,`TIMEIN` ,`AUDIT_IN_ID` ,
            `AUDIT_IN_TIME` ,`AUDIT_IN_IP` ,`LOCATION` ,`CITY` ,`PHONE` ,`SHIFTDATE` ,`SHIFTSTART` ,
            `SHIFTEND` ,`DRESS` ,`TIMEOUT` ,`AUDIT_OUT_ID` ,`AUDIT_OUT_TIME` ,`AUDIT_OUT_IP` ,`SUP_ID` ,
            `SUP_TIME` ,`SUP_IP`) VALUES (
            NULL , '".$deputy."', '".$radioNum."', 'NOW()', '".$_SESSION['userIDnum']."', 'NOW()', INET_ATON('".$_SERVER['REMOTE_ADDR']."'), 
                '".$address."', '".$city."', '".$phone."', '".Date('Y-m-d', strtotime($_POST['dateSelect']))."', 
                '".$shiftStart."', '".$shiftEnd."', '".$dress."', '', '', '', '', '', '', ''
            );";
        $result = $mysqli->query($myq);
        if(!SQLerrorCatch($mysqli, $result)) {
            $secLogID = $mysqli->insert_id;      
            echo '<h2>Results</h2>Successfully Added Secondary Employment Log, Reference Number: '.$secLogID.'<br /><br />';
        }
        else
            echo '<h2>Results</h2>Failed to add Secondary Employment Log, try again.<br /><Br />';
        
        
        //display results and get secLogID just added
        $isEditing = true;
        
        
    }
    if($logoutSecLog){
        $secLogID = isset($_POST['secLogID']) ? $_POST['secLogID'] : '';
        
        $myq = "UPDATE `PAYROLL`.`SECLOG` SET `TIMEOUT` = NOW( ) ,
            `AUDIT_OUT_ID` = '".$_SESSION['userIDnum']."', `AUDIT_OUT_TIME` = NOW( ) ,
            `AUDIT_OUT_IP` = INET_ATON('".$_SERVER['REMOTE_ADDR']."') WHERE `SECLOG`.`IDNUM` = ".$secLogID." LIMIT 1 ;";
        $result = $mysqli->query($myq);
        If(!SQLerrorCatch($mysqli, $result))
                echo '<h2>Results</h2>Successfully Logged Out Reference Number: '.$secLogID.'<br /><br />';
        else
            echo '<h2>Results</h2>Failed to logout Secondary Employment Log, try again.<br /><Br />';   
    }
    
    if($isEditing){
        $mysqli = $config->mysqli;
        $myq = "SELECT CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYID', S.RADIO, LOCATION, S.CITY, PHONE,
                    SHIFTSTART, SHIFTEND, DRESS, S.IDNUM
                FROM SECLOG S
                JOIN EMPLOYEE AS SEC ON SEC.IDNUM=S.DEPUTYID
                WHERE S.IDNUM = '".$secLogID."' AND IS_RESERVE=0
                UNION
                SELECT CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYID', S.RADIO, LOCATION, S.CITY, PHONE,
                    SHIFTSTART, SHIFTEND, DRESS, S.IDNUM
                FROM SECLOG S
                JOIN RESERVE AS SEC ON SEC.IDNUM=S.DEPUTYID
                WHERE S.IDNUM = '".$secLogID."' AND IS_RESERVE=1
                ORDER BY IDNUM";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $row = $result->fetch_assoc();
        if($config->adminLvl >= 25){
            echo '<input type="hidden" name="formName" value="secLogOpt" /> 
                Reference #: '.$secLogID.'<input type="hidden" name="secLogID" value="'.$secLogID.'" /><br />
                Deputy: <input type="text" name="deputy" value="'.$row['DEPUTYID'].'" /><br/>
                Radio#: <input type="text" name="radioNum" value="'.$row['RADIO'].'" /><br/>
                Site Name or Address: <input type="text" name="address" value="'.$row['LOCATION'].'" /><br/>
                City/Twp: <input type="text" name="city" value="'.$row['CITY'].'" /><br/>
                Contact#: <input type="text" name="phone" value="'.$row['PHONE'].'" /><br/>
                Shift Start Time: ';
                $temp = explode(":", $row['SHIFTSTART']);
            showTimeSelector("shiftStart", $temp[0], $temp[1], false);
            echo ' <br/>
                Shift End Time: ';
            $temp = explode(":", $row['SHIFTEND']);
            showTimeSelector("shiftEnd", $temp[0], $temp[1], false);
            echo '<br/>
                Dress: <select name="dress">
                    <option value=""></option>
                    <option value="U"';
            if(strcmp($row['DRESS'], "U") ==0)
                    echo ' SELECT ';
            echo '>Uniform</option>
                    <option value="PC"';
            if(strcmp($row['DRESS'], "PC") ==0)
                    echo ' SELECT ';
            echo '>Plain Clothes</option>
                </select><br/><br />
                <input type="submit" name="logoutSecLog" value="LogOut" />
                <input type="submit" name="updateSecLog" value="Update" />
                <input type="submit" name="goBtn" value="Cancel" />';
        }
        else{
            echo 'Reference #: '.$secLogID.'<input type="hidden" name="secLogID" value="'.$secLogID.'" />
                Deputy: '.$row['DEPUTYID'];
                lookupButton($config, 'secLogOpt');
                echo '<br/>';
                echo 'Radio#: '.$row['RADIO'].'<br/>
                Site Name or Address: '.$row['LOCATION'].'<br/>
                City/Twp: '.$row['CITY'].'<br/>
                Contact#: '.$row['PHONE'].'<br/>
                Shift Start Time: ';
                $temp = explode(":", $row['SHIFTSTART']);
            echo $temp[0].' : '.$temp[1];
            echo ' <br/>
                Shift End Time: ';
            $temp = explode(":", $row['SHIFTEND']);
            echo $temp[0].' : '.$temp[1];
            echo '<br/>
                Dress:';
            if(strcmp($row['DRESS'], "U") ==0)
                    echo ' Uniform ';
            if(strcmp($row['DRESS'], "PC") ==0)
                    echo ' Plain Clothes ';
            echo '<br/><br />
                <input type="submit" name="logoutSecLog" value="LogOut" />
                <input type="submit" name="goBtn" value="Back" />';
        }
    }
    if(!$isEditing && !isset($_POST['goBtn'])){
        echo '<input type="hidden" name="formName" value="secLogOpt" />
            Deputy: <input type="text" name="deputy" value="" />';
        displayUserLookup($config);
        echo '<br/>';
            echo 'Radio#: <input type="text" name="radioNum" value="" /><br/>
            Site Name or Address: <input type="text" name="address" value="" /><br/>
            City/Twp: <input type="text" name="city" value="" /><br/>
            Contact#: <input type="text" name="phone" value="" /><br/>
            Shift Start Time: ';
        showTimeSelector("shiftStart", "", "", false);
        echo ' <br/>
            Shift End Time: ';
        showTimeSelector("shiftEnd", "", "", false);
        echo '<br/>
            Dress: <select name="dress">
                <option value=""></option>
                <option value="U">Uniform</option>
                <option value="PC">Plain Clothes</option>
            </select><br/><br />
            <input type="hidden" name="addBtn" value="true" />
            <input type="submit" name="addSecLog" value="Add" />
            <input type="submit" name="goBtn" value="Cancel" />';
    }
}
?>
