<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of wts_inv_log
 *
 * @author aturner
 */
class wts_inv_log {

    public $config;
    public $mysqliReserve;
    public $checkOutRadio;
    public $checkInRadio;
    public $updateRadioLog;
    public $num_deputies;
    public $totalRows;

    public function wts_inv_log($config) {
        $this->config = $config;
        $this->mysqliReserve = connectToSQL($reserveDB = TRUE);
    }
    
    public function getPOSTS(){
        $this->checkOutRadio = isset($_POST['addRadioLog']) ? true : false;
        $this->checkInRadio = isset($_POST['checkInRadio']) ? true : false;
        $this->updateRadioLog = isset($_POST['updateRadioLog']) ? true : false;
        $this->num_deputies = isset($_POST['num_deputies']) ? $_POST['num_deputies'] : 0;
        $this->totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
    }

    public function showRadioLogDetails($config, $radioLogID, $isEditing = false, $isApprove = false) {
        



        if ($this->checkOutRadio) {
            //get passed values
            echo '<h2><font color="red">Results</font></h2>';
            if ($this->num_deputies > 0) {
                for ($i = 0; $i < $this->num_deputies; $i++) {
                    $this->deputyID[$i] = isset($_POST['deputyID' . $i]) ? $this->config->mysqli->real_escape_string(strtoupper($_POST['deputyID' . $i])) : false;
                    $this->radioCallNum[$i] = isset($_POST['radioCallNum' . $i]) ? $this->config->real_escape_string(strtoupper($_POST['radioCallNum' . $i])) : '';
                    $this->isReserve[$i] = isset($_POST['isReserve' . $i]) ? '1' : '0';
                }

                $this->radioID = isset($_POST['radioID']) ? $this->config->real_escape_string(strtoupper($_POST['radioID'])) : '';
                $this->podID = isset($_POST['podID']) ? $this->config->real_escape_string(strtoupper($_POST['podID'])) : '';
                $this->checkOutType = isset($_POST['checkOutType']) ? $this->config->real_escape_string(strtoupper($_POST['checkOutType'])) : '';
                $this->gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;

                for ($i = 0; $i < $this->num_deputies; $i++) {
                    $gpIDq = "SELECT MAX( GPNUM ) 'gpID' FROM WTS_RADIOLOG";
                    $gpResult = $this->config->query($gpIDq);
                    SQLerrorCatch($this->config->mysqli, $gpResult);
                    $row = $gpResult->fetch_assoc();
                    if ($this->gpID != 0) {
                        $groupID = $this->gpID;
                    } else {
                        $groupID = 0;
                        if ($num_deputies == 1) {
                            //Set Group ID to 0 or Individual
                        } else if ($i == 0) {
                            $groupID = $row['gpID'] + 1;
                        } else {
                            $groupID = $row['gpID'];
                        }
                    }

                    checkOutItem($this->config, $this->deputyID[$i], $this->radioCallNum[$i], $this->radioID, $this->checkOutType, $this->isReserve[$i], $this->groupID);
                }
            } else {
                echo 'Must select a user.<br />';
            }
            echo '<br />';

            //display results and get secLogID just added
        }
        if ($this->checkInRadio) {
            $this->radioLogID = isset($_POST['radioLogID']) ? $_POST['radioLogID'] : '';
            checkInRadioLog($this->config, $this->radioLogID);
            $this->isEditing = true;
        }

        if ($this->updateRadioLog) {
            ////get posted values
            $this->radioLogID = isset($_POST['radioLogID']) ? $this->config->real_escape_string($_POST['radioLogID']) : '';
            $this->radioID = isset($_POST['radioID']) ? $this->config->real_escape_string(strtoupper($_POST['radioID'])) : '';
            $this->podID = isset($_POST['podID']) ? $this->config->real_escape_string(strtoupper($_POST['podID'])) : '';
            $this->radioCallNum = isset($_POST['radioCallNum']) ? $this->config->real_escape_string(strtoupper($_POST['radioCallNum'])) : '';
            $this->checkOutType = isset($_POST['checkOutType']) ? $this->config->real_escape_string(strtoupper($_POST['checkOutType'])) : '';

            updateRadioLog($this->config, $this->radioLogID, $this->radioCallNum, $this->radioID, $this->podID, $this->checkOutType);
            $this->isEditing = true;
        }

        if ($this->isEditing) {
            if ($this->config->adminLvl >= 0) {
                $myq = "SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.REFNUM = '" . $radioLogID . "' AND IS_RESERVE=0
                    UNION
                    SELECT R.REFNUM, R.GPNUM 'gpID', CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.REFNUM = '" . $radioLogID . "' AND IS_RESERVE=1
                    ";
                $result = $this->config->mysqli->query($myq);
                SQLerrorCatch($this->config->mysqli, $result);
                $row = $result->fetch_assoc();
                if ($row['gpID'] != 0) {
                    //get all users
                    echo '<div align="center">Group Reference #: ' . $row['gpID'] . '
                    <input type="hidden" name="gpID" value="' . $row['gpID'] . '" /></div>';

                    $newq = "SELECT R.REFNUM 'refNum', R.GPNUM 'gpID', 
                        CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN EMPLOYEE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.GPNUM = '" . $row['gpID'] . "' AND IS_RESERVE=0
                    UNION
                    SELECT R.REFNUM 'refNum', R.GPNUM 'gpID', 
                        CONCAT_WS(', ', LNAME, FNAME) 'DEPUTYNAME', R.RADIO_CALLNUM, 
                        R.RADIOID, R.TYPE, DATE_FORMAT (AUDIT_IN_TS, '%m/%d/%y %H%i') 'inTime'
                    FROM WTS_RADIOLOG R
                    JOIN RESERVE AS SEC ON SEC.IDNUM=R.DEPUTYID
                    WHERE R.GPNUM = '" . $row['gpID'] . "' AND IS_RESERVE=1
                    ORDER BY R.REFNUM";
                    $newResult = $this->config->mysqli->query($newq);
                    SQLerrorCatch($this->config->mysqli, $newResult);

                    $x = 0;
                    $y = 0;
                    $depTable = array(array());
                    $depTable[$x][$y] = "Reference#";
                    $y++;
                    $depTable[$x][$y] = "Deputy";
                    $y++;
                    $depTable[$x][$y] = "Radio#";
                    $y++;
                    $depTable[$x][$y] = "Action";
                    $y++;

                    $x++;
                    while ($newRow = $newResult->fetch_assoc()) {
                        $y = 0;
                        $depTable[$x][$y] = $newRow['refNum'] . '
                        <input type="hidden" name="radioLogID' . $x . '" value="' . $newRow['refNum'] . '" />';
                        $y++;
                        $depTable[$x][$y] = $newRow['DEPUTYNAME'];
                        $y++;
                        $depTable[$x][$y] = '<input type="text" name="radioCallNum' . $x . '" value="' . $newRow['RADIO_CALLNUM'] . '" />';
                        $y++;
                        if (strcmp($newRow['inTime'], "00/00/000 0000") == 0) {
                            $depTable[$x][$y] = '<input type="submit" value="Update" name="updateRadioLog' . $x . '" />
                                <input type="submit" value="LogOut" name="logoutRadioLog' . $x . '" /><br/>';
                            $y++;
                        } else {
                            if ($config->adminLvl >= 25) {
                                $depTable[$x][$y] = '<input type="submit" value="Update" name="updateRadioLog' . $x . '" />
                                Checked in at ' . $newRow['inTime'];
                                $y++;
                            } else {
                                $depTable[$x][$y] = 'Checked in at ' . $newRow['inTime'];
                                $y++;
                            }
                        }
                        $x++;
                    }
                    showSortableTable($depTable, 1);
                } else {
                    echo '<br/>Reference #: ' . $radioLogID . '<input type="hidden" name="radioLogID" value="' . $radioLogID . '" /><br />
                    Deputy: ' . $row['DEPUTYNAME'] . '<br/>
                    Radio#: <input type="text" name="radioCallNum" value="' . $row['RADIO_CALLNUM'] . '" /><br/>';
                }
                echo '<div align="left">Add Deputy: <button type="button"  name="searchBtn" 
                value="Lookup Employee" onClick="this.form.action=' . "'?userLookup=true'" . ';this.form.submit()" >
                Lookup Employee</button></div><br/>';
                echo '<br/> Radio Number: ';
                selectRadioInventory($this->config, "radioID", $row['RADIOID']);
                echo '<br/><br/>';
                if ($row['TYPE'] == "LOANER")
                    echo '<input type="radio" name="checkOutType" value="LOANER" CHECKED>LOANER</input>';
                else
                    echo '<input type="radio" name="checkOutType" value="LOANER">LOANER</input>';
                if ($row['TYPE'] == "PERM")
                    echo '<input type="radio" name="checkOutType" value="PERM" CHECKED>PERMANENT</input>';
                else
                    echo '<input type="radio" name="checkOutType" value="PERM">PERMANENT</input>';
                if ($row['TYPE'] == "POD")
                    echo '<input type="radio" name="checkOutType" value="POD" CHECKED>SHIFT ASSIGNMENT</input><br/>';
                else
                    echo '<input type="radio" name="checkOutType" value="POD">SHIFT ASSIGNMENT</input><br/>';
                echo '<br/>Checked in time: ';
                if (strcmp($row['inTime'], "00/00/00 0000") == 0) {
                    echo "<font color=red><b>Not Checked back in Yet</b></font><br /><br />";
                    if ($row['gpID'] != 0) {
                        echo '<input type="submit" name="checkInAllRadio" value="Check in All" />';
                    } else {
                        echo '<input type="submit" name="checkInRadio" value="Check Back In" />';
                    }
                } else {
                    echo $row['inTime'] . '<br /><br />';
                }
                if (strcmp($row['inTime'], "00/00/0000 0000") == 0 || $config->adminLvl >= 25) {
                    if ($row['gpID'] != 0)
                        echo '<input type="submit" name="updateRadioLogAll" value="Update All" />';
                    else
                        echo '<input type="submit" name="updateRadioLog" value="Update" />';
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
            echo '<br/><br/>';
            $radioLogID = isset($_POST['secLogID']) ? $this->config->real_escape_string($_POST['secLogID']) : '';
            $radioID = isset($_POST['radioID']) ? $this->config->real_escape_string(strtoupper($_POST['radioID'])) : '';
            $podID = isset($_POST['podID']) ? $this->config->real_escape_string(strtoupper($_POST['podID'])) : '';
            $checkOutType = isset($_POST['checkOutType']) ? $this->config->real_escape_string(strtoupper($_POST['checkOutType'])) : '';

            //debug
            //var_dump($_POST);
            //Show previously added deputies
            $deputyCount = 0;
            if ($num_deputies > 0) {
                for ($i = 0; $i < $num_deputies; $i++) {
                    if (!isset($_POST['removeDeputyBtn' . $i])) {
                        $deputyID[$i] = isset($_POST['deputyID' . $i]) ? $this->config->real_escape_string(strtoupper($_POST['deputyID' . $i])) : '';
                        $isReserve[$i] = isset($_POST['isReserve' . $i]) ? true : false;

                        //get this user's information
                        if ($isReserve[$i]) {
                            $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM RESERVE WHERE IDNUM=' . $deputyID[$i];
                            $result = $this->mysqliReserve->query($myq);
                            SQLerrorCatch($this->mysqliReserve, $result);
                            $row = $result->fetch_assoc();
                        } else {
                            $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM EMPLOYEE WHERE IDNUM=' . $deputyID[$i];
                            $result = $this->config->query($myq);
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
                    $result = $this->mysqliReserve->query($myq);
                    SQLerrorCatch($this->mysqliReserve, $result);
                } else {
                    $myq = 'SELECT RADIO, CELLPH, LNAME, FNAME FROM EMPLOYEE WHERE IDNUM=' . $foundUserID;
                    $result = $this->config->query($myq);
                    SQLerrorCatch($mysqli, $result);
                }

                $row = $result->fetch_assoc();
                if ($deputyCount == 0)
                    $phone = $row['CELLPH'];
                echo 'Deputy: <input type="hidden" name="deputyID' . $deputyCount . '" value="' . $foundUserID . '" />';
                if ($foundUserIsReserve)
                    echo '<input type="hidden" name="isReserve' . $deputyCount . '" value="true" />';
                echo $row['LNAME'] . ', ' . $row['FNAME'];
                echo ';  Radio Call#: <input type="hidden" name="radioCallNum' . $deputyCount . '" value="' . $row['RADIO'] . '" />' . $row['RADIO'];
                echo '<input type="submit" name="removeDeputyBtn' . $deputyCount . '" value="Remove" />';
                echo '<br/>';
                $deputyCount++;
            }
            echo 'Add Deputy: ';
            displayUserLookup($config);
            echo '<input type="hidden" name="num_deputies" value="' . $deputyCount . '" />';

            $gpID = isset($_POST['gpID']) ? $_POST['gpID'] : 0;
            echo '<br/><br/><input type="hidden" name="gpID" value="' . $gpID . '" /> Radio Number: ';
            selectRadioInventory($config, "radioID", $radioID);
            echo '<br/><br/>';
            if ($checkOutType == "LOANER")
                echo '<input type="radio" name="checkOutType" value="LOANER" CHECKED>LOANER</input>';
            else
                echo '<input type="radio" name="checkOutType" value="LOANER">LOANER</input>';
            if ($checkOutType == "PERM")
                echo '<input type="radio" name="checkOutType" value="PERM" CHECKED>PERMANENT</input>';
            else
                echo '<input type="radio" name="checkOutType" value="PERM">PERMANENT</input>';
            if ($checkOutType == "POD")
                echo '<input type="radio" name="checkOutType" value="POD" CHECKED>SHIFT ASSIGNMENT</input><br/>';
            else
                echo '<input type="radio" name="checkOutType" value="POD">SHIFT ASSIGNMENT</input><br/>';
            echo '<br/><input type="hidden" name="addBtn" value="true" />
            <input type="submit" name="addRadioLog" value="Check Out Radio" />
            <input type="submit" name="goBtn" value="Cancel" />';
        }
    }

}

?>
