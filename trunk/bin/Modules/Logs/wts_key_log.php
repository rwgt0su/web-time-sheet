<?php
function showKeyLogDetails($config, $keyLogID, $isEditing=false, $isApprove=false){
    $checkOutKey = isset($_POST['addKeyLog']) ? true : false;
    $checkInKey = isset($_POST['checkInKey']) ? true : false;
    $updateKeyLog = isset($_POST['updateKeyLog']) ? true : false;
    $num_deputies = isset($_POST['num_deputies']) ? $_POST['num_deputies'] : 0;
    $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
    
    $mysqli = $config->mysqli;
    $mysqliReserve = connectToSQL($reserveDB = TRUE);
    
    if($checkOutKey){
        //get passed values
        echo '<h2><font color="red">Results</font></h2>';
        if($num_deputies > 0){
            for($i=0;$i<$num_deputies;$i++){
                $deputyID[$i] = isset($_POST['deputyID'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['deputyID'.$i])) : false;
                $radioCallNum[$i] = isset($_POST['radioCallNum'.$i]) ? $mysqli->real_escape_string(strtoupper($_POST['radioCallNum'.$i])) : '';
                $isReserve[$i] = isset($_POST['isReserve'.$i]) ? '1' : '0';
            }
            
            $keyID = isset($_POST['keyID']) ? $mysqli->real_escape_string(strtoupper($_POST['keyID'])) : '';
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
                
                checkOutItem($config, $deputyID[$i], $radioCallNum[$i], $keyID, $checkOutType, $isReserve[$i], $groupID);
                
            }
        }
        else{
            echo 'Must select a user.<br />';
        }
        echo '<br />';

        //display results and get secLogID just added
    }
    if($checkInKey){
        $keyLogID = isset($_POST['keyLogID']) ? $_POST['keyLogID'] : '';
        checkInRadioLog($config, $keyLogID);
        $isEditing = true;
    }
    
    if($updateKeyLog){
        ////get posted values
        $keyLogID = isset($_POST['keyLogID']) ? $mysqli->real_escape_string($_POST['keyLogID']) : '';
        $keyID = isset($_POST['keyID']) ? $mysqli->real_escape_string(strtoupper($_POST['keyID'])) : '';
        $podID = isset($_POST['podID']) ? $mysqli->real_escape_string(strtoupper($_POST['podID'])) : '';
        $radioCallNum = isset($_POST['radioCallNum']) ? $mysqli->real_escape_string(strtoupper($_POST['radioCallNum'])) : '';
        $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
        
        updateRadioLog($config, $keyLogID, $radioCallNum, $keyID, $podID, $checkOutType);
        $isEditing = true;
    }
    
    if($isEditing){
        if($config->adminLvl >= 0){
            $mysqli = $config->mysqli;
            $myq = "SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.REFNUM = '".$keyLogID."' AND IS_RESERVE=0
                    UNION
                    SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.REFNUM = '".$keyLogID."' AND IS_RESERVE=1
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
                        $depTable[$x][$y] = '<input type="submit" value="Update" name="updateKeyLog'.$x.'" />
                                <input type="submit" value="LogOut" name="logoutKeyLog'.$x.'" /><br/>'; $y++;
                    }
                    else{
                        if($config->adminLvl >=25){
                            $depTable[$x][$y] = '<input type="submit" value="Update" name="updateKeyLog'.$x.'" />
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
                 echo '<br/>Reference #: '.$keyLogID.'<input type="hidden" name="keyLogID" value="'.$keyLogID.'" /><br />
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
                    echo '<input type="submit" name="checkInAllKey" value="Check in All" />';
                }
                else{
                    echo '<input type="submit" name="checkInKey" value="Check Back In" />';
                }
            }
            else{
                echo $row['inTime'].'<br /><br />';
            }
            if(strcmp($row['inTime'],"00/00/0000 0000")==0 || $config->adminLvl >=25){
                if($row['gpID'] != 0)
                    echo '<input type="submit" name="updateKeyLogAll" value="Update All" />';
                else
                    echo '<input type="submit" name="updateKeyLog" value="Update" />';
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
        $keyLogID = isset($_POST['keyLogID']) ? $mysqli->real_escape_string($_POST['keyLogID']) : '';
        $keyID = isset($_POST['keyID']) ? $mysqli->real_escape_string(strtoupper($_POST['keyID'])) : '';
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
            if($_SERVER['REMOTE_ADDR'] != nslookup('mcjcbcast.sheriff.mahoning.local')){
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
        echo '<br/><br/><input type="hidden" name="gpID" value="'.$gpID.'" /> Key Number: ';
        selectKeyInventory($config, "keyID", $keyID);
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
        echo '<br/><input type="hidden" name="checkoutKeyBtn" value="true" />
            <input type="submit" name="addKeyLog" value="Check Out Key" />
            <input type="submit" name="goBtn" value="Cancel" />';
    }
}
function selectKeyInventory($config, $inputName, $selectedValue=false, $onChangeSubmit=false){
        //assumes to be part of a form
    //provides a drop down selection for time type.
    $mysqli = $config->mysqli;
    if($onChangeSubmit)
        echo '<select name="'.$inputName.'" onchange="this.form.submit()">';
    else
        echo '<select name="'.$inputName.'" >';
    echo '<option value=""></option>';
    
    $myq = "SELECT I.IDNUM, I.SERIAL_NUM, I.DESCR, I.PRIORITY_TYPE 
            FROM WTS_INVENTORY I
            WHERE IS_ACTIVE = 1
            AND IS_DEPRECIATED = 0
            AND NOT (SELECT COUNT(CHECKEDOUT) FROM WTS_RADIOLOG WHERE CHECKEDOUT = 1 AND KEY_RING_ID = I.IDNUM) > 0
            AND TYPE = (SELECT IDNUM FROM WTS_INV_TYPE WHERE DESCR = 'Key');";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    
    while($row = $result->fetch_assoc()){
        $itemDesc = '';
        if(!empty($row['DESCR']))
            $itemDesc = ' ('.$row['DESCR'].')';
        if($row['IDNUM'] == $selectedValue)
            echo '<option value="'.$row['IDNUM'].'" SELECTED>'.$row['SERIAL_NUM'].$itemDesc.'</option>';
        else
            echo '<option value="'.$row['IDNUM'].'">'.$row['SERIAL_NUM'].$itemDesc.'</option>';
        
    }
    
    echo '</select>';
}
?>