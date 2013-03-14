<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function displaySecondaryLog($config, $isApprovePage = false){
    $mysqli = $config->mysqli;
    if($isApprovePage)
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
        $isApprovePage = isset($_GET['secApprove']) ? true : $isApprovePage;
        $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
        $secLogID = isset($_POST['backToApprove']) ? false : $secLogID ;
        
        if($showAll || $showNormal){
            $goBtn = true;
        }
        if($changeDateBtn){
            $dateSelect = false;
            $editSelect = false;
            $goBtn = false;
            $addBtn = false;
        }
        if(!$isApprovePage){
            if(!$dateSelect){
                echo 'Select Date: ';                
                displayDateSelect("dateSelect", "dateSel",false,false,true,true);
                echo '<input id="goBtn" type=submit name="goBtn" value="Go" /><br />'; 
            }
            else{
                echo '<h3>Date: '.$dateSelect.'';
                echo '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />
                    <input type="submit" name="changeDate" value="Change Date" /></h3>';
            }
        }
        if(isset($_POST['editRows'])){
            //popUpMessage($_POST['secLogRadio1']);
            $foundEditBtn = false;
            for ($i=0; $i <= $editSelect; $i++){
                if(isset($_POST['secLogRadio'.$i])){
                    $secLogID = $_POST['secLogID'.$i];
                    $foundEditBtn = true;
                }
            }
            if($foundEditBtn){
                showSecLogDetails($config, $secLogID, true, $isApprovePage); 
            }
            else if(!$addBtn && !$showAll && !$showNormal && !$changeDateBtn && !$isApprovePage){
                echo 'Error getting Reference Number!<br />';
                echo '<input type="submit" name="goBtn" value="Back To Logs" />';
            }
        }
        if($goBtn){                       
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
            showSecLogDetails($config, $secLogID);
        }
                //get group update or logout
        if($totalRows > 0){
            $approveBtn = array();
            for($i=1;$i<$totalRows;$i++){
                if(isset($_POST['logoutSecLog'.$i]) || isset($_POST['logoutSecLogAll'])){
                    $secLogID = $_POST['secLogID'.$i];
        
                    logOutSecLog($config, $secLogID);
                    
                    $editBtn = true;
                }
                else if(isset($_POST['updateSecLog'.$i]) || isset($_POST['updateSecLogAll'])){
                    //get posted values
                    $secLogID = $_POST['secLogID'.$i];
                    $radioNum = isset($_POST['radioNum'.$i]) ? $mysqli->real_escape_string($_POST['radioNum'.$i]) : '';
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
                    
                    updateSecLog($config, $secLogID, $radioNum, $address, $city, $phone, $shiftStart1, $shiftStart2, $shiftEnd1, $shiftEnd2, $dress);

                    $editBtn = true;
                }
                else if(isset($_POST['changeDeputy'.$i])){
                    $secLogID = $_POST['secLogID'.$i];
                    $radioNum = isset($_POST['radioNum'.$i]) ? $mysqli->real_escape_string($_POST['radioNum'.$i]) : '';
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
                    
                    $editBtn = true;
                }
                $approveBtn[$i] = isset($_POST['secLogApproved'.$i]) ? true : false;
                if($approveBtn[$i]){
                    $secLogID = $_POST['secLogID'.$i];
                    //get group ID from selected approval
                    $groupIDQ = "SELECT GPNUM FROM SECLOG WHERE IDNUM = ".$secLogID;
                    $result = $mysqli->query($groupIDQ);
                    SQLerrorCatch($mysqli, $result);
                    $row = $result->fetch_assoc();
                    if($row['GPNUM'] != "0"){
                        //Group Approval required
                        //get all group memebers references
                        $myq = "SELECT IDNUM 
                            FROM SECLOG
                            WHERE GPNUM = ".$row['GPNUM'].";";
                        $result = $mysqli->query($myq);
                        SQLerrorCatch($mysqli, $result);
                        while($row = $result->fetch_assoc()){
                            //approve each member of group
                            $updateQ = "UPDATE SECLOG 
                                    SET SUP_ID = '".$_SESSION['userIDnum']."',
                                        SUP_TIME = NOW(),
                                        SUP_IP = INET_ATON('".$_SERVER['REMOTE_ADDR']."') 
                                    WHERE SECLOG.IDNUM = ".$row['IDNUM'];
                            $resultUpdate = $mysqli->query($updateQ);
                            SQLerrorCatch($mysqli, $resultUpdate);
                            addLog($config, 'Secondary Employment  Log #'.$row['IDNUM'].' approved');
                            echo 'Secondary Employment Log #'.$secLogID.' approved.<br />';
                        }
                    }
                    else{
                        //approve non group secLog
                        $updateQ = "UPDATE SECLOG 
                                SET SUP_ID = '".$_SESSION['userIDnum']."',
                                    SUP_TIME = NOW(),
                                    SUP_IP = INET_ATON('".$_SERVER['REMOTE_ADDR']."') 
                                WHERE SECLOG.IDNUM = ".$secLogID;
                        $resultUpdate = $mysqli->query($updateQ);
                        SQLerrorCatch($mysqli, $resultUpdate);
                        addLog($config, 'Secondary Employment Log #'.$secLogID.' approved');
                        echo 'Secondary Employment Log #'.$secLogID.' approved.<br />';
                    }
                    showSecLog($config, $dateSelect, false, $isApprovePage=true);
                }
            }
        }
        if($isApprovePage && empty($secLogID)){
            showSecLog($config, $dateSelect, false, $isApprovePage);
        }
        if($editBtn || $updateSecLog || $logoutSecLog){
            if($config->adminLvl <= 25){
                //Non supervisor Log details
                showSecLogDetails($config, $secLogID, true, $isApprovePage);
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
    $isApprove = isset($_POST['isApprove']) ? true : $isApprove;
   
    /*query unions the results of joins on two different tables (EMPLOYEE and RESERVE)
      depending on the value of SECLOG.IS_RESERVE */
    if(!$isApprove){
        $myq =  "SELECT S.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO, 
                    TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
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

                SELECT S.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO,
                    TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
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
                ORDER BY 'gpID'";
    }
    else{
        $myq =  "SELECT S.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO, TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
                    DATE_FORMAT(SHIFTDATE,'%a %b %d %Y') 'Shiftdate', 
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
                WHERE AUDIT_OUT_ID != ''
                AND S.IS_RESERVE=0

                UNION

                SELECT S.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO, TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
                    DATE_FORMAT(SHIFTDATE,'%a %b %d %Y') 'Shiftdate',
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
                WHERE AUDIT_OUT_ID != ''
                AND S.IS_RESERVE=1
                ORDER BY 'gpID'";
        echo '<input type="hidden" name="isApprove" value="true" />';
        //echo '<input type="hidden" name="goBtn" value="true" />';
    }

    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $echo = '';
    $x=0;
    $y=0;
    if($config->adminLvl >= 0){
        //resultTable($mysqli, $result, 'false');
        $showAll = isset($_POST['showAll']) ? true : false;
        if(!$isApprove){
            if($showAll)
                echo '<div align="right"><input type="checkbox" name="showNormal" onclick="this.form.submit();" />Show Normal Logs</div>';
            else
                echo '<div align="right"><input type="checkbox" name="showAll" onclick="this.form.submit();" />Show All Logs</div>';
        }
        $theTable = array(array());
        if(!$isApprove){
            $theTable[$x][$y] = "Edit"; $y++;
        }
        else{
            $theTable[$x][$y] = "Approve"; $y++;
            $theTable[$x][$y] = "ShiftDate"; $y++;
        }
        $theTable[$x][$y] = "# in Group"; $y++;
        $theTable[$x][$y] = "Deputy"; $y++;
        $theTable[$x][$y] = "Radio#"; $y++;
        $theTable[$x][$y] = "Log In"; $y++;
        $theTable[$x][$y] = "C/Deputy"; $y++;
        $theTable[$x][$y] = "Site Name/Address"; $y++;
        $theTable[$x][$y] = "City/Twp"; $y++;
        $theTable[$x][$y] = "Contact#"; $y++;
        $theTable[$x][$y] = "Shift Start"; $y++;
        $theTable[$x][$y] = "Shift End"; $y++;
        $theTable[$x][$y] = "Dress"; $y++;
        if($config->adminLvl >=25){
            $theTable[$x][$y] = "Log Off"; $y++;
            $theTable[$x][$y] = "C/Deputy"; $y++;
            $theTable[$x][$y] = "Supervisor"; $y++;
            $theTable[$x][$y] = "Sign Off"; $y++;
        }

        $lastGroupID = '';
        $groupCounter = 0;
        while($row = $result->fetch_assoc()) {
            if($row['gpID'] == $lastGroupID && $lastGroupID != 0){
                    $gpCountSQL = $config->mysqli;
                    $gpCountq = "SELECT GPNUM FROM SECLOG WHERE GPNUM='".$row['gpID']."'";
                    $gpCountresult = $mysqli->query($gpCountq);
                    SQLerrorCatch($gpCountSQL, $gpCountresult);
                    $theTable[$x][0] .= ', '.$row['IDNUM'];
                    $theTable[$x][2] = $gpCountresult->num_rows;
            }//end if last group ID
            else{
                $groupCounter = 1;
                if(strcmp($row['TIMEOUT'], "0000") == 0 || $showAll || (strcmp($row['SUP_TIME'], "00/00/00 0000") == 0 && $isApprove)){
                    $x++;
                    if(!$isApprove)
                        $theTable[$x][0] = '<input type="submit" value="Edit/View" name="secLogRadio'.$x.'" />
                            <input type="hidden" name="secLogID'.$x.'" value="'.$row['IDNUM'].'" />';
                    else{
                        if((strcmp($row['SUP_TIME'], "00/00/00 0000") == 0))
                            $theTable[$x][0] = '<input type="submit" name="secLogApproved'.$x.'" value="Approve" />
                                <input type="hidden" name="secLogID'.$x.'" value="'.$row['IDNUM'].'" />
                                    <input type="submit" value="Edit/View" name="secLogRadio'.$x.'" />
                                    Ref# '.$row['IDNUM'];
                        else{
                            $theTable[$x][0] = 'Ref# '.$row['IDNUM'].'<input type="submit" value="Edit/View" name="secLogRadio'.$x.'" />
                            <input type="hidden" name="secLogID'.$x.'" value="'.$row['IDNUM'].'" />';
                        }
                    }
                    $y = 1;
                    if($isApprove){
                        $theTable[$x][$y] = $row['Shiftdate']; $y++;
                    }
                    $theTable[$x][$y] = $groupCounter; $y++;
                    $theTable[$x][$y] = $row['DEPUTYID']; $y++;
                    $theTable[$x][$y] = $row['RADIO']; $y++;
                    $theTable[$x][$y] = $row['TIMEIN']; $y++;
                    $theTable[$x][$y] =$row['AUDIT_IN_ID']; $y++;
                    $theTable[$x][$y] =$row['LOCATION']; $y++;
                    $theTable[$x][$y] =$row['CITY']; $y++;
                    $theTable[$x][$y] =$row['PHONE']; $y++;
                    $theTable[$x][$y] =$row['SHIFTSTART']; $y++;
                    $theTable[$x][$y] =$row['SHIFTEND']; $y++;
                    $theTable[$x][$y] =$row['DRESS']; $y++;
                    if($config->adminLvl >=25){
                        $theTable[$x][$y] =$row['TIMEOUT']; $y++;
                        $theTable[$x][$y] =$row['AUDIT_OUT_ID']; $y++;
                        $theTable[$x][$y] =$row['SUP_ID']; $y++;
                        $theTable[$x][$y] =$row['SUP_TIME']; $y++;
                    }
                    
                    $lastGroupID = $row['gpID'];
                }
            }
        }//end while loop
    }
    else{
       $showAll = isset($_POST['showAll']) ? true : false;
        if($showAll)
            echo '<div align="right"><input type="checkbox" name="showNormal" onclick="this.form.submit();" />Show Normal Logs</div>';
        else
            echo '<div align="right"><input type="checkbox" name="showAll" onclick="this.form.submit();" />Show All Logs</div>';
       $theTable = array(array());
        $theTable[$x][0] = "Edit";
        $theTable[$x][1] = "# in Group";
        $theTable[$x][2] = "Deputy";
        $theTable[$x][3] = "Radio#";
        $theTable[$x][4] = "Log In";
        $theTable[$x][5] = "C/Deputy";
        $theTable[$x][6] = "Site Name/Address";
        $theTable[$x][7] = "City/Twp";
        $theTable[$x][8] = "Contact#";
        $theTable[$x][9] = "Shift Start";
        $theTable[$x][10] = "Shift End";
    
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
        echo ' ';
    else
        echo '<input type="submit" name="addBtn" value="New Log In" />';
    
}
function showSecLogDetails($config, $secLogID, $isEditing=false, $isApprove=false){
    $addSecLog = isset($_POST['addSecLog']) ? true : false;
    $logoutSecLog = isset($_POST['logoutSecLog']) ? true : false;
    $updateSecLog = isset($_POST['updateSecLog']) ? true : false;
    $num_deputies = isset($_POST['num_deputies']) ? $_POST['num_deputies'] : 0;
    $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
    
    $mysqli = $config->mysqli;
    $mysqliReserve = connectToSQL($reserveDB = TRUE);
    
    if($addSecLog){
        //get passed values
        if($num_deputies > 0){
            for($i=0;$i<$num_deputies;$i++){
                $deputyID[$i] = isset($_POST['deputyID'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['deputyID'.$i])) : false;
                $radioNum[$i] = isset($_POST['radioNum'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['radioNum'.$i])) : '';
                $isReserve[$i] = isset($_POST['isReserve'.$i]) ? '1' : '0';
            }
        }
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
        $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;
        
        //add to database
        echo '<h2>Results</h2>';
        if($num_deputies>0){
            for($i=0;$i<$num_deputies;$i++){
                $gpIDq= "SELECT MAX( GPNUM ) 'gpID' FROM SECLOG";
                $gpResult = $mysqli->query($gpIDq);
                SQLerrorCatch($mysqli, $gpResult);
                $row = $gpResult->fetch_assoc();
                if($gpID != 0){
                    $groupID = $gpID;
                }
                else{
                    $groupID = 0;
                    if($num_deputies == 1){
                        //Set Group ID to 0 or Individual
                    }
                    else if($i==0){
                        $groupID = $row['gpID']+1;
                    }
                    else{
                        $groupID = $row['gpID'];
                    }
                }
                $myq = "INSERT INTO `SECLOG` ( `IDNUM` ,`DEPUTYID` ,`RADIO` ,`TIMEIN` ,`AUDIT_IN_ID` ,
                    `AUDIT_IN_TIME` ,`AUDIT_IN_IP` ,`LOCATION` ,`CITY` ,`PHONE` ,`SHIFTDATE` ,`SHIFTSTART` ,
                    `SHIFTEND` ,`DRESS` ,`TIMEOUT` ,`AUDIT_OUT_ID` ,`AUDIT_OUT_TIME` ,`AUDIT_OUT_IP` ,`SUP_ID` ,
                    `SUP_TIME` ,`SUP_IP`, IS_RESERVE, GPNUM) VALUES (
                    NULL , '".$deputyID[$i]."', '".$radioNum[$i]."', NOW(), '".$_SESSION['userIDnum']."', NOW(), INET_ATON('".$_SERVER['REMOTE_ADDR']."'), 
                        '".$address."', '".$city."', '".$phone."', '".Date('Y-m-d', strtotime($_POST['dateSelect']))."', 
                        '".$shiftStart."', '".$shiftEnd."', '".$dress."', '', '', '', '', '', '', '',".$isReserve[$i].",
                    '".$groupID."');";
                $result = $mysqli->query($myq);
                if(!SQLerrorCatch($mysqli, $result)) {
                    $secLogID = $mysqli->insert_id;  
                    addLog($config, 'Secondary Log #'.$secLogID.' Added');
                    echo 'Successfully Added Secondary Employment Log, Reference Number: '.$secLogID.'<br />';
                    $isEditing = true;
                }
                else
                    echo 'Failed to add Secondary Employment Log, try again.<br />';
            }
        }
        else{
            echo 'Must select a user.<br />';
        }
        echo '<br />';

        //display results and get secLogID just added
    }
    if($logoutSecLog){
        $secLogID = isset($_POST['secLogID']) ? $_POST['secLogID'] : '';
        logOutSecLog($config, $secLogID);
        $isEditing = true;
    }
    
    if($updateSecLog){
        ////get posted values
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
        updateSecLog($config, $secLogID, $radioNum, $address, $city, $phone, $shiftStart1, $shiftStart2, $shiftEnd1, $shiftEnd2, $dress);
        $isEditing = true;
    }
    
    if($isEditing){
        if($config->adminLvl >= 0){
            $mysqli = $config->mysqli;
            $myq = "SELECT S.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', S.RADIO, LOCATION, S.CITY, PHONE,
                        SHIFTSTART, SHIFTEND, DRESS, S.IDNUM, S.TIMEOUT
                    FROM SECLOG S
                    JOIN EMPLOYEE AS SEC ON SEC.IDNUM=S.DEPUTYID
                    WHERE S.IDNUM = '".$secLogID."' AND IS_RESERVE=0
                    UNION
                    SELECT S.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', S.RADIO, LOCATION, S.CITY, PHONE,
                        SHIFTSTART, SHIFTEND, DRESS, S.IDNUM, S.TIMEOUT
                    FROM SECLOG S
                    JOIN RESERVE AS SEC ON SEC.IDNUM=S.DEPUTYID
                    WHERE S.IDNUM = '".$secLogID."' AND IS_RESERVE=1
                    ORDER BY IDNUM";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            $row = $result->fetch_assoc();
            if($row['gpID'] != 0){
                //get all users
                echo '<div align="center">Group Reference #: '.$row['gpID'].'
                    <input type="hidden" name="gpID" value="'.$row['gpID'].'" /></div>';
                $newq = "SELECT S.IDNUM 'refNum', S.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', S.RADIO, LOCATION, S.CITY, PHONE,
                        SHIFTSTART, SHIFTEND, DRESS, S.IDNUM, S.TIMEOUT
                    FROM SECLOG S
                    JOIN EMPLOYEE AS SEC ON SEC.IDNUM=S.DEPUTYID
                    WHERE S.GPNUM = '".$row['gpID']."' AND IS_RESERVE=0
                    UNION
                    SELECT S.IDNUM 'refNum', S.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', S.RADIO, LOCATION, S.CITY, PHONE,
                        SHIFTSTART, SHIFTEND, DRESS, S.IDNUM, S.TIMEOUT
                    FROM SECLOG S
                    JOIN RESERVE AS SEC ON SEC.IDNUM=S.DEPUTYID
                    WHERE S.GPNUM = '".$row['gpID']."' AND IS_RESERVE=1
                    ORDER BY IDNUM";
                $newResult = $mysqli->query($newq);
                SQLerrorCatch($mysqli, $newResult);
                
                $x=0;
                $y=0;
                $depTable = array(array());
                $depTable[$x][$y] = "Reference#"; $y++;
                $depTable[$x][$y] = "Deputy"; $y++;
                $depTable[$x][$y] = "Radio#"; $y++;
                $depTable[$x][$y] = "Action"; $y++;
                
                $x++;
                while($newRow = $newResult->fetch_assoc()){
                    $y=0;
                    $depTable[$x][$y] = $newRow['refNum'].'
                        <input type="hidden" name="secLogID'.$x.'" value="'.$newRow['refNum'].'" />'; $y++;
                    $depTable[$x][$y] = $newRow['DEPUTYNAME']; $y++;
                    $depTable[$x][$y] = '<input type="text" name="radioNum'.$x.'" value="'.$newRow['RADIO'].'" />'; $y++;
                    if(strcmp($newRow['TIMEOUT'],"00:00:00")==0){
                        $depTable[$x][$y] = '<input type="submit" value="Update" name="updateSecLog'.$x.'" />
                                <input type="submit" value="LogOut" name="logoutSecLog'.$x.'" /><br/>'; 
                        if($config->adminLvl >=25){
                            $depTable[$x][$y] .= '<input type="submit" name="changeDeputy'.$x.'" value="Change Deputy" />';
                        }
                        $y++;
                    }
                    else{
                        if($config->adminLvl >=25){
                            $depTable[$x][$y] = '<input type="submit" value="Update" name="updateSecLog'.$x.'" />
                                Logged Out at '.$newRow['TIMEOUT'];$y++;  
                        }
                        else{
                           $depTable[$x][$y] = 'Logged Out at '.$newRow['TIMEOUT'];$y++;  
                        }
                    }
                    $x++;
                }
                showSortableTable($depTable, 1);

            }
            else{
                $x=0;
                $y=0;
                $depTable = array(array());
                $depTable[$x][$y] = "Reference#"; $y++;
                $depTable[$x][$y] = "Deputy"; $y++;
                $depTable[$x][$y] = "Radio#"; $y++;
                $depTable[$x][$y] = "Action"; $y++;
                
                $x++;
                $y=0;
                $depTable[$x][$y] = $secLogID.'<input type="hidden" name="secLogID" value="'.$secLogID.'" />'; $y++;
                $depTable[$x][$y] = $row['DEPUTYNAME']; $y++;
                $depTable[$x][$y] = $row['DEPUTYNAME']; $y++;
                if($config->adminLvl >= 25)
                    $depTable[$x][$y] = '<input type="submit" name="changeDeputy1" value="Change Deputy" />'; 
                else 
                    $depTable[$x][$y] = '';
                $y++;
                
                showSortableTable($depTable, 1);
//                 echo 'Reference #: '.$secLogID.'<input type="hidden" name="secLogID" value="'.$secLogID.'" /><br />
//                    Deputy: '.$row['DEPUTYNAME'].'<br/>
//                    Radio#: <input type="text" name="radioNum" value="'.$row['RADIO'].'" />
//                        <input type="submit" name="changeDeputy1" value="Change Deputy" /><br/>';
            }
            echo '<div align="left">Add Deputy: <button type="button"  name="searchBtn" 
                value="Lookup Employee" onClick="this.form.action=' . "'?userLookup=true'" . ';this.form.submit()" >
                Lookup Employee</button></div><br/>';
            echo 'Site Name or Address: <input type="text" name="address" value="'.$row['LOCATION'].'" /><br/>
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
                if($row['gpID'] != 0){
                    echo '<input type="submit" name="logoutSecLogAll" value="LogOut All" />';
                }
                else{
                    echo '<input type="submit" name="logoutSecLog" value="LogOut" />';
                }
            }
            else{
                echo $row['TIMEOUT'].'<br /><br />';
            }
            if(strcmp($row['TIMEOUT'],"00:00:00")==0 || $config->adminLvl >=25){
                if($row['gpID'] != 0)
                    echo '<input type="submit" name="updateSecLogAll" value="Update All" />';
                else
                    echo '<input type="submit" name="updateSecLog" value="Update" />';
            }
            if($isApprove)
                echo '<input type="submit" name="backToApprove" value="Back To Approvals" />';
            else
                echo '<input type="submit" name="goBtn" value="Back To Logs" />';
        }
        else{
            echo 'Access Denied';
        }
    }
    if(!$isEditing && !isset($_POST['goBtn'])){
        $secLogID = isset($_POST['secLogID']) ? $_POST['secLogID'] :$_SESSION['userIDnum'] ;
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

        //debug
        //var_dump($_POST);
        //Show previously added deputies
        $deputyCount=0;
        if($num_deputies > 0){
            for($i=0;$i<$num_deputies;$i++){
                if(!isset($_POST['removeDeputyBtn'.$i])){
                    $deputyID[$i] = isset($_POST['deputyID'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['deputyID'.$i])) : '';
                    $isReserve[$i] = isset($_POST['isReserve'.$i]) ? true : false;

                    //get this user's information
                    if($isReserve[$i]){
                        $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM RESERVE WHERE IDNUM='.$deputyID[$i];
                        $result = $mysqliReserve->query($myq);
                        SQLerrorCatch($mysqliReserve, $result);
                        $row = $result->fetch_assoc();
                    }
                    else{
                        $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM EMPLOYEE WHERE IDNUM='.$deputyID[$i];
                        $result = $mysqli->query($myq);
                        SQLerrorCatch($mysqli, $result);
                        $row = $result->fetch_assoc();
                    }  
                    if($i==0)
                        $phone = $row['CELLPH'];
                    echo 'Deputy: <input type="hidden" name="deputyID'.$deputyCount.'" value="'.$deputyID[$i].'" />';
                    if($isReserve[$i]==1)
                        echo '<input type="hidden" name="isReserve'.$deputyCount.'" value="true" />';
                    echo $row['LNAME'] . ', ' . $row['FNAME'];
                    echo ';  Radio#: <input type="hidden" name="radioNum'.$deputyCount.'" value="'.$row['RADIO'].'" />'.$row['RADIO'];
                    echo '<input type="submit" name="removeDeputyBtn'.$deputyCount.'" value="Remove" />';
                    echo '<br/>';
                    $deputyCount++;
                }
            }//End for loop of previously added deputies
        }//End check for multiple deputies
        
        //Get added Deputy
        $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
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
                            $foundUserIsReserve = true;
                    else
                        $foundUserIsReserve = false;
                    break;
                }//end if
            }//end for
        }
        if(empty($foundUserID) && $num_deputies == 0){
            if($_SERVER['REMOTE_ADDR'] != '10.1.32.72'/*nslookup('mcjcbcast.sheriff.mahoning.local')*/){
                $foundUserID = $_SESSION['userIDnum'];
                $foundUserIsReserve = false;
            }
        }
        if(!empty($foundUserID)){
            if($foundUserIsReserve){
                $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM RESERVE WHERE IDNUM='.$foundUserID;
                $result = $mysqliReserve->query($myq);
                SQLerrorCatch($mysqliReserve, $result);
            }
            else{
                $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM EMPLOYEE WHERE IDNUM='.$foundUserID;
                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result);
            }

            $row = $result->fetch_assoc();
            if($deputyCount==0)
                $phone = $row['CELLPH'];
            echo 'Deputy: <input type="hidden" name="deputyID'.$deputyCount.'" value="'.$foundUserID.'" />';
            if($foundUserIsReserve)
                echo '<input type="hidden" name="isReserve'.$deputyCount.'" value="true" />';
            echo $row['LNAME'] . ', ' . $row['FNAME'];
            echo ';  Radio#: <input type="hidden" name="radioNum'.$deputyCount.'" value="'.$row['RADIO'].'" />'.$row['RADIO'];
            echo '<input type="submit" name="removeDeputyBtn'.$deputyCount.'" value="Remove" />';
            echo '<br/>';
            $deputyCount++;
        }
        echo 'Add Deputy: ';
        displayUserLookup($config);
        echo '<br />';
        echo '<input type="hidden" name="num_deputies" value="'.$deputyCount.'" />';
           
        $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;
        echo '<input type="hidden" name="gpID" value="'.$gpID.'" />';
        echo 'Site Name or Address: <input type="text" name="address" value="'.$address.'" /><br/>
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
function logOutSecLog($config, $secLogID){
    $mysqli = $config->mysqli;

    $myq = "UPDATE `SECLOG` SET `TIMEOUT` = NOW( ) ,
        `AUDIT_OUT_ID` = '".$_SESSION['userIDnum']."', `AUDIT_OUT_TIME` = NOW( ) ,
        `AUDIT_OUT_IP` = INET_ATON('".$_SERVER['REMOTE_ADDR']."') WHERE `SECLOG`.`IDNUM` = ".$secLogID." LIMIT 1 ;";
    $result = $mysqli->query($myq);
    if(!SQLerrorCatch($mysqli, $result)){
            echo 'Successfully Logged Out Reference Number: '.$secLogID.'<br />';
            addLog($config, 'Secondary Log #'.$secLogID.' Logged Out');
    }
    else
        echo '<h2>Results</h2>Failed to logout Secondary Employment Log, try again.<br /><Br />';  
}
function updateSecLog($config, $secLogID, $radioNum, $address, $city, $phone, $shiftStart1,
        $shiftStart2,$shiftEnd1,$shiftEnd2,$dress){
    $mysqli = $config->mysqli;

    $shiftStart = $shiftStart1.$shiftStart2."00";
    $shiftEnd = $shiftEnd1.$shiftEnd2."00";

    $myq = "UPDATE SECLOG 
            SET RADIO = '".$radioNum."', LOCATION = '".$address."', 
                CITY = '".$city."', PHONE ='".$phone."', 
                SHIFTSTART = '".$shiftStart."', SHIFTEND = '".$shiftEnd."', 
                DRESS = '".$dress."' 
            WHERE IDNUM =".$secLogID;
    $result = $mysqli->query($myq);
    if(!SQLerrorCatch($mysqli, $result)){
            echo 'Successfully Updated Log #'.$secLogID.'<br />';
            addLog($config, 'Secondary Log #'.$secLogID.' Modified');
    }
    else
        echo '<h2>Results</h2>Failed to update Secondary Employment Log, try again.<br /><Br />';

}

