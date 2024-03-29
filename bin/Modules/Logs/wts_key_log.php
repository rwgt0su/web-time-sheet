<?php

function showKeyLogDetails($config, $keyLogID, $isEditing = false, $isApprove = false, $divID = '') {
    $checkOutKey = isset($_POST['addKeyLog']) ? true : false;
    $checkInKey = isset($_POST['checkInKey']) ? true : false;
    $updateKeyLog = isset($_POST['updateKeyLog']) ? true : false;
    $itemIDs = '';
    $debug = '';

    $mysqli = $config->mysqli;
    $mysqliReserve = connectToSQL($reserveDB = TRUE);

    $num_deputies = isset($_POST['num_deputies']) ? $mysqli->real_escape_string($_POST['num_deputies']) : 0;
    $totalRows = isset($_POST['totalRows']) ? $mysqli->real_escape_string($_POST['totalRows']) : 0;
    $invLogComments = isset($_POST['invLogCommments']) ? $mysqli->real_escape_string(strtoupper($_POST['invLogCommments'])) : '';

    if ($checkOutKey) {
        //get passed values
        echo '<h2><font color="red">Results</font></h2>';
        $debug .= 'checking number of deputies ' . $num_deputies . ' <br/>';
        if ($num_deputies > 0) {
            $podID = isset($_POST['podID']) ? $mysqli->real_escape_string(strtoupper($_POST['podID'])) : '';
            $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;
            $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
            $nextGroupID = 0;

            $gpIDq = "SELECT MAX( GPNUM ) 'gpID' FROM WTS_RADIOLOG";
            $gpResult = $mysqli->query($gpIDq);
            SQLerrorCatch($mysqli, $gpResult);
            $row = $gpResult->fetch_assoc();
            $nextGroupID = $row['gpID'] + 1;

            for ($i = 0; $i < $num_deputies; $i++) {
                $debug .= 'adding deputy id ' . $i . '<br/>';
                $deputyID[$i] = isset($_POST['deputyID' . $i]) ? $mysqli->real_escape_string(strtoupper($_POST['deputyID' . $i])) : false;
                $radioCallNum[$i] = isset($_POST['radioCallNum' . $i]) ? $mysqli->real_escape_string(strtoupper($_POST['radioCallNum' . $i])) : '';
                $isReserve[$i] = isset($_POST['isReserve' . $i]) ? '1' : '0';

                $iCount = 0;
                for ($z = 0; $z < $totalRows; $z++) {
                    $debug .= 'Checkbox id: ' . $z;
                    $itemCheckbox = isset($_POST['itemIDcheckbox' . $z]) ? true : false;
                    if ($itemCheckbox) {
                        $debug .= ' is checked';
                        $itemIDs[$iCount] = $mysqli->real_escape_string(strtoupper($_POST['itemID' . $z]));
                        $itemType[$iCount] = isset($_POST['itemType' . $z]) ? $mysqli->real_escape_string(strtoupper($_POST['itemType' . $z])) : '';
                        $iCount++;
                        $isEditing = true;
                    }
                    $debug .= '<br/>';
                }
                $totalItems = sizeof($itemIDs);

                if ($gpID != 0) {
                    $groupID = $gpID;
                } else {
                    if ($num_deputies == 1) {
                        //Set Group ID to 0 or Individual
                        $groupID = 0;
                    } else if ($i == 0) {
                        $groupID = $nextGroupID;
                    } else {
                        $groupID = $nextGroupID - 1;
                    }
                }
                //if only 1 deputy and multiple items
                if ($groupID == 0 && $totalItems > 1)
                    $groupID = $nextGroupID;

                for ($z = 0; $z < $totalItems; $z++) {
                    $keyLogID = checkOutItem($config, $deputyID[$i], $radioCallNum[$i], $itemIDs[$z], $itemType[$z], $checkOutType, $isReserve[$i], $groupID, $divID);
                }
                echo '<input type="submit" name="goBtn" value="Back To Logs" />';
            }
        } else if (!empty($invLogComments)) {
            $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;
            $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
            $nextGroupID = 0;

            $gpIDq = "SELECT MAX( GPNUM ) 'gpID' FROM WTS_RADIOLOG";
            $gpResult = $mysqli->query($gpIDq);
            SQLerrorCatch($mysqli, $gpResult);
            $row = $gpResult->fetch_assoc();
            $groupID = 0;
            $nextGroupID = $row['gpID'] + 1;
            $iCount = 0;
            for ($z = 0; $z < $totalRows; $z++) {
                $debug .= 'Checkbox id: ' . $z;
                $itemCheckbox = isset($_POST['itemIDcheckbox' . $z]) ? true : false;
                if ($itemCheckbox) {
                    $debug .= ' is checked';
                    $itemIDs[$iCount] = $mysqli->real_escape_string(strtoupper($_POST['itemID' . $z]));
                    $itemType[$iCount] = isset($_POST['itemType' . $z]) ? $mysqli->real_escape_string(strtoupper($_POST['itemType' . $z])) : '';
                    $iCount++;
                    $isEditing = true;
                }
                $debug .= '<br/>';
            }
            $totalItems = sizeof($itemIDs);

            //if only 1 deputy and multiple items
            if ($totalItems > 1)
                $groupID = $nextGroupID;

            for ($z = 0; $z < $totalItems; $z++) {
                $keyLogID = checkOutItem($config, '', '', $itemIDs[$z], $itemType[$z], $checkOutType, '0', $groupID, $divID, false, $invLogComments);
            }
            echo '<input type="submit" name="goBtn" value="Back To Logs" />';
        } else {
            echo 'Must select a user.<br />';
        }
        echo '<br />';
        //popUpMessage($debug);
        //display results and get secLogID just added
    }
    if ($checkInKey) {
        $keyLogID = isset($_POST['keyLogID']) ? $_POST['keyLogID'] : '';
        $hiddenInputs = '<input type="hidden" value="'.$_POST['dateSelect'].'" name="dateSelect">
                        <input type="hidden" name="divisionID" value="'.$_POST['divisionID'].'" /> 
                        <input type="hidden" value="'.$keyLogID.'" name="keyLogID">
                        <input type="hidden" value="true" name="checkInKey">';
        checkInRadioLog($config, $keyLogID, $noLog=false, $hiddenInputs);
        $isEditing = true;
    }

    if ($updateKeyLog) {
        ////get posted values
        $keyLogID = isset($_POST['keyLogID']) ? $mysqli->real_escape_string($_POST['keyLogID']) : '';
        $podID = isset($_POST['podID']) ? $mysqli->real_escape_string(strtoupper($_POST['podID'])) : '';
        $radioCallNum = isset($_POST['radioCallNum']) ? $mysqli->real_escape_string(strtoupper($_POST['radioCallNum'])) : '';
        $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
        $debug .= 'Updating KeyLogID ' . $keyLogID;
        for ($z = 0; $z < $totalRows; $z++) {
            $debug .= 'Checkbox id: ' . $z;
            $itemCheckbox = isset($_POST['itemIDcheckbox' . $z]) ? true : false;
            if ($itemCheckbox) {
                $debug .= ' is checked';
                $itemIDs[$z] = $mysqli->real_escape_string(strtoupper($_POST['itemID' . $z]));
                $itemType[$z] = isset($_POST['itemType' . $z]) ? $mysqli->real_escape_string(strtoupper($_POST['itemType' . $z])) : '';

                updateRadioLog($config, $keyLogID, $radioCallNum, $itemIDs[$z], $checkOutType, $invLogComments);

                $isEditing = true;
            }
            $debug .= '<br/>';
        }
        //popUpMessage($debug);

        $isEditing = true;
    }

    if ($isEditing) {
        $filters = showSelectDivision($config, $divID, "I.");
        if ($config->adminLvl >= 0) {
            $mysqli = $config->mysqli;
            $myq = "SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime', R.COMMENTS
                    FROM WTS_RADIOLOG R
                    JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.REFNUM = '" . $keyLogID . "' AND IS_RESERVE=0
                    UNION
                    SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime', R.COMMENTS
                    FROM WTS_RADIOLOG R
                    JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.REFNUM = '" . $keyLogID . "' AND IS_RESERVE=1
                    ";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            $row = $result->fetch_assoc();
            if ($row['gpID'] != 0 && false) {
                //get all users
                echo '<div align="center">Group Reference #: ' . $row['gpID'] . '
                    <input type="hidden" name="gpID" value="' . $row['gpID'] . '" /></div>';

                $newq = "SELECT R.REFNUM 'refNum', R.GPNUM 'gpID', 
                        CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, R.COMMENTS,
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.GPNUM = '" . $row['gpID'] . "' AND IS_RESERVE=0
                    UNION
                    SELECT R.REFNUM 'refNum', R.GPNUM 'gpID', 
                        CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, R.COMMENTS, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.GPNUM = '" . $row['gpID'] . "' AND IS_RESERVE=1";
                $newResult = $mysqli->query($newq);
                SQLerrorCatch($mysqli, $newResult, $newq);

                $x = 0;
                $y = 0;
                $depTable = array(array());
                $selectedRows = array();
                $sRows = 0;
                $depTable[$x][$y] = "Deputy";
                $y++;
                $depTable[$x][$y] = "Radio#";
                $y++;

                $x++;
                while ($newRow = $newResult->fetch_assoc()) {
                    $y = 0;
                    $lastDeputy = false;
                    for ($t = 0; $t < sizeof($depTable); $t++) {
                        if ($newRow['DEPUTYNAME'] == $depTable[$t][0]) {
                            $lastDeputy = true;
                            break;
                        }
                    }
                    if (!$lastDeputy && !empty($newRow['DEPUTYNAME'])) {
                        $depTable[$x][$y] = $newRow['DEPUTYNAME'];
                        $y++;
                        $depTable[$x][$y] = '<input type="text" name="radioCallNum' . $x . '" value="' . $newRow['RADIO_CALLNUM'] . '" />';
                        $y++;
                        $x++;
                    }
                    //echo '<option value="'.$selectedValue.'" SELECTED>'.$row['SERIAL_NUM'].$itemDesc.'</option>';
                    $selectedRows[$sRows] = $newRow['refNum'];

                    $sRows++;
                }
                if (sizeof($depTable) > 0)
                    showSortableTable($depTable, 0);
                else
                    echo 'Comments (include person\'s name and company): <input size=50 name="invLogCommments" value="' . $invLogComments . '"/><br/><Br/>';
                selectInventory($config, $selectedRows, $filters);
            }
            else {
                echo '<br/>Reference #: ' . $keyLogID . '<input type="hidden" name="keyLogID" value="' . $keyLogID . '" /><br />';
                if ($row['DEPUTYNAME'] == "SYSTEM, USER") {
                    echo ' Comments (include person\'s name and company): <br/><input size=50 name="invLogCommments" value="' . $row['COMMENTS'] . '"/><br/><Br/>';
                } else {
                    echo 'Deputy: ' . $row['DEPUTYNAME'] . '
                    Radio#: <input type="text" name="radioCallNum" value="' . $row['RADIO_CALLNUM'] . '" /><br/>
                        ';
                }
                $selectedRows[0] = $keyLogID;
                selectInventory($config, $selectedRows, $filters);
            }

            //selectRadioInventory($config, "radioID", $row['RADIOID']);
            echo '<br/><br/>';
            if ($row['TYPE'] == "LOANER")
                echo '<input type="radio" name="checkOutType" value="LOANER" CHECKED>LOANER</input>';
            else
                echo '<input type="radio" name="checkOutType" value="LOANER">LOANER</input>';
            if ($row['TYPE'] == "SHIFT")
                echo '<input type="radio" name="checkOutType" value="SHIFT" CHECKED>SHIFT ASSIGNMENT</input><br/>';
            else
                echo '<input type="radio" name="checkOutType" value="SHIFT">SHIFT ASSIGNMENT</input>';
            if ($config->adminLvl >= 25) {
                if ($row['TYPE'] == "PERM")
                    echo '<input type="radio" name="checkOutType" value="PERM" CHECKED>PERMANENT</input>';
                else
                    echo '<input type="radio" name="checkOutType" value="PERM">PERMANENT</input>';
            }
            echo '<br/><br/>Checked in time: ';
            if (strcmp($row['inTime'], "00/00/00 0000") == 0) {
                echo "<font color=red><b>Not Checked back in Yet</b></font><br /><br />";
                echo '<input type="submit" name="checkInKey" value="Check Back In" />';
            } else {
                echo '<font color=red>'.$row['inTime'] . '</font><br /><br />';
            }
            if (strcmp($row['inTime'], "00/00/00 0000") == 0 || $config->adminLvl >= 25) {
                echo '<input type="submit" name="updateKeyLog" value="Update" />';
            }
            if ($isApprove)
                echo '<input type="submit" name="backToApprove" value="Back To Approvals" />';
            else
                echo '<input type="submit" name="goBtn" value="Back To Logs" />';
        }
        else {
            echo 'Access Denied';
        }
    }
    if (!$isEditing && !isset($_POST['goBtn'])) {
        $filters = showSelectDivision($config, $divID, "I.");
        echo '<br/>';
        $keyLogID = isset($_POST['keyLogID']) ? $mysqli->real_escape_string($_POST['keyLogID']) : '';
        $keyID = isset($_POST['keyID']) ? $mysqli->real_escape_string(strtoupper($_POST['keyID'])) : '';
        $podID = isset($_POST['podID']) ? $mysqli->real_escape_string(strtoupper($_POST['podID'])) : '';
        $checkOutType = isset($_POST['checkOutType']) ? $mysqli->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
        $invLogComments = isset($_POST['invLogCommments']) ? $mysqli->real_escape_string(strtoupper($_POST['invLogCommments'])) : '';

        //debug
        //var_dump($_POST);
        //Show previously added deputies
        $deputyCount = 0;
        if ($num_deputies > 0) {
            for ($i = 0; $i < $num_deputies; $i++) {
                if (!isset($_POST['removeDeputyBtn' . $i])) {
                    $deputyID[$i] = isset($_POST['deputyID' . $i]) ? $mysqli->real_escape_string(strtoupper($_POST['deputyID' . $i])) : '';
                    $isReserve[$i] = isset($_POST['isReserve' . $i]) ? true : false;

                    //get this user's information
                    if ($isReserve[$i]) {
                        $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM RESERVE WHERE IDNUM=' . $deputyID[$i];
                        $result = $mysqliReserve->query($myq);
                        SQLerrorCatch($mysqliReserve, $result);
                        $row = $result->fetch_assoc();
                    } else {
                        $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM EMPLOYEE WHERE IDNUM=' . $deputyID[$i];
                        $result = $mysqli->query($myq);
                        SQLerrorCatch($mysqli, $result);
                        $row = $result->fetch_assoc();
                    }
                    if ($i == 0)
                        $phone = $row['CELLPH'];
                    echo 'Deputy: <input type="hidden" name="deputyID' . $deputyCount . '" value="' . $deputyID[$i] . '" />';
                    if ($isReserve[$i] == 1)
                        echo '<input type="hidden" name="isReserve' . $deputyCount . '" value="true" />';
                    echo $row['LNAME'] . ', ' . $row['FNAME'];
                    echo ';  Radio Call #: <input type="hidden" name="radioCallNum' . $deputyCount . '" value="' . $row['RADIO'] . '" />' . $row['RADIO'];
                    echo '<input type="submit" name="removeDeputyBtn' . $deputyCount . '" value="Remove" />';
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
        $foundUserID = '';
        if ($totalRows > 0) {
            //get post info providied from search results
            for ($i = 0; $i <= $totalRows; $i++) {
                if (isset($_POST['foundUser' . $i])) {
                    $foundUserFNAME = $_POST['foundUserFNAME' . $i];
                    $foundUserLNAME = $_POST['foundUserLNAME' . $i];
                    $foundUserName = $_POST['foundUserName' . $i];
                    $foundUserID = $_POST['foundUserID' . $i];

                    if (isset($_POST['isReserve' . $i]))
                        $foundUserIsReserve = true;
                    else
                        $foundUserIsReserve = false;
                    break;
                }//end if
            }//end for
        }
        if (empty($foundUserID) && $num_deputies == 0) {
            //security check for central control computer
            if ($_SERVER['REMOTE_ADDR'] != nslookup('WSRF14900.mahoningcountyoh.gov')) { //'10.1.32.72'
                //Default first deputy to logged in user on first load
                $foundUserID = $_SESSION['userIDnum'];
                $foundUserIsReserve = false;
            }
        }
        if (!empty($foundUserID)) {
            if ($foundUserIsReserve) {
                $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM RESERVE WHERE IDNUM=' . $foundUserID;
                $result = $mysqliReserve->query($myq);
                SQLerrorCatch($mysqliReserve, $result);
            } else {
                $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM EMPLOYEE WHERE IDNUM=' . $foundUserID;
                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result);
            }

            $row = $result->fetch_assoc();
            if ($deputyCount == 0)
                $phone = $row['CELLPH'];
            echo 'Deputy: <input type="hidden" name="deputyID' . $deputyCount . '" value="' . $foundUserID . '" />';
            if ($foundUserIsReserve)
                echo '<input type="hidden" name="isReserve' . $deputyCount . '" value="true" />';
            echo $row['LNAME'] . ', ' . $row['FNAME'];
            echo ';  Radio Call#: <input name="radioCallNum' . $deputyCount . '" value="' . $row['RADIO'] . '" />';
            echo '<input type="submit" name="removeDeputyBtn' . $deputyCount . '" value="Remove" />';
            echo '<br/>';
            $deputyCount++;
        }
        if (empty($foundUserID) && $deputyCount == 0) {
            //If no deputy
            echo 'Add Deputy: ';
            displayUserLookup($config);
            echo ' <br/><br/>or Comments (include person\'s name and company): <input size=50 name="invLogCommments" value="' . $invLogComments . '"/>';
        }
        echo '<input type="hidden" name="num_deputies" value="' . $deputyCount . '" />';

        $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;
        echo '<br/><br/><input type="hidden" name="gpID" value="' . $gpID . '" />';
        selectInventory($config, $itemIDs, $filters);
        echo '<br/><br/>';
        if ($checkOutType == "LOANER" || empty($checkOutType))
            echo '<input type="radio" name="checkOutType" value="LOANER" CHECKED>LOANER</input>';
        else
            echo '<input type="radio" name="checkOutType" value="LOANER">LOANER</input>';
        if ($checkOutType == "SHIFT")
            echo '<input type="radio" name="checkOutType" value="SHIFT" CHECKED>SHIFT ASSIGNMENT</input><br/>';
        else
            echo '<input type="radio" name="checkOutType" value="SHIFT">SHIFT ASSIGNMENT</input>';
        if ($config->adminLvl >= 25) {
            if ($checkOutType == "PERM")
                echo '<input type="radio" name="checkOutType" value="PERM" CHECKED>PERMANENT</input>';
            else
                echo '<input type="radio" name="checkOutType" value="PERM">PERMANENT</input>';
        }
        echo '<br/><br/><input type="hidden" name="checkoutKeyBtn" value="true" />
            <input type="submit" name="addKeyLog" value="Check Out Selected Items" />
            <input type="submit" name="goBtn" value="Cancel" />';
    }
}

function selectInventory($config, $selectedValues = false, $filters = '', $selectOnly = false, $myInvView = false, $tableHeight = 200) {
    //assumes to be part of a form
    //provides a drop down selection for time type.
    $mysqli = $config->mysqli;
    $theTable = array(array());
    $x = 0;
    $y = 0;
    $selectedRow = array(array());
    $counter = 1;

//    if($onChangeSubmit)
//        echo '<select name="'.$inputName.'" onchange="this.form.submit()">';
//    else
//        echo '<select name="'.$inputName.'" >';

    $theTable[$x][$y] = "";
    $y++;
    $theTable[$x][$y] = "Type";
    $y++;
    $theTable[$x][$y] = "Item ID";
    $y++;
    $theTable[$x][$y] = "Description";
    $y++;
    $theTable[$x][$y] = "Priority Type";
    $y++;
    if ($myInvView) {
        $theTable[$x][$y] = "Check Out Type";
        $y++;
    }
    $x++;
    $y = 0;

    if ($selectedValues) {
        for ($z = 0; $z < sizeof($selectedValues); $z++) {
            $myq = "SELECT I.IDNUM, OTHER_SN,  T.DESCR 'itemType', 
                    CONCAT(I.DESCR, ' ', I.YEAR, ' ', T.MANUFACTURER, ' ', T.MODEL,' ', I.COLOR) 'itemDescr', I.CAR_NO 'CAR_NO', 
                    PRIORITY_TYPE, RLOG.TYPE 'checkOutType'
                FROM WTS_INVENTORY I
                JOIN WTS_INV_TYPE AS T ON T.IDNUM=I.TYPE
                RIGHT JOIN WTS_RADIOLOG AS RLOG ON RLOG.RADIOID=I.IDNUM
                WHERE I.IDNUM=(SELECT R.RADIOID FROM WTS_RADIOLOG R WHERE REFNUM='" . $selectedValues[$z] . "')
                AND I.TYPE=(SELECT RL.ITEM_TYPE_ID FROM WTS_RADIOLOG RL WHERE REFNUM='" . $selectedValues[$z] . "')
                " . $filters . "    ;";

            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result, $myq);
            $row = $result->fetch_assoc();
            $carNo = '';
            if (!empty($row['CAR_NO'])) {
                $carNo = " Car #" . $row['CAR_NO'];
            }
            //echo '<option value="'.$selectedValue.'" SELECTED>'.$row['SERIAL_NUM'].$itemDesc.'</option>';
            $selectedRow[$z][$y] = '<input type="hidden" name="itemID' . $counter . '"  value="' . $row['IDNUM'] . '" />';
            if (!$myInvView)
                $selectedRow[$z][$y] .= '<input type="checkbox" CHECKED name="itemIDcheckbox' . $counter . '" onclick="Move(this,' . $counter . ');" />' . $carNo;
            else
                $selectedRow[$z][$y] .= $carNo;
            $y++;
            $selectedRow[$z][$y] = '<input type="hidden" name="itemType' . $counter . '"  value="' . $row['itemType'] . '" />' . $row['itemType'];
            $y++;
            $selectedRow[$z][$y] = $row['OTHER_SN'];
            if (!empty($row['CAR_NO'])) {
                $selectedRow[$z][$y] .= '<br/><input type="submit" name="carDetails' . $counter . '" value="More Details" />';
            }
            $y++;
            $selectedRow[$z][$y] = $row['itemDescr'];
            $y++;
            $selectedRow[$z][$y] = $row['PRIORITY_TYPE'];
            $y++;
            if ($myInvView) {
                $selectedRow[$z][$y] = $row['checkOutType'];
                $y++;
            }
            $y = 0;
            $counter++;
        }
    } else {
        echo '<option value=""></option>';
        $selectedRow = '';
    }
    if (!$selectOnly) {
        $myq = "SELECT I.IDNUM, I.TYPE, T.DESCR 'itemType', 
                CONCAT(I.DESCR, ' ', I.YEAR, ' ', T.MANUFACTURER, ' ', T.MODEL, ' ', I.COLOR) 'DESCR',
                I.OTHER_SN, I.PRIORITY_TYPE, I.CAR_NO 'CAR_NO' 
            FROM WTS_INVENTORY I
            JOIN WTS_INV_TYPE AS T ON T.IDNUM=I.TYPE
            WHERE I.IS_ACTIVE = 1
            AND I.IS_DEPRECIATED = 0
            AND I.QUANTITY_AVAILABLE > 0
            " . $filters . " ";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result, $myq);

        //DISPLAY AVAILABLE ITEMS
        while ($row = $result->fetch_assoc()) {
            $itemDesc = '';
            if (!empty($row['DESCR']))
                $itemDesc = ' ' . $row['DESCR'] . '';
            $carNo = '';
            if (!empty($row['CAR_NO']))
                $carNo = 'Car #' . $row['CAR_NO'];
            $theTable[$x][$y] = '<input type="hidden" name="itemID' . $counter . '"  value="' . $row['IDNUM'] . '" />
                    <input type="checkbox" name="itemIDcheckbox' . $counter . '" onclick="Move(this,' . $counter . ');" /> ' . $carNo;
            $y++;
//            }
            $theTable[$x][$y] = '<input type="hidden" name="itemType' . $counter . '"  value="' . $row['TYPE'] . '" />' . $row['itemType'];
            $y++;
            $theTable[$x][$y] = $row['OTHER_SN'];
            if (!empty($row['CAR_NO'])) {
                $theTable[$x][$y] .= '<br/><input type="submit" name="carDetails' . $counter . '" value="More Details" />';
            }
            $y++;
            $theTable[$x][$y] = $itemDesc;
            $y++;
            $theTable[$x][$y] = $row['PRIORITY_TYPE'];
            $y++;
            $x++;
            $counter++;
            $y = 0;
        }
        $checkedoutq = "SELECT I.IDNUM, I.TYPE, T.DESCR 'itemType', 
                CONCAT(I.DESCR, ' ', I.YEAR, ' ', T.MANUFACTURER, ' ', T.MODEL,' ', I.COLOR) 'DESCR',
                I.OTHER_SN, I.PRIORITY_TYPE , I.CAR_NO 'CAR_NO', 
                R.CHECKEDOUT, R.TYPE 'checkoutType', CONCAT_WS(', ', E.LNAME, E.FNAME) 'DEPUTYNAME',
                R.REFNUM
            FROM WTS_INVENTORY I
            LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=I.TYPE
            RIGHT JOIN WTS_RADIOLOG R ON R.RADIOID=I.IDNUM
            LEFT JOIN EMPLOYEE E ON E.IDNUM=R.DEPUTYID
            WHERE I.IS_ACTIVE = 1
            AND I.IS_DEPRECIATED = 0
            AND R.CHECKEDOUT = 1
            " . $filters . "
            GROUP BY I.IDNUM
            ORDER BY I.OTHER_SN, T.DESCR DESC
            ;";
        $result = $mysqli->query($checkedoutq);
        SQLerrorCatch($mysqli, $result, $checkedoutq);

        //DISPLAY CHECKED OUT ITEMS FOR EXCHANGE
        while ($row = $result->fetch_assoc()) {
            $itemDesc = '';
            if (!empty($row['DESCR']))
                $itemDesc = ' (' . $row['DESCR'] . ')';
            $carNo = '';
            if (!empty($row['CAR_NO']))
                $carNo = 'Car #' . $row['CAR_NO'];
            if ($row['CHECKEDOUT']) {
                $theTable[$x][$y] = '<input type="hidden" name="itemID' . $counter . '"  value="' . $row['IDNUM'] . '" />
                    <input type="hidden" name="refNum' . $counter . '"  value="' . $row['REFNUM'] . '" />';
                if ($row['checkoutType'] != 'PERM' || ($config->adminLvl > 25))
                    $theTable[$x][$y] .= '<input type="submit" name="exchangeBtnINV' . $counter . '" value="Exchange" />' . $carNo;
                $y++;
                $itemDesc = $row['checkoutType'] . ' CHECKOUT  by <br/>' . $row['DEPUTYNAME'];
            }
//            else{
//                $theTable[$x][$y] = '<input type="hidden" name="itemID'.$counter.'"  value="'.$row['IDNUM'].'" />
//                    <input type="checkbox" name="itemIDcheckbox'.$counter.'" onclick="Move(this,'.$counter.');" />'; $y++; 
//            }
            $theTable[$x][$y] = '<input type="hidden" name="itemType' . $counter . '"  value="' . $row['TYPE'] . '" />' . $row['itemType'];
            $y++;
            $theTable[$x][$y] = $row['OTHER_SN'];
            if (!empty($row['CAR_NO'])) {
                $theTable[$x][$y] .= '<br/><input type="submit" name="carDetails' . $counter . '" value="More Details" />';
            }
            $y++;
            $theTable[$x][$y] = $itemDesc;
            $y++;
            $theTable[$x][$y] = $row['PRIORITY_TYPE'];
            $y++;
            $x++;
            $counter++;
            $y = 0;
        }
    }

    moveTablesOnSelect($theTable, $selectedRow, $rowToSort = 0, $selectOnly, $tableHeight);
    //echo '</select>';
}

function showInventoryGroups($config, $keyLogID, $deputyID = '') {
    $mysqli = $config->mysqli;
    if (!empty($deputyID)) {
        $myq = "SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                    R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                FROM WTS_RADIOLOG R
                JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
                WHERE R.DEPUTYID = '" . $deputyID . "'
                    AND CHECKEDOUT=1 
                    AND IS_RESERVE=0
                UNION
                SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                    R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                FROM WTS_RADIOLOG R
                JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
                WHERE R.DEPUTYID = '" . $deputyID . "'
                    AND CHECKEDOUT=1 
                    AND IS_RESERVE=1
                ";
    } else {
        $myq = "SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                    R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                FROM WTS_RADIOLOG R
                JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
                WHERE R.DEPUTYID = (SELECT RL.DEPUTYID FROM WTS_RADIOLOG RL WHERE RL.REFNUM='" . $keyLogID . "') 
                    AND CHECKEDOUT=1 
                    AND IS_RESERVE=0
                UNION
                SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                    R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                FROM WTS_RADIOLOG R
                JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
                WHERE R.DEPUTYID = (SELECT RL.DEPUTYID FROM WTS_RADIOLOG RL WHERE RL.REFNUM='" . $keyLogID . "') 
                    AND CHECKEDOUT=1 
                    AND IS_RESERVE=1
                ";
    }
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    //get all users
    $selectedRows = array();
    $sRows = 0;

    while ($newRow = $result->fetch_assoc()) {
        if ($sRows == 0) {
            echo '<br/><br/><div align="center"><h3>Items Currently Checked Out By:</h3></div>Deputy:
                    ' . $newRow['DEPUTYNAME'];
            echo ';  Radio Call#: ' . $newRow['RADIO_CALLNUM'];
            echo '<br/><br/>';
        }
        //echo '<option value="'.$selectedValue.'" SELECTED>'.$row['SERIAL_NUM'].$itemDesc.'</option>';
        $selectedRows[$sRows] = $newRow['REFNUM'];

        $sRows++;
    }
    selectInventory($config, $selectedRows, $filters = '', true, true);
    echo '<input type="submit" name="goBtn" value="Back To Logs" />';
}

function showMyInventory($config) {
    $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
    for ($i = 1; $i <= $totalRows; $i++) {
        if(isset($_POST['carDetails'.$i])){
            showItemDetails($config, $_POST['itemID' . $i]);
            break;
        }
    }
    $mysqli = $config->mysqli;
    $myq = "SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
            FROM WTS_RADIOLOG R
            JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
            WHERE R.DEPUTYID = '" . $_SESSION['userIDnum'] . "'
                AND CHECKEDOUT=1 
                AND IS_RESERVE=0
            UNION
            SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
            FROM WTS_RADIOLOG R
            JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
            WHERE R.DEPUTYID = '" . $_SESSION['userIDnum'] . "'
                AND CHECKEDOUT=1 
                AND IS_RESERVE=1
            ";

    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result, $myq);
    //get all users
    $selectedRows = array();
    $sRows = 0;
    echo '<form method=POST>';
    while ($newRow = $result->fetch_assoc()) {
        if ($sRows == 0) {
            echo '<br/><br/><div align="center"><h3>Items Currently Checked Out By:</h3></div>Deputy:
                    ' . $newRow['DEPUTYNAME'];
            echo ';  Radio Call#: ' . $newRow['RADIO_CALLNUM'];
            echo '<br/><br/>';
        }
        //echo '<option value="'.$selectedValue.'" SELECTED>'.$row['SERIAL_NUM'].$itemDesc.'</option>';
        $selectedRows[$sRows] = $newRow['REFNUM'];

        $sRows++;
    }
    selectInventory($config, $selectedRows, $filters = '', true, $invView = true, $height = 400);
    echo '</form>';
}

