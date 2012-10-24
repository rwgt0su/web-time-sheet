<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function displayRadioLog($config, $isApprovePage = false){
    if($config->adminLvl >= 0){
        $mysqli = $config->mysqli;
        if($isApprovePage)
            echo '<h2>Daily Inventory Checkout Log Approval</h2>';
        else
            echo '<h2>Daily Inventory Checkout Log</h2>';

        echo '<form name="radioLog" method="POST">
            <input type="hidden" name="formName" value="radioLog" />'; 

            //Get variables
            $dateSelect = isset($_POST['dateSelect']) ? $_POST['dateSelect'] : false;
            $changeDateBtn = isset($_POST['changeDate']) ? True : false;
            $editSelect = isset($_POST['editRows']) ? $_POST['editRows'] : false;
            $addBtn = isset($_POST['addBtn']) ? True : false;
            $checkoutKeyBtn = isset($_POST['checkoutKeyBtn']) ? True : false;
            if($checkoutKeyBtn)
                $addBtn = false;
            $editBtn = isset($_POST['editBtn']) ? True : false;
            $radioLogID = isset($_POST['radioLogID']) ? $_POST['radioLogID'] : false;
            $keyLogID = isset($_POST['keyLogID']) ? $_POST['keyLogID'] : false;
            $finalRows = isset($_POST['finalRows']) ? $_POST['finalRows'] : false;
            $checkInKey = isset($_POST['checkInKey']) ? true : false;
            $updateRadioLog = isset($_POST['updateRadioLog']) ? true : false;
            $updateKeyLog = isset($_POST['updateKeyLog']) ? true : false;
            $showAll = isset($_POST['showAllPerm']) ? true : false;
            $showAll = isset($_POST['showAllLoaner']) ? true : $showAll;
            $showAll = isset($_POST['showAllShift']) ? true : $showAll;
            $showNormal = isset($_POST['showNormal']) ?  true : false;
            $goBtn = isset($_POST['goBtn']) ? true : false;
            $isApprovePage = isset($_GET['secApprove']) ? true : $isApprovePage;
            $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
            $radioLogID = isset($_POST['backToApprove']) ? false : $radioLogID ;
            $exchangeLogID = isset($_POST['exchangeLogID']) ? $_POST['exchangeLogID'] : false;
            $itemLogType = '';
            $cancelBtn = isset($_POST['cancelBtn']) ? true : false;
            $counter = 0;

            if($showAll || $showNormal){
                $goBtn = true;
            }
            if($changeDateBtn){
                $dateSelect = false;
                $editSelect = false;
                $goBtn = false;
                $addBtn = false;
            }
            if($cancelBtn){
                $goBtn = true;
                $exchangeLogID = '';
            }
            if(!$isApprovePage && !isset($_POST['exchangeLogID'])){
                if(!$dateSelect){
                    echo 'Select Date: ';
                    displayDateSelect("dateSelect", "dateSel",false,false,true,true);
                    echo '<input id="goBtn" type=submit name="goBtn" value="Go" /><br />'; 
                }
                else{
                    echo '<h3>Date: '.$dateSelect.'';
                    echo '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />
                        <input type="submit" name="changeDate" value="Change Date" /> 
                        <input type="submit" name="checkoutKeyBtn" value="Checkout Items" /></h3>';
                }
            }
            else
                 echo '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />';
            if($goBtn){
                showQuickSearch();
                if($config->adminLvl < 25){
                    //non supervisor logs
                    $counter += showRadioLog($config, $dateSelect,$counter, "LOANER", false);
                }
                else{
                    //supervisor logs
                    $counter += showRadioLog($config, $dateSelect, $counter, "LOANER", true);
                }
                $counter += showRadioLog($config, $dateSelect, $counter, "SHIFT", false);
                $counter += showRadioLog($config, $dateSelect, $counter, "PERM", false);
            } 
            if(isset($_POST['exchangeLogID']))
                $addBtn = false;
            if($addBtn){
                //showRadioLogDetails($config, $radioLogID);
            }
            if($checkoutKeyBtn || $addBtn){
                showKeyLogDetails($config, $keyLogID);
            }
            if($exchangeLogID){
                showItemExchange($config, $exchangeLogID);
                $editBtn = false;
            }
            //get group update or logout
            if($finalRows > 0){
                $approveBtn = array();
                $foundEditBtn = false;
                for($i=1;$i<=$finalRows;$i++){
                    if(isset($_POST['radioLogEditBtn'.$i])){
                        $radioLogID = $_POST['radioLogID'.$i];
                        $itemLogType = $_POST['itemLogType'.$i];
                        $foundEditBtn = true;
                    }
                    if(isset($_POST['logoutRadioLog'.$i]) || isset($_POST['logoutRadioLogAll']) || isset($_POST['checkInRadio'.$i])){
                        $radioLogID = $_POST['radioLogID'.$i];
                        checkInRadioLog($config, $radioLogID);
                        showQuickSearch();
                        $counter += showRadioLog($config, $dateSelect, $counter, "LOANER", false);
                        $counter += showRadioLog($config, $dateSelect, $counter, "SHIFT", false);
                        $counter += showRadioLog($config, $dateSelect, $counter, "PERM", false);
                    }
                    else if(isset($_POST['updateRadioLog'.$i]) || isset($_POST['updateRadioLogAll'])){
                        //get posted values
                        $radioLogID = $_POST['radioLogID'.$i];
                        $radioCallNum = isset($_POST['radioCallNum'.$i]) ? $mysqli->real_escape_string($_POST['radioCallNum'.$i]) : '';
                        $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string($_POST['checkOutType']) : '';

                        updateRadioLog($config, $radioLogID, $radioCallNum, $checkOutType);

                        $editBtn = true;
                    }
                    else if(isset($_POST['exchangeBtn'.$i])){
                        //checkin equipment and start checking back out
                        $radioLogID = $_POST['radioLogID'.$i];
                        showItemExchange($config, $radioLogID);
                    }
                    if(isset($_POST['viewDeputyInv'.$i])){
                        $radioLogID = $_POST['radioLogID'.$i];
                        showInventoryGroups($config, $radioLogID);
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
                        showQuickSearch();
                        $counter += showRadioLog($config, $dateSelect, $counter, "LOANER", false, $isApprovePage=true);
                        $counter += showRadioLog($config, $dateSelect, $counter, "SHIFT", false);
                        $counter += showRadioLog($config, $dateSelect, $counter, "PERM");
                    }
                }
                if($foundEditBtn){
//                    if($itemLogType == "RADIO")
//                        showRadioLogDetails($config, $radioLogID, true, $isApprovePage);
//                    if($itemLogType == "KEY")
                        showKeyLogDetails ($config, $radioLogID, true, $isApprovePage);
                }
                else if(!$addBtn && !$showAll && !$showNormal && !$changeDateBtn && !$isApprovePage && $totalRows < 0){
                    echo 'Error getting Reference Number!<br />';
                    echo '<input type="submit" name="goBtn" value="Back To Logs" />';
                }
            }
            if($isApprovePage && empty($radioLogID)){
                showQuickSearch();
                $counter += showRadioLog($config, $dateSelect, $counter, "LOANER", false, $isApprovePage);
                $counter += showRadioLog($config, $dateSelect, $counter, "SHIFT", false);
                $counter += showRadioLog($config, $dateSelect, $counter, "PERM");
            }
            if($editBtn || $updateRadioLog || $checkInKey || $updateKeyLog){
                if($config->adminLvl <= 25){
                    //Non supervisor Log details
                    showKeyLogDetails ($config, $radioLogID, true, $isApprovePage);
                }
                else{
                    //Supervisor Log Details
                    showKeyLogDetails ($config, $radioLogID, true);
                }
            }
        echo '<input type="hidden" name="finalRows" value="'.$counter.'" />';
        echo '
        
        <br />
        <br />';
    }
    else{
        echo '<h2>Daily Radio Checkout Log</h2>Access Denied';
    }
   
}

