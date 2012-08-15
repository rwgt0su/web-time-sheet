<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function displaySecondaryLog($config, $isApprove = false){
    if($isApprove)
        echo '<h2>Secondary Employment Daily Logs Approval</h2>';
    else
        echo '<h2>Secondary Employment Daily Logs</h2>';
        ?>
    <form name="secLog" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
        <input type="hidden" name="formName" value="secLog" /> 
        
        <?php
        //Get variables
        $dateSelect = isset($_POST['dateSelect']) ? $_POST['dateSelect'] : false;
        $changeDateBtn = isset($_POST['changeDate']) ? True : false;
        $editSelect = isset($_POST['editRows']) ? $_POST['editRows'] : false;
        $addBtn = isset($_POST['addBtn']) ? True : false;
        $editBtn = isset($_POST['editBtn']) ? True : false;
        $secLogID = isset($_POST['secLogID']) ? $_POST['secLogID'] : false;
        $rowNum = isset($_POST['rowNum ']) ? $_POST['rowNum '] : false;
        $logoutSecLog = isset($_POST['logoutSecLog']) ? true : false;
        $updateSecLog = isset($_POST['updateSecLog']) ? true : false;
        $showAll = isset($_POST['showAll']) ? true : false;
        $showNormal = isset($_POST['showNormal']) ?  true : false;
        $goBtn = isset($_POST['goBtn']) ? true : false;
        $isApprove = isset($_POST['isApprove']) ? true : $isApprove;
        
        if($showAll || $showNormal){
            $goBtn = true;
        }
        if($changeDateBtn){
            $dateSelect = false;
            $editSelect = false;
            $goBtn = false;
            $addBtn = false;
        }
        if(!$dateSelect){
            echo 'Select Date: ';
            displayDateSelect("dateSelect", "dateSel",false,false,true,true);
            echo '<input id="goBtn" type=submit name="goBtn" value="Go" /><br />'; 
            if($isApprove){
                echo '<input type="hidden" name="isApprove" value="true" />';
            }
            
        }
        else{
            echo '<h3>Date: '.$dateSelect.'';
            echo '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />
                <input type="submit" name="changeDate" value="Change Date" /></h3>';
            if($isApprove){
                echo '<input type="hidden" name="isApprove" value="true" />';
            }
        }
        if(isset($_POST['editRows'])){
            //popUpMessage($_POST['secLogRadio1']);
            for ($i=0; $i <= $editSelect; $i++){
                if(isset($_POST['secLogRadio'.$i]))
                    $secLogID = $_POST['secLogID'.$i];
            }
            if(!empty($secLogID))
                showSecLogDetails($config, $secLogID, true);
            else if(!$addBtn && !$showAll && !$showNormal && !$changeDateBtn && !$isApprove){
                echo 'Error getting Reference Number!<br />';
                echo '<input type="submit" name="goBtn" value="Back To Logs" />';
            }
        }
        if($goBtn){
            if($isApprove){
                showSecLog($config, $dateSelect, false, $isApprove);
            }
            else{
                if($config->adminLvl < 25){
                    //non supervisor logs
                    showSecLog($config, $dateSelect, false);
                }
                else{
                    //supervisor logs
                    showSecLog($config, $dateSelect, true);
                }
            }
        }
        
        if($addBtn || $logoutSecLog || $updateSecLog){
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

function showSecLog($config, $dateSelect, $secLogID, $isApprove=false){
    $mysqli = $config->mysqli;
    $app = isset($_POST['isApprove']) ? true : $isApprove;
     
             
    //Get Approved Checkboxes
    $approveBtn = isset($_POST['approveBtn']) ? true : false;
    if($approveBtn) {
        for($i=0;$i<=$_POST['totalRows'];$i++) {
            $box[$i] = isset($_POST['secLogApproved'.$i]) ? true : false;
            if($box[$i]=='true'){
            $secLogID = $_POST['secLogID'.$i];
                $myq = "UPDATE SECLOG 
                        SET SUP_ID = '".$_SESSION['userIDnum']."',
                            SUP_TIME = NOW(),
                            SUP_IP = INET_ATON('".$_SERVER['REMOTE_ADDR']."') 
                        WHERE SECLOG.IDNUM = ".$secLogID;
                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result);
                echo 'Log #'.$secLogID.' approved.<br />';
            }
        }
    }
        
    /*query unions the results of joins on two different tables (EMPLOYEE and RESERVE)
      depending on the value of SECLOG.IS_RESERVE */
    if(!$isApprove){
        $myq =  "SELECT CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO, TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
                    CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', LOCATION, S.CITY,
                    TIME_FORMAT(SHIFTSTART,'%H%i') 'SHIFTSTART', TIME_FORMAT(SHIFTEND,'%H%i') 'SHIFTEND',
                    DRESS, TIME_FORMAT(TIMEOUT,'%H%i') 'TIMEOUT', 
                    CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID', 
                    CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', DATE_FORMAT(SUP_TIME,'%m/%d/%y %H%i') 'SUP_TIME',
                    PHONE, S.IDNUM
                FROM SECLOG S
                INNER JOIN EMPLOYEE AS SEC ON S.DEPUTYID=SEC.IDNUM
                LEFT JOIN EMPLOYEE AS LOGIN ON S.AUDIT_IN_ID=LOGIN.IDNUM
                LEFT JOIN EMPLOYEE AS LOGOUT ON S.AUDIT_OUT_ID=LOGOUT.IDNUM
                LEFT JOIN EMPLOYEE AS SUP ON S.SUP_ID=SUP.IDNUM
                WHERE `SHIFTDATE` = '".Date('Y-m-d', strtotime($dateSelect))."' 
                AND S.IS_RESERVE=0

                UNION

                SELECT CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO, TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
                    CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', LOCATION, S.CITY,
                    TIME_FORMAT(SHIFTSTART,'%H%i') 'SHIFTSTART', TIME_FORMAT(SHIFTEND,'%H%i') 'SHIFTEND',
                    DRESS, TIME_FORMAT(TIMEOUT,'%H%i') 'TIMEOUT', 
                    CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID', 
                    CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', DATE_FORMAT(SUP_TIME,'%m/%d/%y %H%i') 'SUP_TIME',
                    PHONE, S.IDNUM
                FROM SECLOG S
                INNER JOIN RESERVE AS SEC ON S.DEPUTYID=SEC.IDNUM
                LEFT JOIN EMPLOYEE AS LOGIN ON S.AUDIT_IN_ID=LOGIN.IDNUM
                LEFT JOIN EMPLOYEE AS LOGOUT ON S.AUDIT_OUT_ID=LOGOUT.IDNUM
                LEFT JOIN EMPLOYEE AS SUP ON S.SUP_ID=SUP.IDNUM
                WHERE `SHIFTDATE` = '".Date('Y-m-d', strtotime($dateSelect))."' 
                AND S.IS_RESERVE=1
                ORDER BY IDNUM";
    }
    else{
        $myq =  "SELECT CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO, TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
                    CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', LOCATION, S.CITY,
                    TIME_FORMAT(SHIFTSTART,'%H%i') 'SHIFTSTART', TIME_FORMAT(SHIFTEND,'%H%i') 'SHIFTEND',
                    DRESS, TIME_FORMAT(TIMEOUT,'%H%i') 'TIMEOUT', 
                    CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID', 
                    CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', DATE_FORMAT(SUP_TIME,'%m/%d/%y %H%i') 'SUP_TIME',
                    PHONE, S.IDNUM
                FROM SECLOG S
                INNER JOIN EMPLOYEE AS SEC ON S.DEPUTYID=SEC.IDNUM
                LEFT JOIN EMPLOYEE AS LOGIN ON S.AUDIT_IN_ID=LOGIN.IDNUM
                LEFT JOIN EMPLOYEE AS LOGOUT ON S.AUDIT_OUT_ID=LOGOUT.IDNUM
                LEFT JOIN EMPLOYEE AS SUP ON S.SUP_ID=SUP.IDNUM
                WHERE `SHIFTDATE` = '".Date('Y-m-d', strtotime($dateSelect))."'
                AND AUDIT_OUT_ID != ''
                AND S.IS_RESERVE=0

                UNION

                SELECT CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO, TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
                    CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', LOCATION, S.CITY,
                    TIME_FORMAT(SHIFTSTART,'%H%i') 'SHIFTSTART', TIME_FORMAT(SHIFTEND,'%H%i') 'SHIFTEND',
                    DRESS, TIME_FORMAT(TIMEOUT,'%H%i') 'TIMEOUT', 
                    CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID', 
                    CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', DATE_FORMAT(SUP_TIME,'%m/%d/%y %H%i') 'SUP_TIME',
                    PHONE, S.IDNUM
                FROM SECLOG S
                INNER JOIN RESERVE AS SEC ON S.DEPUTYID=SEC.IDNUM
                LEFT JOIN EMPLOYEE AS LOGIN ON S.AUDIT_IN_ID=LOGIN.IDNUM
                LEFT JOIN EMPLOYEE AS LOGOUT ON S.AUDIT_OUT_ID=LOGOUT.IDNUM
                LEFT JOIN EMPLOYEE AS SUP ON S.SUP_ID=SUP.IDNUM
                WHERE `SHIFTDATE` = '".Date('Y-m-d', strtotime($dateSelect))."' 
                AND AUDIT_OUT_ID != ''
                AND S.IS_RESERVE=1
                ORDER BY IDNUM";
        echo '<input type="hidden" name="isApprove" value="true" />';
        echo '<input type="hidden" name="goBtn" value="true" />';
    }

    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $echo = '';
    $x=0;
    if($config->adminLvl >= 25){
        //resultTable($mysqli, $result, 'false');
        $showAll = isset($_POST['showAll']) ? true : false;
        if($showAll)
            echo '<div align="right"><input type="checkbox" name="showNormal" onclick="this.form.submit();" />Show Normal Logs</div>';
        else
            echo '<div align="right"><input type="checkbox" name="showAll" onclick="this.form.submit();" />Show All Logs</div>';
        $theTable = array(array());
        if(!$isApprove)
            $theTable[$x][0] = "Edit";
        else
            $theTable[$x][0] = "Approve";
        $theTable[$x][1] = "Deputy";
        $theTable[$x][2] = "Radio#";
        $theTable[$x][3] = "Log In";
        $theTable[$x][4] = "C/Deputy";
        $theTable[$x][5] = "Site Name/Address";
        $theTable[$x][6] = "City/Twp";
        $theTable[$x][7] = "Contact#";
        $theTable[$x][8] = "Shift Start";
        $theTable[$x][9] = "Shift End";
        $theTable[$x][10] = "Dress";
        $theTable[$x][11] = "Log Off";
        $theTable[$x][12] = "C/Deputy";
        $theTable[$x][13] = "Supervisor";
        $theTable[$x][14] = "Sign Off";

        while($row = $result->fetch_assoc()) {
            if(strcmp($row['TIMEOUT'], "0000") == 0 || $showAll || (strcmp($row['SUP_TIME'], "00/00/00 0000") == 0 && $isApprove)){
                $x++;
                if(!$isApprove)
                    $theTable[$x][0] = '<input type="submit" value="Edit/View" name="secLogRadio'.$x.'" />
                        <input type="hidden" name="secLogID'.$x.'" value="'.$row['IDNUM'].'" />';
                else
                    $theTable[$x][0] = 'Ref# '.$row['IDNUM'].'<input type="checkbox" name="secLogApproved'.$x.'" value="true" />
                        <input type="hidden" name="secLogID'.$x.'" value="'.$row['IDNUM'].'" />';
                $theTable[$x][1] = $row['DEPUTYID'];
                $theTable[$x][2] = $row['RADIO'];
                $theTable[$x][3] = $row['TIMEIN'];
                $theTable[$x][4] =$row['AUDIT_IN_ID'];
                $theTable[$x][5] =$row['LOCATION'];
                $theTable[$x][6] =$row['CITY'];
                $theTable[$x][7] =$row['PHONE'];
                $theTable[$x][8] =$row['SHIFTSTART'];
                $theTable[$x][9] =$row['SHIFTEND'];
                $theTable[$x][10] =$row['DRESS'];
                $theTable[$x][11] =$row['TIMEOUT'];
                $theTable[$x][12] =$row['AUDIT_OUT_ID'];
                $theTable[$x][13] =$row['SUP_ID'];
                $theTable[$x][14] =$row['SUP_TIME'];
                
            }
        }
    }
    else{
       $showAll = isset($_POST['showAll']) ? true : false;
        if($showAll)
            echo '<div align="right"><input type="checkbox" name="showNormal" onclick="this.form.submit();" />Show Normal Logs</div>';
        else
            echo '<div align="right"><input type="checkbox" name="showAll" onclick="this.form.submit();" />Show All Logs</div>';
       $theTable = array(array());
        $theTable[$x][0] = "Edit";
        $theTable[$x][1] = "Deputy";
        $theTable[$x][2] = "Radio#";
        $theTable[$x][3] = "Log In";
        $theTable[$x][4] = "C/Deputy";
        $theTable[$x][5] = "Site Name/Address";
        $theTable[$x][6] = "City/Twp";
        $theTable[$x][7] = "Contact#";
        $theTable[$x][8] = "Shift Start";
        $theTable[$x][9] = "Shift End";
    
        while($row = $result->fetch_assoc()) {
            if(strcmp($row['TIMEOUT'], "0000") == 0 || $showAll ){
                $x++;
                $theTable[$x][0] = '<input type="submit" value="View" name="secLogRadio'.$x.'" />
                    <input type="hidden" name="secLogID'.$x.'" value="'.$row['IDNUM'].'" />';
                $theTable[$x][1] = $row['DEPUTYID'];
                $theTable[$x][2] = $row['RADIO'];
                $theTable[$x][3] = $row['TIMEIN'];
                $theTable[$x][4] =$row['AUDIT_IN_ID'];
                $theTable[$x][5] =$row['LOCATION'];
                $theTable[$x][6] =$row['CITY'];
                $theTable[$x][7] =$row['PHONE'];
                $theTable[$x][8] =$row['SHIFTSTART'];
                $theTable[$x][9] =$row['SHIFTEND'];
            }
        } 
    }
    showSortableTable($theTable, 3);
    $echo .= '<input type="hidden" name="editRows" value="'.$x.'" />';
    $echo .= '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />';
    
    echo $echo;
    if($isApprove)
        echo '<input type="submit" name="approveBtn" value="Approve Selected Logs" />';
    else
        echo '<input type="submit" name="addBtn" value="New Log In" />';
    
}
function showSecLogDetails($config, $secLogID, $isEditing=false){
    $addSecLog = isset($_POST['addSecLog']) ? true : false;
    $logoutSecLog = isset($_POST['logoutSecLog']) ? true : false;
    $updateSecLog = isset($_POST['updateSecLog']) ? true : false;
    
    $mysqli = $config->mysqli;
    
    if($addSecLog){
        //get passed values
        $deputy = isset($_POST['deputy']) ? $mysqli->real_escape_string(strtoupper($_POST['deputy'])) : false;
        $radioNum = isset($_POST['radioNum']) ? $mysqli->real_escape_string(strtoupper($_POST['radioNum'])) : '';
        $address = isset($_POST['address']) ? $mysqli->real_escape_string(strtoupper($_POST['address'])) : '';
        $city = isset($_POST['city']) ? $mysqli->real_escape_string(strtoupper($_POST['city'])) : '';
        $phone = isset($_POST['phone']) ? $mysqli->real_escape_string($_POST['phone']) : '';
        $shiftStart1 = !empty($_POST['shiftStart1']) ? $mysqli->real_escape_string($_POST['shiftStart1']) : '00';
        $shiftStart2 = !empty($_POST['shiftStart2']) ? $mysqli->real_escape_string($_POST['shiftStart2']) : '00';
        $shiftStart = $shiftStart1.$shiftStart2."00";
        $shiftEnd1 = !empty($_POST['shiftEnd1']) ? $mysqli->real_escape_string($_POST['shiftEnd1']) : '00';
        $shiftEnd2 = !empty($_POST['shiftEnd2']) ? $mysqli->real_escape_string($_POST['shiftEnd2']) : '00';
        $shiftEnd = $shiftEnd1.$shiftEnd2."00";
        $dress = isset($_POST['dress']) ? $mysqli->real_escape_string($_POST['dress']) : '';
        $isReserve = isset($_POST['isReserve']) ? '1' : '0';
        
        //add to database
        if(!empty($deputy)){
            $myq = "INSERT INTO `SECLOG` ( `IDNUM` ,`DEPUTYID` ,`RADIO` ,`TIMEIN` ,`AUDIT_IN_ID` ,
                `AUDIT_IN_TIME` ,`AUDIT_IN_IP` ,`LOCATION` ,`CITY` ,`PHONE` ,`SHIFTDATE` ,`SHIFTSTART` ,
                `SHIFTEND` ,`DRESS` ,`TIMEOUT` ,`AUDIT_OUT_ID` ,`AUDIT_OUT_TIME` ,`AUDIT_OUT_IP` ,`SUP_ID` ,
                `SUP_TIME` ,`SUP_IP`, IS_RESERVE) VALUES (
                NULL , '".$deputy."', '".$radioNum."', NOW(), '".$_SESSION['userIDnum']."', NOW(), INET_ATON('".$_SERVER['REMOTE_ADDR']."'), 
                    '".$address."', '".$city."', '".$phone."', '".Date('Y-m-d', strtotime($_POST['dateSelect']))."', 
                    '".$shiftStart."', '".$shiftEnd."', '".$dress."', '', '', '', '', '', '', '',".$isReserve."
                );";
            $result = $mysqli->query($myq);
            if(!SQLerrorCatch($mysqli, $result)) {
                $secLogID = $mysqli->insert_id;      
                echo '<h2>Results</h2>Successfully Added Secondary Employment Log, Reference Number: '.$secLogID.'<br /><br />';
                $isEditing = true;
            }
            else
                echo '<h2>Results</h2>Failed to add Secondary Employment Log, try again.<br /><Br />';
        }
        else{
            echo '<h2>Results</h2>Must select a user.<br /><Br />';
        }
        
        
        //display results and get secLogID just added
        
        
        
    }
    if($logoutSecLog){
        $secLogID = isset($_POST['secLogID']) ? $_POST['secLogID'] : '';
        
        $myq = "UPDATE `SECLOG` SET `TIMEOUT` = NOW( ) ,
            `AUDIT_OUT_ID` = '".$_SESSION['userIDnum']."', `AUDIT_OUT_TIME` = NOW( ) ,
            `AUDIT_OUT_IP` = INET_ATON('".$_SERVER['REMOTE_ADDR']."') WHERE `SECLOG`.`IDNUM` = ".$secLogID." LIMIT 1 ;";
        $result = $mysqli->query($myq);
        if(!SQLerrorCatch($mysqli, $result))
                echo '<h2>Results</h2>Successfully Logged Out Reference Number: '.$secLogID.'<br /><br />';
        else
            echo '<h2>Results</h2>Failed to logout Secondary Employment Log, try again.<br /><Br />';  
        $isEditing = true;
    }
    
    if($updateSecLog){
        //get posted values
        $secLogID = isset($_POST['secLogID']) ? $mysqli->real_escape_string($_POST['secLogID']) : '';
        $radioNum = isset($_POST['radioNum']) ? $mysqli->real_escape_string($_POST['radioNum']) : '';
        $address = isset($_POST['address']) ? $mysqli->real_escape_string($_POST['address']) : '';
        $city = isset($_POST['city']) ? $mysqli->real_escape_string($_POST['city']) : '';
        $phone = isset($_POST['phone']) ? $mysqli->real_escape_string($_POST['phone']) : '';
        $shiftStart1 = isset($_POST['shiftStart1']) ? $mysqli->real_escape_string($_POST['shiftStart1']) : '';
        $shiftStart2 = isset($_POST['shiftStart2']) ? $mysqli->real_escape_string($_POST['shiftStart2']) : '';
        $shiftStart = $shiftStart1.$shiftStart2."00";
        $shiftEnd1 = isset($_POST['shiftEnd1']) ? $mysqli->real_escape_string($_POST['shiftEnd1']) : '';
        $shiftEnd2 = isset($_POST['shiftEnd2']) ? $mysqli->real_escape_string($_POST['shiftEnd2']) : '';
        $shiftEnd = $shiftEnd1.$shiftEnd2."00";
        $dress = isset($_POST['dress']) ? $mysqli->real_escape_string($_POST['dress']) : '';
        
        $myq = "UPDATE SECLOG 
                SET RADIO = '".$radioNum."', LOCATION = '".$address."', 
                    CITY = '".$city."', PHONE ='".$phone."', 
                    SHIFTSTART = '".$shiftStart."', SHIFTEND = '".$shiftEnd."', 
                    DRESS = '".$dress."' 
                WHERE IDNUM =".$secLogID;
        $result = $mysqli->query($myq);
        if(!SQLerrorCatch($mysqli, $result))
                echo '<h2>Results</h2>Successfully Updated Log #'.$secLogID.'<br /><br />';
        else
            echo '<h2>Results</h2>Failed to update Secondary Employment Log, try again.<br /><Br />';
        
        $isEditing = true;
    }
    
    if($isEditing){
        $mysqli = $config->mysqli;
        $myq = "SELECT CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', S.RADIO, LOCATION, S.CITY, PHONE,
                    SHIFTSTART, SHIFTEND, DRESS, S.IDNUM, S.TIMEOUT
                FROM SECLOG S
                JOIN EMPLOYEE AS SEC ON SEC.IDNUM=S.DEPUTYID
                WHERE S.IDNUM = '".$secLogID."' AND IS_RESERVE=0
                UNION
                SELECT CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYID', S.RADIO, LOCATION, S.CITY, PHONE,
                    SHIFTSTART, SHIFTEND, DRESS, S.IDNUM, S.TIMEOUT
                FROM SECLOG S
                JOIN RESERVE AS SEC ON SEC.IDNUM=S.DEPUTYID
                WHERE S.IDNUM = '".$secLogID."' AND IS_RESERVE=1
                ORDER BY IDNUM";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $row = $result->fetch_assoc();
        if($config->adminLvl >= 25){
            echo 'Reference #: '.$secLogID.'<input type="hidden" name="secLogID" value="'.$secLogID.'" /><br />
                Deputy: '.$row['DEPUTYNAME'].'<br/>
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
                    echo ' SELECTED ';
            echo '>Uniform</option>
                    <option value="PC"';
            if(strcmp($row['DRESS'], "PC") ==0)
                    echo ' SELECTED ';
            echo '>Plain Clothes</option>
                </select><br/>';
            echo 'Logged Off Time: ';
            if(strcmp($row['TIMEOUT'],"00:00:00")==0){
                echo "Not Logged Off Yet<br /><br />";
                echo '<input type="submit" name="logoutSecLog" value="LogOut" />';
            }
            else{
                echo $row['TIMEOUT'].'<br /><br />';
            }
            echo '<input type="submit" name="updateSecLog" value="Update" />';
            echo '<input type="submit" name="goBtn" value="Back To Logs" />';
        }
        else{
            echo 'Reference #: '.$secLogID.'<input type="hidden" name="secLogID" value="'.$secLogID.'" /><br />
                Deputy: <input type="hidden" name="deputy" value="'.$row['DEPUTYNAME'].'" />'.$row['DEPUTYNAME'].'<br/>
                Radio#: <input type="hidden" name="radioNum" value="'.$row['RADIO'].'" />'.$row['RADIO'].'<br/>
                Site Name or Address: <input type="hidden" name="address" value="'.$row['LOCATION'].'" />'.$row['LOCATION'].'<br/>
                City/Twp: <input type="hidden" name="city" value="'.$row['CITY'].'" />'.$row['CITY'].'<br/>
                Contact#: <input type="hidden" name="phone" value="'.$row['PHONE'].'" />'.$row['PHONE'].'<br/>
                Shift Start Time: ';
                $temp = explode(":", $row['SHIFTSTART']);
            echo '<input type="hidden" name="shiftStart1" value="'.$temp[0].'" />'.$temp[0].':'.'<input type="hidden" name="shiftStart2" value="'.$temp[1].'" />'.$temp[1];
            echo ' <br/>
                Shift End Time: ';
            $temp = explode(":", $row['SHIFTEND']);
            echo '<input type="hidden" name="shiftEnd1" value="'.$temp[0].'" />'.$temp[0].':'.'<input type="hidden" name="shiftEnd2" value="'.$temp[1].'" />'.$temp[1];
            echo '<br/>
                Dress:<input type="hidden" name="dress" value="'.$row['DRESS'].'" />';
            if(strcmp($row['DRESS'], "U") ==0)
                    echo ' Uniform ';
            if(strcmp($row['DRESS'], "PC") ==0)
                    echo ' Plain Clothes ';
            echo '<br/>';
            echo 'Logged Off Time: ';
            if(strcmp($row['TIMEOUT'],"00:00:00")==0){
                echo "Not Logged Off Yet<br /><br />"; 
                echo '<input type="submit" name="logoutSecLog" value="LogOut" />';
            }
            else{
                echo $row['TIMEOUT'].'<br /><br />';
            }
            
               echo '<input type="submit" name="goBtn" value="Back To Logs" />';
        }
    }
    if(!$isEditing && !isset($_POST['goBtn'])){
        $secLogID = isset($_POST['secLogID']) ? $_POST['secLogID'] :'' ;
        $radioNum = isset($_POST['radioNum']) ? $_POST['radioNum']:'';
        $address = isset($_POST['address']) ? $_POST['address'] : '';
        $city = isset($_POST['city']) ? $_POST['city'] : '';
        $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
        $shiftStart1 = isset($_POST['shiftStart1']) ? $_POST['shiftStart1'] : '';
        $shiftStart2 = isset($_POST['shiftStart2']) ? $_POST['shiftStart2']:'';
        $shiftEnd1 = isset($_POST['shiftEnd1']) ? $_POST['shiftEnd1'] : ''; 
        $shiftEnd2 = isset($_POST['shiftEnd2']) ? $_POST['shiftEnd2'] : '';
        $dress = isset($_POST['dress']) ? $_POST['dress']: '';
        $dateSelect = isset($_POST['dateSelect']) ? $_POST['dateSelect'] : '' ;
        $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : '';
        $foundUserFNAME = '';
        $foundUserLNAME = '';
        $foundUserName = '';
        $foundUserID = '' ;
        if($totalRows > 0) {         
            //get post info providied from search results
            for($i=0;$i<=$totalRows;$i++){
                if(isset($_POST['foundUser'.$i])) {
                    $foundUserFNAME = $_POST['foundUserFNAME'.$i];
                    $foundUserLNAME = $_POST['foundUserLNAME'.$i];
                    $foundUserName = $_POST['foundUserName'.$i];
                    $foundUserID = $_POST['foundUserID'.$i];
                    
                    if(isset($_POST['isReserve'.$i]))
                            echo '<input type="hidden" name="isReserve" value="true" />';
                    break;
                }//end if
            }//end for
        }
        if(!empty($foundUserID)){
            //get the selected user's radio and cell #s to pre-fill form
            $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM EMPLOYEE WHERE IDNUM='.$foundUserID;         
        }
        else{
            //get #s on the logged in user if no lookup was done
            $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM EMPLOYEE WHERE IDNUM='.$_SESSION['userIDnum'];
        }
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $row = $result->fetch_assoc();
            
        $radioNum = $row['RADIO'];   
        $phone = $row['CELLPH'];
        
            
        echo 'Deputy: <input type="hidden" name="deputy" value="'.$foundUserID.'" />';
        if(!empty($foundUserID))
            echo $foundUserLNAME . ', ' . $foundUserFNAME;
        else
            echo $row['LNAME'] . ', ' . $row['FNAME'];
        
        displayUserLookup($config);
        echo '<br/>';
            echo 'Radio#: <input type="text" name="radioNum" value="'.$radioNum.'" /><br/>
            Site Name or Address: <input type="text" name="address" value="'.$address.'" /><br/>
            City/Twp: <input type="text" name="city" value="'.$city.'" /><br/>
            Contact#: <input type="text" name="phone" value="'.$phone.'" /><br/>
            Shift Start Time: ';
        showTimeSelector("shiftStart", $shiftStart1, $shiftStart2, false);
        echo ' <br/>
            Shift End Time: ';
        showTimeSelector("shiftEnd", $shiftEnd1, $shiftEnd2, false);
        echo '<br/>
            Dress: <select name="dress">
                <option value=""></option>
                <option value="U"'; 
        if($dress=='U')
            echo ' selected '; 
        echo '>Uniform</option>
                <option value="PC"';
        if($dress=='PC')
            echo ' selected ';
        echo '>Plain Clothes</option>
            </select><br/><br />
            <input type="hidden" name="addBtn" value="true" />
            <input type="submit" name="addSecLog" value="Add" />
            <input type="submit" name="goBtn" value="Cancel" />';
    }
}

?>