function showQuickSearch() {
    $kwd_search = isset($_POST['kwd_search']) ? $_POST['kwd_search'] : '';
    echo '<br/><div align="center">Quick Search: <input name="kwd_search" type="text" id="kwd_search" value="'.$kwd_search.'"/></div>
        <table id="quickSearch" class="sortable"></table>';
    echo '    
        <link rel="stylesheet" type="text/css" href="bin/jQuery/css/smoothness/jquery-ui-1.8.4.custom.css" id="link"/>
        <link rel="stylesheet" type="text/css" href="bin/jQuery/css/base.css" />';
    //echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    //     <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/jquery-ui.min.js"></script>';
    echo '
        <script type="text/javascript" src="bin/jQuery/js/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="bin/jQuery/js/jquery-ui-1.8.4.min.js"></script>
        ';
    echo '<script type="text/javascript" src="bin/jQuery/js/highlighter/codehighlighter.js"></script>	
        <script type="text/javascript" src="bin/jQuery/js/highlighter/javascript.js"></script>			
        <script type="text/javascript" src="bin/jQuery/js/jquery.fixheadertable.min.js"></script>
        
<script type="text/javascript"> 
       $(document).ready(function(){
            // Write on keyup event of keyword input element
            //copy all tables to quickSearch table Cache
            var cloneTable = $("#LOANER").html();
            $("#quickSearch").append(cloneTable);
            var cloneTable = $("#SHIFT").html();
            $("#quickSearch").append(cloneTable);
            var cloneTable = $("#PERM").html();
            $("#quickSearch").append(cloneTable);
            $("#quickSearch tbody>tr").hide();

            $("#kwd_search").keyup(function(){
                // When value of the input is not blank
                if( $(this).val() != "")
                {
                    // Show only matching TR, hide rest of them
                    //$("#LOANER tbody>tr").hide();
                    $("#quickSearch tbody>tr").hide();
                    $("#quickSearch td:contains-ci(\'" + $(this).val() + "\')").parent("tr").show();
                    //var foundRow = $("#LOANER td:contains-ci(\'" + $(this).val() + "\')").parent("tr").html();
                    //$("#quickSearch").append(foundRow);
                    //$("#quickSearch tr").append();
                    //$("#LOANER td:contains-ci(\'" + $(this).val() + "\')").parent("tr").show();
                }
                else
                {
                    // When there is no input or clean again, show everything back
                    //$("#LOANERtbody>tr").show();
                    $("#quickSearch tbody>tr").hide();
                }
            });
        });
        // jQuery expression for case-insensitive filter
        $.extend($.expr[":"], 
        {
            "contains-ci": function(elem, i, match, array) 
            {
                return (elem.textContent || elem.innerText || $(elem).text() || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
            }
        });
        </script>';
}

function showSelectDivision($config, $myDivID, $table = "R.") {
    if ($config->adminLvl >= 0) {
        echo '<div align="center">Show for the following division: 
            <select name="divisionID" onchange="this.form.submit()">';

        if (isset($_POST['divisionID'])) {
            $myDivID = $_POST['divisionID'];
        } else {
            if ($config->adminLvl >= 50) {
                $myDivID = "All";
            } else {
                $mydivq = "SELECT DIVISIONID FROM EMPLOYEE E WHERE E.IDNUM='" . $config->mysqli->real_escape_string($_SESSION['userIDnum']) . "'";
                $myDivResult = getQueryResult($config, $mydivq, $debug = false);

                $temp = $myDivResult->fetch_assoc();
                $myDivID = $temp['DIVISIONID'];
            }
        }

        $alldivq = "SELECT * FROM `DIVISION`";
        $allDivResult = getQueryResult($config, $alldivq, $debug = false);
        while ($Divrow = $allDivResult->fetch_assoc()) {
            echo '<option value="' . $Divrow['DIVISIONID'] . '"';
            if ($Divrow['DIVISIONID'] == $myDivID)
                echo ' SELECTED ';
            echo '>' . $Divrow['DESCR'] . '</option>';
        }
        if ($config->adminLvl >= 25) {
            if (strcmp($myDivID, "All") == 0)
                echo '<option value="All" SELECTED>All</option>';
            else
                echo '<option value="All">All</option>';
        }
        echo '</select><br/><br/>';
//            if ($myDivID != "All")
//                $this->showShiftDropDown($myDivID, $onChangeSubmit = true);
        echo '</div>';
        $filters = getFilerDivision($config, $myDivID, $table);
        //popupmessage($filters);

        return $filters;
    }
}

function showItemDetails($config, $itemID) {
    //Inventory Information on item
    $myq = "SELECT I.IDNUM, I.TYPE, T.MANUFACTURER 'MAKE', T.MODEL 'MODEL',
            I.OTHER_SN, I.DESCR, I.PRIORITY_TYPE , I.SERIAL_NUM 'CAR_NO', I.COLOR 'COLOR', I.YEAR 'YEAR',
            I.VIN 'VIN', I.LICENSE 'LICENSE'
            FROM WTS_INVENTORY I
            LEFT JOIN WTS_INV_TYPE AS T ON T.IDNUM=I.TYPE
            WHERE I.IDNUM = '" . $itemID . "'";

    $result = $config->mysqli->query($myq);
    SQLerrorCatch($config->mysqli, $result, $myq);
    
    //Millage information on Veh
    $vehMilq = "SELECT M.IDNUM, DATE, MILAGE, CONCAT(E.LNAME, ', ', E.FNAME) 'AddedBy'
            FROM WTS_VEH_MILAGE M
            LEFT JOIN EMPLOYEE AS E ON E.IDNUM = M.AUDIT_ID
            WHERE INV_ID = '" . $itemID . "' ORDER BY AUDIT_TS DESC";

    $vehMilresult = $config->mysqli->query($vehMilq);
    SQLerrorCatch($config->mysqli, $vehMilresult, $vehMilq);
    
    //Veh Notes
    $vehNotesq = "SELECT N.IDNUM, DATE, NOTES, CONCAT(E.LNAME, ', ', E.FNAME) 'AddedBy'
            FROM WTS_VEH_NOTES N
            LEFT JOIN EMPLOYEE AS E ON E.IDNUM = N.AUDIT_ID
            WHERE INV_ID = '" . $itemID . "' ORDER BY AUDIT_TS DESC";

    $vehNoteresult = $config->mysqli->query($vehNotesq);
    SQLerrorCatch($config->mysqli, $vehNoteresult, $vehNotesq);
    
    $itemDetails = '';

    $row = $result->fetch_assoc();
    $itemDetails .= 'Car Number: <font color="blue">'.$row['CAR_NO'].'</font><br/>';
    $itemDetails .= 'Year: '.$row['YEAR'].'<br/>';
    $itemDetails .= 'Make: '.$row['MAKE'].'<br/>';
    $itemDetails .= 'Model: '.$row['MODEL'].'<br/>';
    $itemDetails .= 'Color: '.$row['COLOR'].'<br/>';
    $itemDetails .= 'Description: '.$row['DESCR'].'<br/><br/>';
    $itemDetails .= 'VIN: '.$row['VIN'].'<br/>';
    $itemDetails .= 'License: '.$row['LICENSE'].'<br/>';
    $itemDetails .= '<hr/>';
    $itemDetails .= '<h4>Milage Log</h4>';
    while($vehMilage = $vehMilresult->fetch_assoc()){
        $itemDetails .= 'Milage is <font color="blue">'.$vehMilage['MILAGE'].'</font> - Reported by '.$vehMilage['AddedBy'].' on <font color="dark">'.$vehMilage['DATE'].'</font><br/>';
    }
    $itemDetails .= '<BR/>';
    $itemDetails .= '<hr/>';
    $itemDetails .= '<h4>Upcoming Maintenance</h4>';
    $itemDetails .= '<h6><font color="red">Oil Change PAST Due - demo system message</font></h6>';
    $itemDetails .= 'Transmission Flush due on 6/3/2013 - Reported by demo_system_message<br/>';
    $itemDetails .= '<BR/>';
    $itemDetails .= '<hr/>';
    $itemDetails .= '<h4>Notes</h4>';
    while($vehNotes = $vehNoteresult->fetch_assoc()){
        $itemDetails .= '<font color="blue">'.$vehNotes['NOTES'].'</FONT> - Reported by '.$vehNotes['AddedBy'].' on <font color="dark">'.$vehNotes['DATE'].'</font><br/>';
    }
    $itemDetails .= '<BR/>';    

    popupmessage($itemDetails, $title='Details for '.$row['OTHER_SN'], $width='600');
}
function vehUpdateHistory($config, $vehID, $vmilage, $vIssues, $vDate=''){
    if(!empty($vDate))
        $vDate = "'" . $vDate . "'";
    else
        $vDate = "NOW()";
    $myq = "INSERT INTO `WTS_VEH_MILAGE`(
                `IDNUM`, `INV_ID`, `MILAGE`, `DATE`, `AUDIT_ID`, `AUDIT_TS`, `AUDIT_IP`) 
            VALUES ('','".$vehID."','".$vmilage."', ".$vDate." ,
                '".$_SESSION['userIDnum']."',NOW(),INET_ATON('".$_SERVER['REMOTE_ADDR']."')); ";
    $result = $config->mysqli->query($myq);
    SQLerrorCatch($config->mysqli, $result, $myq, $debug=false);
    
    if(!empty($vIssues)){
        $myq = "INSERT INTO `WTS_VEH_NOTES`(
                    `IDNUM`, `INV_ID`, `NOTES`, `DATE`, `AUDIT_ID`, `AUDIT_TS`, `AUDIT_IP`) 
                VALUES ('','".$vehID."','".$vIssues."',".$vDate.",
                    '".$_SESSION['userIDnum']."',NOW(),INET_ATON('".$_SERVER['REMOTE_ADDR']."'));";
        $result = $config->mysqli->query($myq);
        SQLerrorCatch($config->mysqli, $result, $myq);
    }

    
}

?>