function showRadioLog($config, $dateSelect, $counter, $logType, $radioLogID, $isApprove=false){
    $mysqli = $config->mysqli;
    $isApprove = isset($_POST['isApprove']) ? true : $isApprove;
    
    /*query unions the results of joins on two different tables (EMPLOYEE and RESERVE)
      depending on the value of SECLOG.IS_RESERVE */
    if(!$isApprove){
        if($logType == "PERM"){
            $showAll = isset($_POST['showAllPerm']) ? true : false;
            if($showAll)
                $showAllQ = "AUDIT_OUT_TS LIKE '%".Date('Y-m-d', strtotime($dateSelect))."%'";
            else
                $showAllQ = "R.CHECKEDOUT=1";
            echo '<div class="divider"></div><br/><h3>Permanent Equipment Checked Out Log</h3>';
            
            $myq =  "SELECT R.REFNUM, T.DESCR 'itemType', R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
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
                LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=R.ITEM_TYPE_ID
                WHERE ".$showAllQ."
                AND R.TYPE='PERM'
                AND R.IS_RESERVE=0

                UNION

                SELECT R.REFNUM, T.DESCR 'itemType', R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
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
                LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=R.ITEM_TYPE_ID
                WHERE ".$showAllQ."
                AND R.TYPE='PERM'
                AND R.IS_RESERVE=1
                ORDER BY 'gpID'";
        }
        else if($logType == "SHIFT"){
            echo '<div class="divider"></div><br/><h3>SHIFT Assignment Checked Out Items Log</h3>';
            $showAll = isset($_POST['showAllShift']) ? true : false;
            if($showAll)
                $showAllQ = "AUDIT_OUT_TS LIKE '%".Date('Y-m-d', strtotime($dateSelect))."%'";
            else
                $showAllQ = "R.CHECKEDOUT=1";
            $myq =  "SELECT R.REFNUM, T.DESCR 'itemType', R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
                    INV.OTHER_SN, R.RADIO_CALLNUM, R.TYPE 'checkOutType', R.CHECKEDOUT 'isCheckedOut',
                    DATE_FORMAT(R.AUDIT_OUT_TS,'%m/%d/%y %H%i') 'checkOut', R.COMMENTS,
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
                LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=R.ITEM_TYPE_ID
                WHERE ".$showAllQ."
                AND R.TYPE='SHIFT'
                AND R.IS_RESERVE=0

                UNION

                SELECT R.REFNUM, T.DESCR 'itemType', R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
                    INV.OTHER_SN, R.RADIO_CALLNUM, R.TYPE 'checkOutType', R.CHECKEDOUT 'isCheckedOut',
                    DATE_FORMAT(R.AUDIT_OUT_TS,'%m/%d/%y %H%i') 'checkOut', R.COMMENTS,
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
                LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=R.ITEM_TYPE_ID
                WHERE ".$showAllQ."
                AND R.TYPE='SHIFT'
                AND R.IS_RESERVE=1
                ORDER BY 'gpID'";
        }
        else if($logType == "LOANER"){
            echo '<br/><br/><div class="divider"></div><h3>Loaner Equipment Log</h3>';
            $showAll = isset($_POST['showAllLoaner']) ? true : false;
            if($showAll)
                $showAllQ = "AUDIT_OUT_TS LIKE '%".Date('Y-m-d', strtotime($dateSelect))."%'";
            else
                $showAllQ = "AUDIT_OUT_TS LIKE '%".Date('Y-m-d', strtotime($dateSelect))."%'";
            $myq =  "SELECT R.REFNUM, T.DESCR 'itemType', R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
                    INV.OTHER_SN, R.RADIO_CALLNUM, R.TYPE 'checkOutType', R.CHECKEDOUT 'isCheckedOut',
                    DATE_FORMAT(R.AUDIT_OUT_TS,'%m/%d/%y %H%i') 'checkOut', R.COMMENTS, 
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
                LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=R.ITEM_TYPE_ID
                WHERE ".$showAllQ."
                AND R.TYPE = 'LOANER'
                AND R.IS_RESERVE=0

                UNION

                SELECT R.REFNUM, T.DESCR 'itemType', R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
                    INV.OTHER_SN, R.RADIO_CALLNUM, R.TYPE 'checkOutType', R.CHECKEDOUT 'isCheckedOut',
                    DATE_FORMAT(R.AUDIT_OUT_TS,'%m/%d/%y %H%i') 'checkOut', R.COMMENTS, 
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
                LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=R.ITEM_TYPE_ID
                WHERE ".$showAllQ."
                AND R.TYPE = 'LOANER'
                AND R.IS_RESERVE=1
                ORDER BY 'gpID'";
        }
    }
    else{
        echo '<br/><br/><div class="divider"></div><h3>Approve Equipment Log</h3>';
        //Querey used for approvals.  
        $myq =  "SELECT R.REFNUM, T.DESCR 'itemType', R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
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
                LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=R.ITEM_TYPE_ID
                WHERE AUDIT_IN_ID != ''
                AND R.IS_RESERVE=0

                UNION

                SELECT R.REFNUM, T.DESCR 'itemType', R.GPNUM 'gpID', CONCAT_WS(', ',SEC.LNAME,SEC.FNAME) 'DEPUTYID', 
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
                LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=R.ITEM_TYPE_ID
                WHERE AUDIT_IN_ID != ''
                AND R.IS_RESERVE=1
                ORDER BY 'gpID'";
        
        echo '<input type="hidden" name="isApprove" value="true" />';
    }

    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result, $myq);
    $echo = '';
    $x=$counter;
    $y=0;
    if($config->adminLvl >= 0){
        if($logType == "LOANER"){
            $showAll = isset($_POST['showAllLoaner']) ? true : false;
            if(!$isApprove){
                if($showAll)
                    echo '<div align="right"><input type="checkbox" name="showNormal" onclick="this.form.submit();" />Show Normal Loaner Logs</div>';
                else
                    echo '<div align="right"><input type="checkbox" name="showAllLoaner" onclick="this.form.submit();" />Show All Loaner Logs</div>';
            }
            $theTable = array(array());
            if(!$isApprove){
                $theTable[$x][$y] = "Edit"; $y++;
            }
            else{
                $theTable[$x][$y] = "Approve"; $y++;
            }
            $theTable[$x][$y] = "Type"; $y++;
            $theTable[$x][$y] = "Serial Number"; $y++;
            $theTable[$x][$y] = "Deputy"; $y++;
            $theTable[$x][$y] = "Radio Call#"; $y++;
            $theTable[$x][$y] = "Type"; $y++;
            $theTable[$x][$y] = "OUT_Time"; $y++;
            $theTable[$x][$y] = "OUT_By"; $y++;
            $theTable[$x][$y] = "In_Time"; $y++;
            $theTable[$x][$y] = "In_By"; $y++;
            $theTable[$x][$y] = "Status"; $y++;

            if($config->adminLvl >=25 && false){
                $theTable[$x][$y] = "Supervisor"; $y++;
                $theTable[$x][$y] = "Sign_Off"; $y++;
            }
        }//end loaner check
        else if($logType == "PERM"){
            $theTable = array(array());
            $x=0;
            $y=0;
            $theTable[$x][$y] = "Edit"; $y++;
            $theTable[$x][$y] = "Type"; $y++;
            $theTable[$x][$y] = "Serial Number"; $y++;
            $theTable[$x][$y] = "Deputy"; $y++;
            $theTable[$x][$y] = "Radio Call#"; $y++;
            $theTable[$x][$y] = "Type"; $y++;
            $theTable[$x][$y] = "OUT_Time"; $y++;
            $theTable[$x][$y] = "OUT_By"; $y++;
            $theTable[$x][$y] = "In_Time"; $y++;
            $theTable[$x][$y] = "In_By"; $y++;
            $theTable[$x][$y] = "Status"; $y++;

            if($config->adminLvl >=25 && false){
                $theTable[$x][$y] = "Supervisor"; $y++;
                $theTable[$x][$y] = "Sign_Off"; $y++;
            }
        }
        else if($logType == "SHIFT"){
            $showAll = isset($_POST['showAllShift']) ? true : false;
                if($showAll)
                    echo '<div align="right"><input type="checkbox" name="showNormal" onclick="this.form.submit();" />Show Normal Shift Assignment Logs</div>';
                else
                    echo '<div align="right"><input type="checkbox" name="showAllShift" onclick="this.form.submit();" />Show All Shift Assignment Logs</div>';
          
            $theTable = array(array());
            $x=0;
            $y=0;
            $theTable[$x][$y] = "Edit"; $y++;
            $theTable[$x][$y] = "Type"; $y++;
            $theTable[$x][$y] = "Serial Number"; $y++;
            $theTable[$x][$y] = "Deputy "; $y++;
            $theTable[$x][$y] = "Radio Call#"; $y++;
            $theTable[$x][$y] = "Type"; $y++;
            $theTable[$x][$y] = "OUT_Time"; $y++;
            $theTable[$x][$y] = "OUT_By"; $y++;
            $theTable[$x][$y] = "In_Time"; $y++;
            $theTable[$x][$y] = "In_By"; $y++;
            $theTable[$x][$y] = "Status"; $y++;

            if($config->adminLvl >=25 && false){
                $theTable[$x][$y] = "Supervisor"; $y++;
                $theTable[$x][$y] = "Sign_Off"; $y++;
            }
        }
        
        while($row = $result->fetch_assoc()) {
            if($logType == "LOANER"){
                if(strcmp($row['checkIn'], "00/00/00 0000") == 0 || $showAll || (strcmp($row['SUP_TIME'], "00/00/00 0000") == 0 && $isApprove)){
                    $x++;
                    if(!$isApprove)
                        $theTable[$x][0] = 'Ref# '.$row['REFNUM'].'<input type="submit" value="Edit/View" name="radioLogEditBtn'.$x.'" />
                            <input type="hidden" name="radioLogID'.$x.'" value="'.$row['REFNUM'].'" />';
                    else{
                        if((strcmp($row['SUP_TIME'], "00/00/00 0000") == 0))
                            $theTable[$x][0] = 'Ref# '.$row['REFNUM'].'<input type="submit" name="radioLogApproved'.$x.'" value="Approve" />
                                <input type="hidden" name="radioLogID'.$x.'" value="'.$row['REFNUM'].'" />
                                    <input type="submit" value="Edit/View" name="radioLogRadio'.$x.'" />
                                    Ref# '.$row['REFNUM'];
                        else{
                            $theTable[$x][0] = 'Ref# '.$row['REFNUM'].'<input type="submit" value="Edit/View" name="radioLogRadio'.$x.'" />
                            <input type="hidden" name="radioLogID'.$x.'" value="'.$row['REFNUM'].'" />';
                        }
                    }
                    $y = 1;
                    $theTable[$x][$y] = $row['itemType'].'<input type="hidden" name="itemLogType'.$x.'" value="'.$row['itemType'].'" />'; $y++;
                    $theTable[$x][$y] = $row['OTHER_SN']; $y++;
                    $deputyName = $row['DEPUTYID'];
                    if($deputyName == "SYSTEM, USER"){
                        $theTable[$x][$y] = $row['COMMENTS']; $y++;
                    }
                    else{
                        if($config->adminLvl >=25)
                            $theTable[$x][$y] = 'All Inventory for <br/><input type="submit" value="'.$deputyName.'" name="viewDeputyInv'.$x.'" />';
                        else
                            $theTable[$x][$y] = $deputyName; $y++;
                    }
                    $theTable[$x][$y] = $row['RADIO_CALLNUM']; $y++;
                    $theTable[$x][$y] = $row['checkOutType']; $y++;
                    $theTable[$x][$y] =$row['checkOut']; $y++;
                    $theTable[$x][$y] =$row['AUDIT_OUT_ID']; $y++;
                    if($row['checkIn']=="00/00/00 0000"){
                        $theTable[$x][$y] = ""; $y++;
                    }
                    else{
                        $theTable[$x][$y] = $row['checkIn']; $y++;
                    }
                    if($row['AUDIT_IN_ID'] == "SYSTEM, USER")
                        $theTable[$x][$y] = "";
                    else
                        $theTable[$x][$y] = $row['AUDIT_IN_ID']; 
                    $y++;
                    if($row['isCheckedOut'] == 1){
                        $theTable[$x][$y] = '<input type="submit" name="checkInRadio'.$x.'" value="Check Back In" /><br/>
                            <input type="submit" name="exchangeBtn'.$x.'" value="Exchange" />'; $y++;
                    }
                    else{
                        $theTable[$x][$y] = "Checked In"; $y++;
                    }  
                    if($config->adminLvl >=25){
                        $theTable[$x][$y] =$row['SUP_ID']; $y++;
                        if($row['SUP_TIME']=="00/00/00 0000"){
                            $theTable[$x][$y] = ""; $y++;
                        }
                        else{
                            $theTable[$x][$y] =$row['SUP_TIME']; $y++;
                        }
                    }
                }//end show all checkbox verification
            }//end loaner log check
            else if ($logType == "PERM"){
                $x++;
                $counter++;
                $y=0;

                if($config->adminLvl >=25){
                    $theTable[$x][$y] = 'Ref# '.$row['REFNUM'].'<input type="submit" value="Edit/View" name="radioLogEditBtn'.$counter.'" />
                        <input type="hidden" name="radioLogID'.$counter.'" value="'.$row['REFNUM'].'" />'; $y++;
                }
                else{
                $theTable[$x][$y] = 'Ref# '.$row['REFNUM'].'
                    <input type="hidden" name="radioLogID'.$counter.'" value="'.$row['REFNUM'].'" />'; $y++; 
                }
                $theTable[$x][$y] = $row['itemType'].'<input type="hidden" name="itemLogType'.$counter.'" value="'.$row['itemType'].'" />'; $y++;
                $theTable[$x][$y] = $row['OTHER_SN']; $y++;
                if($config->adminLvl >=25)
                        $theTable[$x][$y] = 'All Inventory for <br/><input type="submit" value="'.$row['DEPUTYID'].'" name="viewDeputyInv'.$x.'" />';
                    else
                        $theTable[$x][$y] = $row['DEPUTYID']; $y++;
                $theTable[$x][$y] = $row['RADIO_CALLNUM']; $y++;
                $theTable[$x][$y] = $row['checkOutType']; $y++;
                $theTable[$x][$y] = $row['checkOut']; $y++;
                $theTable[$x][$y] = $row['AUDIT_OUT_ID']; $y++;
                if($row['checkIn']=="00/00/00 0000"){
                    $theTable[$x][$y] = ""; $y++;
                }
                else{
                    $theTable[$x][$y] = $row['checkIn']; $y++;
                }
                if($row['AUDIT_IN_ID'] == "SYSTEM, USER")
                    $theTable[$x][$y] = "";
                else
                    $theTable[$x][$y] = $row['AUDIT_IN_ID']; 
                $y++;
                if($row['isCheckedOut'] == 1){
                    if($config->adminLvl >=50){
                        $theTable[$x][$y] = '<input type="submit" name="checkInRadio'.$counter.'" value="Check Back In" />'; $y++;
                    }
                    else{
                        $theTable[$x][$y] = "Checked Out"; $y++;
                    }
                }
                else{
                    $theTable[$x][$y] = "Checked In"; $y++;
                }  
                if($config->adminLvl >=25 && false){
                    $theTable[$x][$y] =$row['SUP_ID']; $y++;
                    if($row['SUP_TIME']=="00/00/00 0000"){
                        $theTable[$x][$y] = ""; $y++;
                    }
                    else{
                        $theTable[$x][$y] =$row['SUP_TIME']; $y++;
                    }
                }
                
            }//end Perm Log Check
            else if ($logType == "SHIFT"){
                $x++;
                $counter++;
                $y=0;

                if($row['checkIn'] == "" || $config->adminLvl >=25){
                    $theTable[$x][$y] = 'Ref# '.$row['REFNUM'].'<input type="submit" value="Edit/View" name="radioLogEditBtn'.$counter.'" />
                        <input type="hidden" name="radioLogID'.$counter.'" value="'.$row['REFNUM'].'" />'; $y++;
                }
                else{
                $theTable[$x][$y] = 'Ref# '.$row['REFNUM'].'
                    <input type="hidden" name="radioLogID'.$counter.'" value="'.$row['REFNUM'].'" />'; $y++; 
                }
                $theTable[$x][$y] = $row['itemType'].'<input type="hidden" name="itemLogType'.$counter.'" value="'.$row['itemType'].'" />'; $y++;
                $theTable[$x][$y] = $row['OTHER_SN']; $y++;
                if($config->adminLvl >=25)
                    $theTable[$x][$y] = 'All Inventory for <br/><input type="submit" value="'.$row['DEPUTYID'].'" name="viewDeputyInv'.$x.'" />';
                else
                    $theTable[$x][$y] = $row['DEPUTYID']; $y++;
                $theTable[$x][$y] = $row['RADIO_CALLNUM']; $y++;
                $theTable[$x][$y] = $row['checkOutType']; $y++;
                $theTable[$x][$y] = $row['checkOut']; $y++;
                $theTable[$x][$y] = $row['AUDIT_OUT_ID']; $y++;
                if($row['checkIn']=="00/00/00 0000"){
                    $theTable[$x][$y] = ""; $y++;
                }
                else{
                    $theTable[$x][$y] = $row['checkIn']; $y++;
                }
                if($row['AUDIT_IN_ID'] == "SYSTEM, USER")
                    $theTable[$x][$y] = "";
                else
                    $theTable[$x][$y] = $row['AUDIT_IN_ID']; 
                $y++;
                if($row['isCheckedOut'] == 1){
                    if($config->adminLvl >=0){
                        $theTable[$x][$y] ='';
                        if($config->adminLvl>=0)
                            $theTable[$x][$y] ='<input type="submit" name="checkInRadio'.$counter.'" value="Check Back In" /><br/>';
                        $theTable[$x][$y] .= '<input type="submit" name="exchangeBtn'.$x.'" value="Exchange" />'; $y++;
                    }
                    else{
                        $theTable[$x][$y] = "Checked Out"; $y++;
                    }
                }
                else{
                    $theTable[$x][$y] = "Checked In"; $y++;
                }  
                if($config->adminLvl >=25 && false){
                    $theTable[$x][$y] =$row['SUP_ID']; $y++;
                    if($row['SUP_TIME']=="00/00/00 0000"){
                        $theTable[$x][$y] = ""; $y++;
                    }
                    else{
                        $theTable[$x][$y] =$row['SUP_TIME']; $y++;
                    }
                }
                
            }//end Pod Log Check
        }//end while loop
    }

    $sortOrder = array(2, 1, 1);
    showSortableTable($theTable, 2, $logType, $sortOrder);
    $echo .= '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />';
    
    echo $echo;
    return $x;
    
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
        echo '<h2><font color="red">Results</font></h2>';
        if($num_deputies > 0){
            for($i=0;$i<$num_deputies;$i++){
                $deputyID[$i] = isset($_POST['deputyID'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['deputyID'.$i])) : false;
                $radioCallNum[$i] = isset($_POST['radioCallNum'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['radioCallNum'.$i])) : '';
                $isReserve[$i] = isset($_POST['isReserve'.$i]) ? '1' : '0';
            }
            
            $radioID = isset($_POST['radioID']) ? $mysqli->real_escape_string(strtoupper($_POST['radioID'])) : '';
            $podID = isset($_POST['podID']) ? $mysqli->real_escape_string(strtoupper($_POST['podID'])) : '';
            $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
            $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;

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
                
                checkOutItem($config, $deputyID[$i], $radioCallNum[$i], $radioID, $checkOutType, $isReserve[$i], $groupID);
                
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
        $podID = isset($_POST['podID']) ? $mysqli->real_escape_string(strtoupper($_POST['podID'])) : '';
        $radioCallNum = isset($_POST['radioCallNum']) ? $mysqli->real_escape_string(strtoupper($_POST['radioCallNum'])) : '';
        $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
        
        updateRadioLog($config, $radioLogID, $radioCallNum, $radioID, $podID, $checkOutType);
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
                 echo '<br/>Reference #: '.$radioLogID.'<input type="hidden" name="radioLogID" value="'.$radioLogID.'" /><br />
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
                echo '<input type="radio" name="checkOutType" value="PERM" CHECKED>PERMANENT</input>';
            else
                echo '<input type="radio" name="checkOutType" value="PERM">PERMANENT</input>';
            if($row['TYPE'] == "POD")
                echo '<input type="radio" name="checkOutType" value="POD" CHECKED>SHIFT ASSIGNMENT</input><br/>';
            else
                echo '<input type="radio" name="checkOutType" value="POD">SHIFT ASSIGNMENT</input><br/>';
            echo '<br/>Checked in time: ';
            if(strcmp($row['inTime'],"00/00/00 0000")==0){
                echo "<font color=red><b>Not Checked back in Yet</b></font><br /><br />";
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
        echo '<br/><br/>';
        $radioLogID = isset($_POST['secLogID']) ? $mysqli->real_escape_string($_POST['secLogID']) : '';
        $radioID = isset($_POST['radioID']) ? $mysqli->real_escape_string(strtoupper($_POST['radioID'])) : '';
        $podID = isset($_POST['podID']) ? $mysqli->real_escape_string(strtoupper($_POST['podID'])) : '';
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
            //security check for central control computer
            if($_SERVER['REMOTE_ADDR'] != '10.1.32.58'/*nslookup('mcjcbcast.sheriff.mahoning.local')*/){
                //Default first deputy to logged in user on first load
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
            echo ';  Radio Call#: <input type="hidden" name="radioCallNum'.$deputyCount.'" value="'.$row['RADIO'].'" />'.$row['RADIO'];
            echo '<input type="submit" name="removeDeputyBtn'.$deputyCount.'" value="Remove" />';
            echo '<br/>';
            $deputyCount++;
        }
        echo 'Add Deputy: ';
        displayUserLookup($config);
        echo '<input type="hidden" name="num_deputies" value="'.$deputyCount.'" />';
        
        $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;
        echo '<br/><br/><input type="hidden" name="gpID" value="'.$gpID.'" /> Radio Number: ';
        selectRadioInventory($config, "radioID", $radioID);
        echo '<br/><br/>';
        if($checkOutType == "LOANER")
                echo '<input type="radio" name="checkOutType" value="LOANER" CHECKED>LOANER</input>';
            else
                echo '<input type="radio" name="checkOutType" value="LOANER">LOANER</input>';
            if($checkOutType == "PERM")
                echo '<input type="radio" name="checkOutType" value="PERM" CHECKED>PERMANENT</input>';
            else
                echo '<input type="radio" name="checkOutType" value="PERM">PERMANENT</input>';
            if($checkOutType == "POD")
                echo '<input type="radio" name="checkOutType" value="POD" CHECKED>SHIFT ASSIGNMENT</input><br/>';
            else
                echo '<input type="radio" name="checkOutType" value="POD">SHIFT ASSIGNMENT</input><br/>';
        echo '<br/><input type="hidden" name="addBtn" value="true" />
            <input type="submit" name="addRadioLog" value="Check Out Radio" />
            <input type="submit" name="goBtn" value="Cancel" />';
    }
}
function checkInRadioLog($config, $radioLogID, $noLog=false){
    $mysqli = $config->mysqli;
    $checkq = "SELECT PRIORITY_TYPE FROM WTS_INVENTORY WHERE IDNUM=(SELECT RADIOID FROM WTS_RADIOLOG WHERE REFNUM='".$radioLogID."')";
    $checkResult = $mysqli->query($checkq);
    SQLerrorCatch($mysqli, $checkResult, $checkq);
    $row = $checkResult->fetch_assoc();
    
    if($row['PRIORITY_TYPE'] == "EMERGENCY"){
        popUpMessage('Emergency Reason: <br/>
            <form method="POST"><input name="ereason"/><br/>
            <input type="submit" name="ereaesonBtn" value="Submit Reason" />
            </form>');
    }
    
    $myq = "UPDATE WTS_RADIOLOG SET CHECKEDOUT = '0', `AUDIT_IN_ID` = '".$_SESSION['userIDnum']."', `AUDIT_IN_TS` = NOW(),
        `AUDIT_IN_IP` = INET_ATON('".$_SERVER['REMOTE_ADDR']."') WHERE WTS_RADIOLOG.REFNUM = ".$radioLogID." LIMIT 1 ;";
    $result = $mysqli->query($myq);
    if(!SQLerrorCatch($mysqli, $result)){
            if(!$noLog){
                echo '<font color="red">Successfully checked item back in with Reference Number: '.$radioLogID.'</font><br /><br/>';
                addLog($config, 'Radio log #'.$radioLogID.' checked back in');
            }
    }
    else
        echo '<h2>Results</h2><font color="red">Failed to check radio back in, try again.</font><br /><Br />';  
}
function updateRadioLog($config, $radioLogID, $radioCallNum, $radioID, $checkOutType, $comments='', $ereason=''){
    $mysqli = $config->mysqli;

    $myq = "UPDATE WTS_RADIOLOG 
            SET RADIO_CALLNUM = '".$radioCallNum."', TYPE = '".$checkOutType."', RADIOID='".$radioID."',
                COMMENTS = '".$comments."', EREASON = '".$ereason."' 
            WHERE REFNUM = ".$radioLogID;
    $result = $mysqli->query($myq);
    if(!SQLerrorCatch($mysqli, $result)){
            echo '<font color="red">Successfully Updated Radio Log #'.$radioLogID.'</font><br />';
            addLog($config, 'Radio Log #'.$radioLogID.' Modified');
    }
    else
        echo '<h2>Results</h2><font color="red">Failed to update Radio Log, try again.</font><br /><Br />';

}
function selectRadioInventory($config, $inputName, $selectedValue=false, $onChangeSubmit=false){
        //assumes to be part of a form
    //provides a drop down selection for time type.
    $mysqli = $config->mysqli;
    if($onChangeSubmit)
        echo '<select name="'.$inputName.'" onchange="this.form.submit()">';
    else
        echo '<select name="'.$inputName.'" >';
    if($selectedValue){
        $myq = "SELECT OTHER_SN, DESCR 
                FROM WTS_INVENTORY INV
                WHERE IDNUM='".$selectedValue."';";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $row = $result->fetch_assoc();
        if(!empty($row['DESCR']))
            $itemDesc = ' ('.$row['DESCR'].')';
        echo '<option value="'.$selectedValue.'" SELECTED>'.$row['OTHER_SN'].$itemDesc.'</option>';
    }
    else
        echo '<option value=""></option>';
            
    $myq = "SELECT IDNUM, OTHER_SN, DESCR 
            FROM WTS_INVENTORY INV
            WHERE IS_ACTIVE = 1
            AND IS_DEPRECIATED = 0
            AND NOT (SELECT COUNT(CHECKEDOUT) FROM WTS_RADIOLOG WHERE CHECKEDOUT = 1 AND RADIOID = INV.IDNUM) > 0
            AND TYPE = (SELECT IDNUM FROM WTS_INV_TYPE WHERE DESCR = 'Radio');";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    
    while($row = $result->fetch_assoc()){
        $itemDesc = '';
        if(!empty($row['DESCR']))
            $itemDesc = ' ('.$row['DESCR'].')';
        if($row['IDNUM'] == $selectedValue)
            echo '<option value="'.$row['IDNUM'].'" SELECTED>'.$row['OTHER_SN'].$itemDesc.'</option>';
        else
            echo '<option value="'.$row['IDNUM'].'">'.$row['OTHER_SN'].$itemDesc.'</option>';
        
    }
    
    echo '</select>';
}
function selectPOD($config, $inputName, $selectedValue=false, $onChangeSubmit=false){
        //assumes to be part of a form
    //provides a drop down selection for time type.
    $mysqli = $config->mysqli;
    if($onChangeSubmit)
        echo '<select name="'.$inputName.'" onchange="this.form.submit()">';
    else
        echo '<select name="'.$inputName.'" ><option value="0"></option>';
    
    $myq = "SELECT * FROM WTS_PODS";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    
    while($row = $result->fetch_assoc()){
        if($row['IDNUM'] == $selectedValue)
            echo '<option value="'.$row['IDNUM'].'" SELECTED>'.$row['PODNAME'].'</option>';
        else
            echo '<option value="'.$row['IDNUM'].'">'.$row['PODNAME'].'</option>';
        
    }
    
    echo '</select>';
}
function checkOutItem($config, $deputyID, $radioCallNum, $itemID, $itemTypeID, $checkOutType, $isReserve, $groupID, $noLog=false, $comments=''){
    $mysqli = $config->mysqli;
    
    //get supplied item's description type
    $itemq = "SELECT DESCR FROM WTS_INV_TYPE WHERE IDNUM=".$itemTypeID." LIMIT 1;";
    $itemResult = $mysqli->query($itemq);
    SQLerrorCatch($mysqli, $itemResult, $itemq);
    $itemName = $itemResult->fetch_assoc();
    $itemType = $itemName['DESCR'];
    $inventoryLogID = '';
    
    $verifyq = "SELECT COUNT(I.IDNUM) 'isAvailable', OTHER_SN 'itemID'
                FROM WTS_INVENTORY I
                WHERE IS_ACTIVE = 1
                AND IS_DEPRECIATED = 0
                AND NOT 
                    (SELECT COUNT(CHECKEDOUT) FROM WTS_RADIOLOG WHERE CHECKEDOUT = 1 AND RADIOID = I.IDNUM) 
                    >= 
                    (SELECT QUANTITY FROM WTS_INVENTORY INV WHERE INV.IDNUM=I.IDNUM)
                AND I.IDNUM=".$itemID."
                ;";
    $verifyResult = $mysqli->query($verifyq);
    SQLerrorCatch($mysqli, $verifyResult, $verifyq);
    $verifyCount = $verifyResult->fetch_assoc();
    
    if($verifyCount['isAvailable'] == "1"){    
        $myq = "INSERT INTO WTS_RADIOLOG ( REFNUM ,`DEPUTYID`,`RADIO_CALLNUM` , CHECKEDOUT, RADIOID, TYPE,
                `AUDIT_OUT_ID` ,`AUDIT_OUT_TS` ,`AUDIT_OUT_IP`, IS_RESERVE, GPNUM, ITEM_TYPE_ID, COMMENTS) 
            VALUES ( NULL , '".$deputyID."', '".$radioCallNum."', '1', '".$itemID."',
                '".$checkOutType."', '".$_SESSION['userIDnum']."', NOW(), 
                INET_ATON('".$_SERVER['REMOTE_ADDR']."'), '".$isReserve."', '".$groupID."', '".$itemTypeID."', '".$comments."' );";
        $result = $mysqli->query($myq);
        if(!SQLerrorCatch($mysqli, $result, $myq)) {
            $inventoryLogID = $mysqli->insert_id;
            if(!$noLog){
                addLog($config, $itemType.' Checked out Ref#'.$inventoryLogID.' Added');
                echo '<font color="red">Successfully Checked Out '.$itemType.' with Reference Number: '.$inventoryLogID.'</font><br/>';
            }
        }
        else
            echo '<font color="red">Failed to check out '.$itemType.', try again.</font><br/>';
        return $inventoryLogID;
    }
    else
        return '<font color="red">'.$itemType.' '.$verifyCount['itemID'].' not available for checkout.  Please check back in first.</font>';
}
function showItemExchange($config, $radioLogID){
    $mysqli = $config->mysqli;
    
    //get radioLog duplicating information
    $myq = "SELECT R.RADIOID, R.TYPE, INV.OTHER_SN, ITYPE.IDNUM 'itemTypeID', ITYPE.DESCR 'itemType', CONCAT_WS(', ', EMP.LNAME, EMP.FNAME) 'deputyName'
        FROM WTS_RADIOLOG R
        LEFT JOIN EMPLOYEE AS EMP ON R.DEPUTYID=EMP.IDNUM
        LEFT JOIN WTS_INVENTORY AS INV ON R.RADIOID=INV.IDNUM
        LEFT JOIN WTS_INV_TYPE AS ITYPE ON INV.TYPE=ITYPE.IDNUM
        WHERE R.REFNUM = '".$radioLogID."' LIMIT 1;";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result, $myq);
    $item = $result->fetch_assoc();
    $radioID = $item['RADIOID'];
    
    
    echo '<br/>'.$item['itemType'].' '.$item['OTHER_SN'].' will be exchanged from '.$item['deputyName'].' to: <br/>';
    
    

    //debug
    //var_dump($_POST);
    //Show previously added deputies
    $isExchanged = false;
    $deputyCount=0;
    $num_deputies = isset($_POST['num_deputies']) ? $_POST['num_deputies'] : 0;
    $exchangeBtn = isset($_POST['exchangeItemBtn']) ? true : false;
    $removeBtn = false;
    
    if($num_deputies > 0){
        for($i=0;$i<$num_deputies;$i++){
            if(!isset($_POST['removeDeputyBtn'.$i])){
                $deputyID[$i] = isset($_POST['deputyID'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['deputyID'.$i])) : '';
                $isReserve[$i] = isset($_POST['isReserve'.$i]) ? true : false;

                //get this user's information
                if($isReserve[$i]){
                    $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM RESERVE WHERE IDNUM='.$deputyID[$i];
                    $result = $mysqliReserve->query($myq);
                    SQLerrorCatch($mysqliReserve, $result, $myq);
                    $row = $result->fetch_assoc();
                }
                else{
                    $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM EMPLOYEE WHERE IDNUM='.$deputyID[$i];
                    $result = $mysqli->query($myq);
                    SQLerrorCatch($mysqli, $result, $myq);
                    $row = $result->fetch_assoc();
                }  
                if($i==0)
                    $phone = $row['CELLPH'];
                echo 'Deputy: <input type="hidden" name="deputyID'.$deputyCount.'" value="'.$deputyID[$i].'" />';
                if($isReserve[$i]==1)
                    echo '<input type="hidden" name="isReserve'.$deputyCount.'" value="true" />';
                echo $row['LNAME'] . ', ' . $row['FNAME'];
                echo ';  Radio Call #: <input type="hidden" name="radioCallNum'.$deputyCount.'" value="'.$row['RADIO'].'" />'.$row['RADIO'];
                echo '<br/>';
                if($exchangeBtn){
                    checkInRadioLog($config, $radioLogID, $noLog=true);
                    $noteq = "UPDATE WTS_RADIOLOG SET EXCHANGEID = '".$deputyID[$i]."' WHERE REFNUM='".$radioLogID."';";
                    $noteResult = $mysqli->query($noteq);
                    SQLerrorCatch($mysqli, $noteResult);
                    
                    $tempReserve = isset($_POST['isReserve'.$i]) ? '1' : '0';
                    $insertLogID = checkOutItem($config, $deputyID[$i], $row['RADIO'], $radioID, $item['itemTypeID'], "SHIFT", $tempReserve, "0", $noLog=true);
                
                    addLog($config, 'Exchanged Log Ref#'.$radioLogID.' with Ref#'.$insertLogID);
                    echo '<br/><font color="red">Exchanged Ref#'.$radioLogID.' with Ref#'.$insertLogID.'</font><br/>';
                    $isExchanged = true;
                }
               
                $deputyCount++;
            }//End check for remove button
            else
                $removeBtn = true;
        }//End for loop of previously added deputies
    }//End check for multiple deputies
     if(!$isExchanged){
        echo '<input type="hidden" name="exchangeLogID" value="'.$radioLogID.'" />';
        echo '<input type="hidden" name="itemID" value="'.$radioID.'" />';
    }
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
    //Defaut First User - Default keep disabled for this type of exchange
//    if(empty($foundUserID) && $num_deputies == 0){
//        //security check for central control computer
//        if($_SERVER['REMOTE_ADDR'] != nslookup('mcjcbcast.sheriff.mahoning.local')){
//            //Default first deputy to logged in user on first load
//            $foundUserID = $_SESSION['userIDnum'];
//            $foundUserIsReserve = false;
//        }
//    }
    
    //Start to display information
    if(empty($foundUserID) && !$removeBtn){
        //default to logged in deputy
        $foundUserID = $_SESSION['userIDnum'];
        $foundUserIsReserve = false;
    }
    if(!empty($foundUserID) && !$exchangeBtn){
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
        echo '<br/>Deputy: <font color="red"><input type="hidden" name="deputyID'.$deputyCount.'" value="'.$foundUserID.'" />';
        if($foundUserIsReserve)
            echo '<input type="hidden" name="isReserve'.$deputyCount.'" value="true" />';
        echo $row['LNAME'] . ', ' . $row['FNAME'];
        echo '</font>;  Radio Call#: <input type="hidden" name="radioCallNum'.$deputyCount.'" value="'.$row['RADIO'].'" />'.$row['RADIO'];
        echo '<input type="submit" name="removeDeputyBtn'.$deputyCount.'" value="Remove" />';
        echo '<br/>';
        $deputyCount++;
    }
    if($deputyCount < 1){
        //default to logged in deputy
        
        echo 'Add Deputy: ';
        displayUserLookup($config);
    }
    echo '<input type="hidden" name="num_deputies" value="'.$deputyCount.'" />';
    if(isset($_POST['exchangeItemBtn'])){
        echo '<br/><input type="submit" name="goBtn" value="Back to Logs" />';
    }
    else{
        echo '<br/><br/>';
        if($deputyCount > 0)
            echo '<input type="submit" name="exchangeItemBtn" value="Exchange Equipment" />';
        echo '<input type="submit" name="cancelBtn" value="Cancel" />';
    }
}
?>
