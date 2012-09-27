<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function displayRadioLog($config, $isApprovePage = false){
    if($config->adminLvl >= 25){
        $mysqli = $config->mysqli;
        if($isApprovePage)
            echo '<h2>Daily Radio Checkout Log Approval</h2>';
        else
            echo '<h2>Daily Radio Checkout Log</h2>';

        echo '<form name="radioLog" method="POST">
            <input type="hidden" name="formName" value="radioLog" />'; 

            //Get variables
            $dateSelect = isset($_POST['dateSelect']) ? $_POST['dateSelect'] : false;
            $changeDateBtn = isset($_POST['changeDate']) ? True : false;
            $editSelect = isset($_POST['editRows']) ? $_POST['editRows'] : false;
            $addBtn = isset($_POST['addBtn']) ? True : false;
            $editBtn = isset($_POST['editBtn']) ? True : false;
            $radioLogID = isset($_POST['radioLogID']) ? $_POST['radioLogID'] : false;
            $rowNum = isset($_POST['rowNum ']) ? $_POST['rowNum '] : false;
            $checkInRadio = isset($_POST['checkInRadio']) ? true : false;
            $updateRadioLog = isset($_POST['updateRadioLog']) ? true : false;
            $showAll = isset($_POST['showAll']) ? true : false;
            $showNormal = isset($_POST['showNormal']) ?  true : false;
            $goBtn = isset($_POST['goBtn']) ? true : false;
            $isApprovePage = isset($_GET['secApprove']) ? true : $isApprovePage;
            $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
            $radioLogID = isset($_POST['backToApprove']) ? false : $radioLogID ;

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
                    if(isset($_POST['radioLogEditBtn'.$i])){
                        $radioLogID = $_POST['radioLogID'.$i];
                        $foundEditBtn = true;
                    }
                }
                if($foundEditBtn){
                    showRadioLogDetails($config, $radioLogID, true, $isApprovePage); 
                }
                else if(!$addBtn && !$showAll && !$showNormal && !$changeDateBtn && !$isApprovePage){
                    echo 'Error getting Reference Number!<br />';
                    echo '<input type="submit" name="goBtn" value="Back To Logs" />';
                }
            }
            if($goBtn){                       
                if($config->adminLvl < 25){
                    //non supervisor logs
                    showRadioLog($config, $dateSelect, false);
                }
                else{
                    //supervisor logs
                    showRadioLog($config, $dateSelect, true);
                }           
            }        
            if($addBtn){
                showRadioLogDetails($config, $radioLogID);
            }
                    //get group update or logout
            if($totalRows > 0){
                $approveBtn = array();
                for($i=1;$i<$totalRows;$i++){
                    if(isset($_POST['logoutRadioLog'.$i]) || isset($_POST['logoutRadioLogAll'])){
                        $radioLogID = $_POST['radioLogID'.$i];

                        checkInRadioLog($config, $radioLogID);

                        $editBtn = true;
                    }
                    else if(isset($_POST['updateRadioLog'.$i]) || isset($_POST['updateRadioLogAll'])){
                        //get posted values
                        $radioLogID = $_POST['radioLogID'.$i];
                        $radioCallNum = isset($_POST['radioCallNum'.$i]) ? $mysqli->real_escape_string($_POST['radioCallNum'.$i]) : '';
                        $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string($_POST['checkOutType']) : '';

                        updateRadioLog($config, $radioLogID, $radioCallNum, $checkOutType);

                        $editBtn = true;
                    }
                    $approveBtn[$i] = isset($_POST['radioLogApproved'.$i]) ? true : false;
                    if($approveBtn[$i]){
                        $radioLogID = $_POST['radioLogID'.$i];
                        //get group ID from selected approval
                        $groupIDQ = "SELECT GPNUM FROM WTS_RADIOLOG WHERE REFNUM = ".$radioLogID;
                        $result = $mysqli->query($groupIDQ);
                        SQLerrorCatch($mysqli, $result);
                        $row = $result->fetch_assoc();
                        if($row['GPNUM'] != "0"){
                            //Group Approval required
                            //get all group memebers references
                            $myq = "SELECT REFNUM 
                                FROM WTS_RADIOLOG
                                WHERE GPNUM = ".$row['GPNUM'].";";
                            $result = $mysqli->query($myq);
                            SQLerrorCatch($mysqli, $result);
                            while($row = $result->fetch_assoc()){
                                //approve each member of group
                                $updateQ = "UPDATE WTS_RADIOLOG
                                        SET SUP_ID = '".$_SESSION['userIDnum']."',
                                            SUP_TS = NOW(),
                                            SUP_IP = INET_ATON('".$_SERVER['REMOTE_ADDR']."') 
                                        WHERE WTS_RADIOLOG.REFNUM = ".$row['REFNUM'];
                                $resultUpdate = $mysqli->query($updateQ);
                                SQLerrorCatch($mysqli, $resultUpdate);
                                addLog($config, 'Radio Checkout Log #'.$row['REFNUM'].' approved');
                                echo 'Radio Checkout Log #'.$radioLogID.' approved.<br />';
                            }
                        }
                        else{
                            //approve non group secLog
                            $updateQ = "UPDATE WTS_RADIOLOG 
                                    SET SUP_ID = '".$_SESSION['userIDnum']."',
                                        SUP_TS = NOW(),
                                        SUP_IP = INET_ATON('".$_SERVER['REMOTE_ADDR']."') 
                                    WHERE WTS_RADIOLOG.REFNUM = ".$radioLogID;
                            $resultUpdate = $mysqli->query($updateQ);
                            SQLerrorCatch($mysqli, $resultUpdate);
                            addLog($config, 'Radio Checkout Log #'.$radioLogID.' approved');
                            echo 'Radio Checkout Log #'.$radioLogID.' approved.<br />';
                        }
                        showRadioLog($config, $dateSelect, false, $isApprovePage=true);
                    }
                }
            }
            if($isApprovePage && empty($radioLogID)){
                showRadioLog($config, $dateSelect, false, $isApprovePage);
            }
            if($editBtn || $updateRadioLog || $checkInRadio){
                if($config->adminLvl <= 25){
                    //Non supervisor Log details
                    showRadioLogDetails($config, $radioLogID, true, $isApprovePage);
                }
                else{
                    //Supervisor Log Details
                    showRadioLogDetails($config, $radioLogID, true);
                }
            }
            ?>
        </form>
        <br />
        <br />
        <?php
    }
    else{
        echo '<h2>Daily Radio Checkout Log</h2>Access Denied';
    }
    
}