function displaySecLogReport($config){
    echo '<h2>Secondary Employement Logs Reports By Date</h2>';
    
    if($config->adminLvl >=25){
        $dateSelect = isset($_POST['dateSelect']) ? $_POST['dateSelect'] : false;
        //popupmessage("isset: ".isset($_POST['dateSelect']).' to '.$_POST['dateSelect']);
        echo '<form method="POST" name="secLog">';
        
        if(!$dateSelect){
            $dateSelect = Date('m/d/Y', time());
            echo 'Date: '; 
            //echo '<input name="dateSelect" type="text" value="'.$dateSelect.'" />';
            displayDateSelect("dateSelect", "dateSel",false,false,true,true);
            echo '<input id="goBtn" type=submit name="goBtn" value="Go" /><br />'; 
        }
        else{
            echo '<h3>Date: ';
            displayDateSelect("dateSelect", "dateSel",$dateSelect,false,false,true);
            echo '<input id="goBtn" type=submit name="goBtn" value="Go" /><br />';
        }

        $mysqli = $config->mysqli;

        /*query unions the results of joins on two different tables (EMPLOYEE and RESERVE)
          depending on the value of SECLOG.IS_RESERVE */

        $myq =  "SELECT S.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO, 
                    TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
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

                SELECT S.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', S.RADIO,
                    TIME_FORMAT(TIMEIN,'%H%i') 'TIMEIN',
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
                ORDER BY 'gpID'";


        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result, $myq, $debug=false);
        $echo = '';
        $x=0;
        $y=0;
        //resultTable($mysqli, $result, 'false');
        $showAll = true;

        $theTable = array(array());

        $theTable[$x][$y] = "Action"; $y++;
        $theTable[$x][$y] = "# in Group"; $y++;
        $theTable[$x][$y] = "Deputy"; $y++;
        $theTable[$x][$y] = "Radio#"; $y++;
        $theTable[$x][$y] = "Log In"; $y++;
        $theTable[$x][$y] = "C/Deputy"; $y++;
        $theTable[$x][$y] = "Site Name/Address"; $y++;
        $theTable[$x][$y] = "City/Twp"; $y++;
        $theTable[$x][$y] = "Contact#"; $y++;
        $theTable[$x][$y] = "Shift Start"; $y++;
        $theTable[$x][$y] = "Shift End"; $y++;
        $theTable[$x][$y] = "Dress"; $y++;
        $theTable[$x][$y] = "Log Off"; $y++;
        $theTable[$x][$y] = "C/Deputy"; $y++;
        $theTable[$x][$y] = "Supervisor"; $y++;
        $theTable[$x][$y] = "Sign Off"; $y++;
 

        $lastGroupID = '';
        $groupCounter = 0;
        while($row = $result->fetch_assoc()) {
            if($row['gpID'] == $lastGroupID && $lastGroupID != 0){
                    $gpCountSQL = $config->mysqli;
                    $gpCountq = "SELECT GPNUM FROM SECLOG WHERE GPNUM='".$row['gpID']."'";
                    $gpCountresult = $mysqli->query($gpCountq);
                    SQLerrorCatch($gpCountSQL, $gpCountresult);
                    $theTable[$x][0] .= ', '.$row['IDNUM'];
                    $theTable[$x][2] = $gpCountresult->num_rows;
            }//end if last group ID
            else{
                $groupCounter = 1;
                if(strcmp($row['TIMEOUT'], "0000") == 0 || $showAll || (strcmp($row['SUP_TIME'], "00/00/00 0000") == 0)){
                    $x++;

                    if((strcmp($row['SUP_TIME'], "00/00/00 0000") == 0)){
//                        $theTable[$x][0] = '<input type="submit" name="secLogApproved'.$x.'" value="Approve" />
//                            <input type="hidden" name="secLogID'.$x.'" value="'.$row['IDNUM'].'" />
//                                <input type="submit" value="Edit/View" name="secLogRadio'.$x.'" />';
                        $theTable[$x][0] = 'Ref# '.$row['IDNUM'];
                    }
                    else{
                        $theTable[$x][0] = 'Ref# '.$row['IDNUM'];
                        //$theTable[$x][0] .= '<input type="submit" value="Edit/View" name="secLogRadio'.$x.'" />
                        //<input type="hidden" name="secLogID'.$x.'" value="'.$row['IDNUM'].'" />';
                    }

                    $y = 1;
                    $theTable[$x][$y] = $groupCounter; $y++;
                    $theTable[$x][$y] = $row['DEPUTYID']; $y++;
                    $theTable[$x][$y] = $row['RADIO']; $y++;
                    $theTable[$x][$y] = $row['TIMEIN']; $y++;
                    $theTable[$x][$y] =$row['AUDIT_IN_ID']; $y++;
                    $theTable[$x][$y] =$row['LOCATION']; $y++;
                    $theTable[$x][$y] =$row['CITY']; $y++;
                    $theTable[$x][$y] =$row['PHONE']; $y++;
                    $theTable[$x][$y] =$row['SHIFTSTART']; $y++;
                    $theTable[$x][$y] =$row['SHIFTEND']; $y++;
                    $theTable[$x][$y] =$row['DRESS']; $y++;
                    $theTable[$x][$y] =$row['TIMEOUT']; $y++;
                    $theTable[$x][$y] =$row['AUDIT_OUT_ID']; $y++;
                    $theTable[$x][$y] =$row['SUP_ID']; $y++;
                    $theTable[$x][$y] =$row['SUP_TIME']; $y++;
                    
                    $lastGroupID = $row['gpID'];
                }
            }
        }//end while loop
        showSortableTable($theTable, 3);
        $echo .= '<input type="hidden" name="editRows" value="'.$x.'" />';

        echo $echo;
    }
    else{
        echo 'Access Denied';
    }
}

?>