function showRadioLog($config, $dateSelect, $radioLogID, $isApprove=false){
    $mysqli = $config->mysqli;
    $isApprove = isset($_POST['isApprove']) ? true : $isApprove;
   
    /*query unions the results of joins on two different tables (EMPLOYEE and RESERVE)
      depending on the value of SECLOG.IS_RESERVE */
    if(!$isApprove){
        $myq =  "SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
                    INV.OTHER_SN, R.RADIO_CALLNUM, R.TYPE 'checkOutType', R.CHECKEDOUT 'isCheckedOut',
                    DATE_FORMAT(R.AUDIT_OUT_TS,'%m/%d/%y %H%i') 'checkOut',
                    CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID',
                    DATE_FORMAT(R.AUDIT_IN_TS,'%m/%d/%y %H%i') 'checkIn', 
                    CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', 
                    CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', DATE_FORMAT(SUP_TS,'%m/%d/%y %H%i') 'SUP_TIME'
                FROM WTS_RADIOLOG R
                INNER JOIN EMPLOYEE AS SEC ON R.DEPUTYID=SEC.IDNUM
                LEFT JOIN EMPLOYEE AS LOGOUT ON R.AUDIT_OUT_ID=LOGOUT.IDNUM
                LEFT JOIN EMPLOYEE AS LOGIN ON R.AUDIT_IN_ID=LOGIN.IDNUM
                LEFT JOIN EMPLOYEE AS SUP ON R.SUP_ID=SUP.IDNUM
                LEFT JOIN WTS_INVENTORY AS INV ON R.RADIOID=INV.IDNUM
                WHERE AUDIT_OUT_TS LIKE '%".Date('Y-m-d', strtotime($dateSelect))."%'
                AND R.IS_RESERVE=0

                UNION

                SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
                    INV.OTHER_SN, R.RADIO_CALLNUM, R.TYPE 'checkOutType', R.CHECKEDOUT 'isCheckedOut',
                    DATE_FORMAT(R.AUDIT_OUT_TS,'%m/%d/%y %H%i') 'checkOut',
                    CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID',
                    DATE_FORMAT(R.AUDIT_IN_TS,'%m/%d/%y %H%i') 'checkIn', 
                    CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', 
                    CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', DATE_FORMAT(SUP_TS,'%m/%d/%y %H%i') 'SUP_TIME'
                FROM WTS_RADIOLOG R
                INNER JOIN RESERVE AS SEC ON R.DEPUTYID=SEC.IDNUM
                LEFT JOIN EMPLOYEE AS LOGOUT ON R.AUDIT_OUT_ID=LOGOUT.IDNUM
                LEFT JOIN EMPLOYEE AS LOGIN ON R.AUDIT_IN_ID=LOGIN.IDNUM
                LEFT JOIN EMPLOYEE AS SUP ON R.SUP_ID=SUP.IDNUM
                LEFT JOIN WTS_INVENTORY AS INV ON R.RADIOID=INV.IDNUM
                WHERE AUDIT_OUT_TS LIKE '%".Date('Y-m-d', strtotime($dateSelect))."%'
                AND R.IS_RESERVE=1
                ORDER BY 'gpID'";
    }
    else{
        //Querey used for approvals.  
        $myq =  "SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
                    INV.OTHER_SN, R.RADIO_CALLNUM, R.TYPE 'checkOutType', R.CHECKEDOUT 'isCheckedOut',
                    DATE_FORMAT(R.AUDIT_OUT_TS,'%m/%d/%y %H%i') 'checkOut',
                    CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID',
                    DATE_FORMAT(R.AUDIT_IN_TS,'%m/%d/%y %H%i') 'checkIn', 
                    CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', 
                    CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', DATE_FORMAT(SUP_TS,'%m/%d/%y %H%i') 'SUP_TIME'
                FROM WTS_RADIOLOG R
                INNER JOIN EMPLOYEE AS SEC ON R.DEPUTYID=SEC.IDNUM
                LEFT JOIN EMPLOYEE AS LOGOUT ON R.AUDIT_OUT_ID=LOGOUT.IDNUM
                LEFT JOIN EMPLOYEE AS LOGIN ON R.AUDIT_IN_ID=LOGIN.IDNUM
                LEFT JOIN EMPLOYEE AS SUP ON R.SUP_ID=SUP.IDNUM
                LEFT JOIN WTS_INVENTORY AS INV ON R.RADIOID=INV.IDNUM
                WHERE AUDIT_IN_ID != ''
                AND R.IS_RESERVE=0

                UNION

                SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
                    INV.OTHER_SN, R.RADIO_CALLNUM, R.TYPE 'checkOutType', R.CHECKEDOUT 'isCheckedOut',
                    DATE_FORMAT(R.AUDIT_OUT_TS,'%m/%d/%y %H%i') 'checkOut',
                    CONCAT_WS(', ',LOGOUT.LNAME,LOGOUT.FNAME) 'AUDIT_OUT_ID',
                    DATE_FORMAT(R.AUDIT_IN_TS,'%m/%d/%y %H%i') 'checkIn', 
                    CONCAT_WS(', ',LOGIN.LNAME,LOGIN.FNAME) 'AUDIT_IN_ID', 
                    CONCAT_WS(', ',SUP.LNAME,SUP.FNAME) 'SUP_ID', DATE_FORMAT(SUP_TS,'%m/%d/%y %H%i') 'SUP_TIME'
                FROM WTS_RADIOLOG R
                INNER JOIN RESERVE AS SEC ON R.DEPUTYID=SEC.IDNUM
                LEFT JOIN EMPLOYEE AS LOGOUT ON R.AUDIT_OUT_ID=LOGOUT.IDNUM
                LEFT JOIN EMPLOYEE AS LOGIN ON R.AUDIT_IN_ID=LOGIN.IDNUM
                LEFT JOIN EMPLOYEE AS SUP ON R.SUP_ID=SUP.IDNUM
                LEFT JOIN WTS_INVENTORY AS INV ON R.RADIOID=INV.IDNUM
                WHERE AUDIT_IN_ID != ''
                AND R.IS_RESERVE=1
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
        }
        $theTable[$x][$y] = "Radio#"; $y++;
        $theTable[$x][$y] = "# in Group"; $y++;
        $theTable[$x][$y] = "Deputy"; $y++;
        $theTable[$x][$y] = "Radio Call#"; $y++;
        $theTable[$x][$y] = "Type"; $y++;
        $theTable[$x][$y] = "OUT_Time"; $y++;
        $theTable[$x][$y] = "OUT_Out_By"; $y++;
        $theTable[$x][$y] = "In_Time"; $y++;
        $theTable[$x][$y] = "In_By"; $y++;
        $theTable[$x][$y] = "Status"; $y++;

        if($config->adminLvl >=25){
            $theTable[$x][$y] = "Supervisor"; $y++;
            $theTable[$x][$y] = "Sign_Off"; $y++;
        }

        $lastGroupID = '';
        $groupCounter = 0;
        while($row = $result->fetch_assoc()) {
            if($row['gpID'] == $lastGroupID && $lastGroupID != 0){
                    $gpCountSQL = $config->mysqli;
                    $gpCountq = "SELECT GPNUM FROM WTS_RADIOLOG WHERE GPNUM='".$row['gpID']."'";
                    $gpCountresult = $mysqli->query($gpCountq);
                    SQLerrorCatch($gpCountSQL, $gpCountresult);
                    $theTable[$x][0] .= ', '.$row['REFNUM'];
                    $theTable[$x][2] = $gpCountresult->num_rows;
            }//end if last group ID
            else{
                $groupCounter = 1;
                if(strcmp($row['checkIn'], "00/00/00 0000") == 0 || $showAll || (strcmp($row['SUP_TIME'], "00/00/00 0000") == 0 && $isApprove)){
                    $x++;
                    if(!$isApprove)
                        $theTable[$x][0] = '<input type="submit" value="Edit/View" name="radioLogEditBtn'.$x.'" />
                            <input type="hidden" name="radioLogID'.$x.'" value="'.$row['REFNUM'].'" />';
                    else{
                        if((strcmp($row['SUP_TIME'], "00/00/00 0000") == 0))
                            $theTable[$x][0] = '<input type="submit" name="radioLogApproved'.$x.'" value="Approve" />
                                <input type="hidden" name="radioLogID'.$x.'" value="'.$row['REFNUM'].'" />
                                    <input type="submit" value="Edit/View" name="radioLogRadio'.$x.'" />
                                    Ref# '.$row['REFNUM'];
                        else{
                            $theTable[$x][0] = 'Ref# '.$row['REFNUM'].'<input type="submit" value="Edit/View" name="radioLogRadio'.$x.'" />
                            <input type="hidden" name="radioLogID'.$x.'" value="'.$row['REFNUM'].'" />';
                        }
                    }
                    $y = 1;
                    
                    $theTable[$x][$y] = $row['OTHER_SN']; $y++;
                    $theTable[$x][$y] = $groupCounter; $y++;
                    $theTable[$x][$y] = $row['DEPUTYID']; $y++;
                    $theTable[$x][$y] = $row['RADIO_CALLNUM']; $y++;
                    $theTable[$x][$y] = $row['checkOutType']; $y++;
                    $theTable[$x][$y] =$row['checkOut']; $y++;
                    $theTable[$x][$y] =$row['AUDIT_OUT_ID']; $y++;
                    $theTable[$x][$y] =$row['checkIn']; $y++;
                    $theTable[$x][$y] =$row['AUDIT_IN_ID']; $y++;
                    if($row['isCheckedOut'] == 1){
                        $theTable[$x][$y] = "Checked Out"; $y++;
                    }
                    else{
                        $theTable[$x][$y] = "Available"; $y++;
                    }  
                    if($config->adminLvl >=25){
                        $theTable[$x][$y] =$row['SUP_ID']; $y++;
                        $theTable[$x][$y] =$row['SUP_TIME']; $y++;
                    }
                    
                    $lastGroupID = $row['gpID'];
                }
            }
        }//end while loop
    }

    showSortableTable($theTable, 3);
    $echo .= '<input type="hidden" name="editRows" value="'.$x.'" />';
    $echo .= '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />';
    
    echo $echo;
    if($isApprove)
        echo ' ';
    else
        echo '<input type="submit" name="addBtn" value="Checkout Radio" />';
    
}
function showRadioLogDetails($config, $radioLogID, $isEditing=false, $isApprove=false){
    $checkOutRadio = isset($_POST['addRadioLog']) ? true : false;
    $checkInRadio = isset($_POST['checkInRadio']) ? true : false;
    $updateRadioLog = isset($_POST['updateRadioLog']) ? true : false;
    $num_deputies = isset($_POST['num_deputies']) ? $_POST['num_deputies'] : 0;
    $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
    
    $mysqli = $config->mysqli;
    $mysqliReserve = connectToSQL($reserveDB = TRUE);
    
    if($checkOutRadio){
        //get passed values
        if($num_deputies > 0){
            for($i=0;$i<$num_deputies;$i++){
                $deputyID[$i] = isset($_POST['deputyID'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['deputyID'.$i])) : false;
                $radioCallNum[$i] = isset($_POST['radioCallNum'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['radioCallNum'.$i])) : '';
                $isReserve[$i] = isset($_POST['isReserve'.$i]) ? '1' : '0';
            }
        }
        $radioID = isset($_POST['radioID']) ? $mysqli->real_escape_string(strtoupper($_POST['radioID'])) : '';
        $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
        $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;
        
        //add to database
        echo '<h2>Results</h2>';
        if($num_deputies>0){
            for($i=0;$i<$num_deputies;$i++){
                $gpIDq= "SELECT MAX( GPNUM ) 'gpID' FROM WTS_RADIOLOG";
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
                
                $myq = "INSERT INTO WTS_RADIOLOG ( REFNUM ,`DEPUTYID`,`RADIO_CALLNUM` , CHECKEDOUT, RADIOID, TYPE,
                            `AUDIT_OUT_ID` ,`AUDIT_OUT_TS` ,`AUDIT_OUT_IP`, IS_RESERVE, GPNUM) 
                        VALUES ( NULL , '".$deputyID[$i]."', '".$radioCallNum[$i]."', '1', '".$radioID."',
                            '".$checkOutType."', '".$_SESSION['userIDnum']."', NOW(), 
                            INET_ATON('".$_SERVER['REMOTE_ADDR']."'), ".$isReserve[$i].", '".$groupID."');";
                $result = $mysqli->query($myq);
                if(!SQLerrorCatch($mysqli, $result)) {
                    $radioLogID = $mysqli->insert_id;  
                    addLog($config, 'Radio Checked out Ref#'.$radioLogID.' Added');
                    echo 'Successfully Checked Out Radio with Reference Number: '.$radioLogID.'<br />';
                    $isEditing = true;
                }
                else
                    echo 'Failed to check out radio, try again.<br />';
            }
        }
        else{
            echo 'Must select a user.<br />';
        }
        echo '<br />';

        //display results and get secLogID just added
    }
    if($checkInRadio){
        $radioLogID = isset($_POST['radioLogID']) ? $_POST['radioLogID'] : '';
        checkInRadioLog($config, $radioLogID);
        $isEditing = true;
    }
    
    if($updateRadioLog){
        ////get posted values
        $radioLogID = isset($_POST['radioLogID']) ? $mysqli->real_escape_string($_POST['radioLogID']) : '';
        $radioID = isset($_POST['radioID']) ? $mysqli->real_escape_string(strtoupper($_POST['radioID'])) : '';
        $radioCallNum = isset($_POST['radioCallNum']) ? $mysqli->real_escape_string(strtoupper($_POST['radioCallNum'])) : '';
        $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
        
        updateRadioLog($config, $radioLogID, $radioCallNum, $radioID, $checkOutType);
        $isEditing = true;
    }
    
    if($isEditing){
        if($config->adminLvl >= 0){
            $mysqli = $config->mysqli;
            $myq = "SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.REFNUM = '".$radioLogID."' AND IS_RESERVE=0
                    UNION
                    SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.REFNUM = '".$radioLogID."' AND IS_RESERVE=1
                    ";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            $row = $result->fetch_assoc();
            if($row['gpID'] != 0){
                //get all users
                echo '<div align="center">Group Reference #: '.$row['gpID'].'
                    <input type="hidden" name="gpID" value="'.$row['gpID'].'" /></div>';
                
                $newq = "SELECT R.REFNUM 'refNum', R.GPNUM 'gpID', 
                        CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.GPNUM = '".$row['gpID']."' AND IS_RESERVE=0
                    UNION
                    SELECT R.REFNUM 'refNum', R.GPNUM 'gpID', 
                        CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.GPNUM = '".$row['gpID']."' AND IS_RESERVE=1
                    ORDER BY R.REFNUM";
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
                        <input type="hidden" name="radioLogID'.$x.'" value="'.$newRow['refNum'].'" />'; $y++;
                    $depTable[$x][$y] = $newRow['DEPUTYNAME']; $y++;
                    $depTable[$x][$y] = '<input type="text" name="radioCallNum'.$x.'" value="'.$newRow['RADIO_CALLNUM'].'" />'; $y++;
                    if(strcmp($newRow['inTime'],"00/00/000 0000")==0){
                        $depTable[$x][$y] = '<input type="submit" value="Update" name="updateRadioLog'.$x.'" />
                                <input type="submit" value="LogOut" name="logoutRadioLog'.$x.'" /><br/>'; $y++;
                    }
                    else{
                        if($config->adminLvl >=25){
                            $depTable[$x][$y] = '<input type="submit" value="Update" name="updateRadioLog'.$x.'" />
                                Checked in at '.$newRow['inTime'];$y++;  
                        }
                        else{
                           $depTable[$x][$y] = 'Checked in at '.$newRow['inTime'];$y++;  
                        }
                    }
                    $x++;
                }
                showSortableTable($depTable, 1);

            }
            else{
                 echo 'Reference #: '.$radioLogID.'<input type="hidden" name="radioLogID" value="'.$radioLogID.'" /><br />
                    Deputy: '.$row['DEPUTYNAME'].'<br/>
                    Radio#: <input type="text" name="radioCallNum" value="'.$row['RADIO_CALLNUM'].'" /><br/>';
            }
            echo '<div align="left">Add Deputy: <button type="button"  name="searchBtn" 
                value="Lookup Employee" onClick="this.form.action=' . "'?userLookup=true'" . ';this.form.submit()" >
                Lookup Employee</button></div><br/>';
            echo '<br/> Radio Number: ';
            selectRadioInventory($config, "radioID", $row['RADIOID']);
            echo '<br/><br/>';
            if($row['TYPE'] == "LOANER")
                echo '<input type="radio" name="checkOutType" value="LOANER" CHECKED>LOANER</input>';
            else
                echo '<input type="radio" name="checkOutType" value="LOANER">LOANER</input>';
            if($row['TYPE'] == "PERM")
                echo '<input type="radio" name="checkOutType" value="PERM" CHECKED>PERMANENT</input><br/>';
            else
                echo '<input type="radio" name="checkOutType" value="PERM">PERMANENT</input><br/>';
            echo '<br/>Checked in time: ';
            if(strcmp($row['inTime'],"00/00/00 0000")==0){
                echo "<b>Not Checked back in Yet</b><br /><br />";
                if($row['gpID'] != 0){
                    echo '<input type="submit" name="checkInAllRadio" value="Check in All" />';
                }
                else{
                    echo '<input type="submit" name="checkInRadio" value="Check Back In" />';
                }
            }
            else{
                echo $row['inTime'].'<br /><br />';
            }
            if(strcmp($row['inTime'],"00/00/0000 0000")==0 || $config->adminLvl >=25){
                if($row['gpID'] != 0)
                    echo '<input type="submit" name="updateRadioLogAll" value="Update All" />';
                else
                    echo '<input type="submit" name="updateRadioLog" value="Update" />';
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
        $radioLogID = isset($_POST['secLogID']) ? $mysqli->real_escape_string($_POST['secLogID']) : '';
        $radioID = isset($_POST['radioID']) ? $mysqli->real_escape_string(strtoupper($_POST['radioID'])) : '';
        $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';

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
                    echo ';  Radio Call #: <input type="hidden" name="radioCallNum'.$deputyCount.'" value="'.$row['RADIO'].'" />'.$row['RADIO'];
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
            $foundUserID = $_SESSION['userIDnum'];
            $foundUserIsReserve = false;
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
            echo ';  Radio Call#: <input type="hidden" name="radioCallNum'.$deputyCount.'" value="'.$row['RADIO'].'" />'.$row['RADIO'];
            echo '<input type="submit" name="removeDeputyBtn'.$deputyCount.'" value="Remove" />';
            echo '<br/>';
            $deputyCount++;
        }
        echo 'Add Deputy: ';
        displayUserLookup($config);
        echo '<br />';
        echo '<input type="hidden" name="num_deputies" value="'.$deputyCount.'" />';
           
        $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;
        echo '<br/><input type="hidden" name="gpID" value="'.$gpID.'" /> Radio Number: ';
        selectRadioInventory($config, "radioID", $radioID);
        echo '<br/><br/>';
        echo '<input type="radio" name="checkOutType" value="LOANER">LOANER</input>';
        echo '<input type="radio" name="checkOutType" value="PERM">PERMANENT</input><br/>';
        echo '<br/><input type="hidden" name="addBtn" value="true" />
            <input type="submit" name="addRadioLog" value="Check Out Radio" />
            <input type="submit" name="goBtn" value="Cancel" />';
    }
}
function checkInRadioLog($config, $radioLogID){
    $mysqli = $config->mysqli;
    
    $myq = "UPDATE WTS_RADIOLOG SET CHECKEDOUT = '0', `AUDIT_IN_ID` = '".$_SESSION['userIDnum']."', `AUDIT_IN_TS` = NOW(),
        `AUDIT_IN_IP` = INET_ATON('".$_SERVER['REMOTE_ADDR']."') WHERE WTS_RADIOLOG.REFNUM = ".$radioLogID." LIMIT 1 ;";
    $result = $mysqli->query($myq);
    if(!SQLerrorCatch($mysqli, $result)){
            echo 'Successfully checked radio back in with Reference Number: '.$radioLogID.'<br /><br/>';
            addLog($config, 'Radio log #'.$radioLogID.' checked back in');
    }
    else
        echo '<h2>Results</h2>Failed to check radio back in, try again.<br /><Br />';  
}
function updateRadioLog($config, $radioLogID, $radioCallNum, $radioID, $checkOutType){
    $mysqli = $config->mysqli;

    $myq = "UPDATE WTS_RADIOLOG 
            SET RADIO_CALLNUM = '".$radioCallNum."', TYPE = '".$checkOutType."', RADIOID='".$radioID."'
            WHERE REFNUM = ".$radioLogID;
    $result = $mysqli->query($myq);
    if(!SQLerrorCatch($mysqli, $result)){
            echo 'Successfully Updated Radio Log #'.$radioLogID.'<br />';
            addLog($config, 'Radio Log #'.$radioLogID.' Modified');
    }
    else
        echo '<h2>Results</h2>Failed to update Radio Log, try again.<br /><Br />';

}
function selectRadioInventory($config, $inputName, $selectedValue=false, $onChangeSubmit=false){
        //assumes to be part of a form
    //provides a drop down selection for time type.
    $mysqli = $config->mysqli;
    if($onChangeSubmit)
        echo '<select name="'.$inputName.'" onchange="this.form.submit()">';
    else
        echo '<select name="'.$inputName.'" >';
    
    $myq = "SELECT IDNUM, OTHER_SN 
            FROM WTS_INVENTORY
            WHERE IS_DEPRECIATED = 0
            AND TYPE = (SELECT IDNUM FROM WTS_INV_TYPE WHERE DESCR = 'Radio');";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    
    while($row = $result->fetch_assoc()){
        if($row['IDNUM'] == $selectedValue)
            echo '<option value="'.$row['IDNUM'].'" SELECTED>'.$row['OTHER_SN'].'</option>';
        else
            echo '<option value="'.$row['IDNUM'].'">'.$row['OTHER_SN'].'</option>';
        
    }
    
    echo '</select>';
}

?>
