<?php

function displayReserves($config){
    echo '<h3>Reserves Manager</h3>';
    if($config->adminLvl >= 75){
        //get passed variables
        $addBtn = isset($_POST['addBtn']) ? true : false;
        $editSelect = isset($_POST['totalRows']) ? $_POST['totalRows'] : false;
        $reserveID = isset($_POST['reserveID']) ? $_POST['reserveID'] : false;
        $goBackBtn = isset($_POST['goBackBtn']) ?  true : false;
        $delBtn = isset($_POST['delBtn']) ? true : false;
        $delBtn = isset($_POST['noBtn']) ? false : $delBtn;
        
        if($goBackBtn){
            $addBtn = false;
            $reserveID = false;
        }

        if(isset($_POST['totalRows']) && !$reserveID){
            for ($i=0; $i <= $editSelect; $i++){
                if(isset($_POST['foundUser'.$i])){
                    $reserveID = $_POST['foundUserID'.$i];
                    break;
                }
            }
        }
        if($delBtn){
            $confirmBtn = isset($_POST['confirmBtn']) ? true : false;
            $mysqli = connectToSQL($reserveDB = TRUE);
            
            if(!$confirmBtn){
                //Confirm Delete Record
                popUpMessage('Are you Sure? <br/>
                    <form method="POST" name="confirmForm">
                    <input type="submit" name="confirmBtn" value="Yes" />
                    <input type="submit" name="noBtn" value="Cancel" />
                    <input type="hidden" name="delBtn" value="true" />
                    <input type="hidden" name="reserveID" value="'.$reserveID.'" />
                    </form>');
            }
            else{
                $myq = "DELETE FROM `RESERVE`
                    WHERE `IDNUM` = ".$reserveID." LIMIT 1";
                
                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result);
                $reserveID = false;
                echo 'Reserve Successfully Removed.<br/>';
            }
        }
        //Main Content
        echo '<form name="resManage" method="POST" action="'.$_SERVER['REQUEST_URI'].'" >';
        echo '<input type="hidden" name="formName" value="resManage" />';

        if(!$addBtn && !$reserveID){
            reservesTable($config);
            echo '<input type="submit" name="addBtn" value="Add Reserve" />';
        }
        if($addBtn){
            //get return to location
            $prevNum = isset($_POST['prevNum']) ? $_POST['prevNum'] : "0";
            $nextNum = isset($_POST['nextNum']) ? $_POST['nextNum'] : "25";
            $limit= isset($_POST['limit']) ? $_POST['limit'] : "25";
            echo '<input type="hidden" name="prevNum" value="'.$prevNum.'" />';
            echo '<input type="hidden" name="nextNum" value="'.$nextNum.'" />'; 
            echo '<input type="hidden" name="limit" value="'.$limit.'" />'; 
            
            showAddReserve($config);
        }
        if(!empty($reserveID)){
            //get return to location
            $prevNum = isset($_POST['prevNum']) ? $_POST['prevNum'] : "0";
            $nextNum = isset($_POST['nextNum']) ? $_POST['nextNum'] : "25";
            $limit= isset($_POST['limit']) ? $_POST['limit'] : "25";
            echo '<input type="hidden" name="prevNum" value="'.$prevNum.'" />';
            echo '<input type="hidden" name="nextNum" value="'.$nextNum.'" />'; 
            echo '<input type="hidden" name="limit" value="'.$limit.'" />'; 
            
            reserveDetails($config, $reserveID);
        }

        //End Content
        echo '</form>';
    }
    else{
        echo '<h3>Access Denied!</h3>';
    }
    
}
function reservesTable($config){
    $prevNum = isset($_POST['prevNum']) ? $_POST['prevNum'] : "0";
    $nextNum = isset($_POST['nextNum']) ? $_POST['nextNum'] : "25";
    $limit= isset($_POST['limit']) ? $_POST['limit'] : "25";
    
    if(isset($_POST['prevBtn'])){
        $prevNum = $prevNum - $limit;
        $nextNum = $nextNum - $limit;
    }
    if(isset($_POST['nextBtn'])){
        $prevNum = $prevNum + $limit;
        $nextNum = $nextNum + $limit;
    }
    
    $mysqli = connectToSQL($reserveDB = TRUE);
    if($config->adminLvl >= 75)
        $myq = "SELECT *  FROM `RESERVE`";
    else
        $myq = "SELECT *  FROM `RESERVE` WHERE `GRP` != 5";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $totalRows = $result->num_rows;
    
    if($config->adminLvl >= 75)
        $myq = "SELECT *  FROM `RESERVE` ORDER BY `RESERVE`.`RADIO` ASC LIMIT ".$prevNum.",  ".$limit;
    else
        $myq = "SELECT *  FROM `RESERVE` WHERE `GRP` != 5 ORDER BY `RESERVE`.`RADIO` ASC LIMIT ".$prevNum.",  ".$limit;
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $rowCount = 0;
    $echo = "";
    $rowCount = 0;
    $theTable = array(array());
    $theTable[$rowCount][0] = "Edit";
    $theTable[$rowCount][1] = "First Name";
    $theTable[$rowCount][2] = "Last Name";
    $theTable[$rowCount][3] = "Username";
    $theTable[$rowCount][4] = "Radio";
    $theTable[$rowCount][5] = "Group";
    
    while($row = $result->fetch_assoc()) {
        $rowCount++;
        $theTable[$rowCount][0] = $rowCount.'<input name="foundUser'.$rowCount.'" type="submit" value="Edit/View" />';
        $theTable[$rowCount][1] = '<input type="hidden" name="foundUserFNAME'.$rowCount.'" value="'.$row['FNAME'].'" /> ' . $row['FNAME'];
        $theTable[$rowCount][2] = '<input type="hidden" name="foundUserLNAME'.$rowCount.'" value="'.$row['LNAME'].'" />' . $row['LNAME'];
        $theTable[$rowCount][3] =  '<input type="hidden" name="foundUserID'.$rowCount.'" value="'.$row['IDNUM'].'" />' . $row['FNAME'].".".$row['LNAME'].
                '<input type="hidden" name="foundUserName'.$rowCount.'" value="'.$row['FNAME'].".".$row['LNAME'].'" />';
        $theTable[$rowCount][4] = $row['RADIO'];
        $theTable[$rowCount][5] = $row['GRP'];
    }//end While Loop
    
    
    
    echo "Number of entries found in the reserve database is: " . $totalRows;
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<input type="hidden" name="searchFullTime" value="false" />';
    echo '<input type="hidden" name="searchReserves" value="checked" />';
    displayUserLookup($config);
    echo '<br /><br /><hr />';
    echo '<input type="hidden" name="prevNum" value="'.$prevNum.'" />';
    echo '<input type="hidden" name="nextNum" value="'.$nextNum.'" />';
    $lastRec = $prevNum + $limit;
    echo 'Showing Records '. $prevNum . ' to ' .$lastRec;
    //Spacing characters
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    if(!$prevNum > 0){
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    echo 'Records: <select name="limit" onChange="this.form.submit()" >
        <option value="25"';
    if(strcmp($limit, "25") ==0)
        echo ' SELECTED';
    echo '>25</option>
        <option value="50"';
    if(strcmp($limit, "50") ==0)
        echo ' SELECTED';
    echo '>50</option>
        </select>';
    if($prevNum > 0)
        echo '<input type="submit" name="prevBtn" value="Previous" />';
    if($limit == $rowCount)
        echo '<input type="submit" name="nextBtn" value="Next" />';
    //echo $echo;
    showSortableTable($theTable, 4);
}
function showAddReserve($config){
    if($config->adminLvl >=75){
        $mysqli = connectToSQL($reserveDB = TRUE);
        $saveBtn = isset($_POST['saveBtn']) ? true : false;
        if($saveBtn){
            $group = isset($_POST['resGroup']) ?  $mysqli->real_escape_string($_POST['resGroup']) : "";
            $fName = isset($_POST['foundUserFNAME']) ?  $mysqli->real_escape_string($_POST['foundUserFNAME']) : "";
            $lName = isset($_POST['foundUserLNAME']) ?  $mysqli->real_escape_string($_POST['foundUserLNAME']) : "";
            $radio = isset($_POST['radioNum']) ?  $mysqli->real_escape_string($_POST['radioNum']) : "";
            $address = isset($_POST['address']) ?  $mysqli->real_escape_string($_POST['address']) : "";
            $city = isset($_POST['city']) ?  $mysqli->real_escape_string($_POST['city']) : "";
            $state = isset($_POST['state']) ?  $mysqli->real_escape_string($_POST['state']) : "";
            $zip = isset($_POST['zip']) ?  $mysqli->real_escape_string($_POST['zip']) : "";
            $hPhone = isset($_POST['hPhone']) ?  $mysqli->real_escape_string($_POST['hPhone']) : "";
            $cPhone = isset($_POST['cPhone']) ?  $mysqli->real_escape_string($_POST['cPhone']) : "";
            $wPhone = isset($_POST['wPhone']) ?  $mysqli->real_escape_string($_POST['wPhone']) : "";
            $tis = isset($_POST['tis']) ?  $mysqli->real_escape_string($_POST['tis']) : "";
            $agency = isset($_POST['agency']) ?  $mysqli->real_escape_string($_POST['agency']) : "";
            $notes = isset($_POST['notes']) ?  $mysqli->real_escape_string($_POST['notes']) : "";
            
            if(empty($fName) || empty($lName) || empty($group)){
                echo '<br />Must provide all the highlighted items<br /> Did not Save<br />';
                $saveBtn = false;
            }
            else{
                $myq = "INSERT INTO `RESERVE`.`RESERVE` (`GRP` ,`LNAME` ,`FNAME` ,`RADIO` ,`ADDRESS` ,`CITY` ,`ST` ,`ZIP` ,`HOMEPH` ,`CELLPH` ,`WORKPH` ,`TIS` ,`AGENCY` ,`NOTES` ,`IDNUM`)
                    VALUES (
                    ".$group.",
                    '".$lName."',
                    '".$fName."',
                    '".$radio."',
                    '".$address."',
                    '".$city."',
                    '".$state."',
                    '".$zip."',
                    '".$hPhone."',
                    '".$cPhone."',
                    '".$wPhone."',
                    '".$tis."',
                    '".$agency."',
                    '".$notes."',
                    NULL
                    );";
                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result);
                echo 'Successfully Saved Reserve with ID: '.$mysqli->insert_id. '<br />';
                reserveDetails($config, $mysqli->insert_id);
            }
        }
        if(!$saveBtn){
            echo '</div><div align="left" class="login"><table>';
            echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
                First Name: </td><td><input type="text" name="foundUserFNAME" ';
            echo showInputBoxError();
            echo ' /></td></tr>';
            echo '<tr><td></td><td>Last Name: </td><td><input type="text" name="foundUserLNAME" ';
            echo showInputBoxError();
            echo '/></td></tr>';
            echo '<tr><td></td><td>Group: </td><td><select name="resGroup" ';
            echo showInputBoxError();
            echo '>
                <option value="">Select Group</option>
                <option value="1">Group 1</option>
                <option value="2">Group 2</option>
                <option value="3">Group 3</option>
                <option value="4">Group 4</option>
                <option value="5">Group 5</option>
                </select></td></tr>';
            echo '<tr><td></td><td>Radio#: </td><td><input type="text" name="radioNum" /></td></tr>';
            echo '<tr><td></td><td>Address: </td><td><input type="text" name="address" /></td></tr>';
            echo '<tr><td></td><td>City: </td><td><input type="text" name="city" /></td></tr>';
            echo '<tr><td></td><td>State: </td><td><input type="text" name="state" /></td></tr>';
            echo '<tr><td></td><td>ZIP: </td><td><input type="text" name="zip" /></td></tr>';
            echo '<tr><td></td><td>Home Phone: </td><td><input type="text" name="hPhone" /></td></tr>';
            echo '<tr><td></td><td>Cell Phone: </td><td><input type="text" name="cPhone" /></td></tr>';
            echo '<tr><td></td><td>Work Phone: </td><td><input type="text" name="wPhone" /></td></tr>';
            echo '<tr><td></td><td>Time in Service: </td><td>';
            displayDateSelect("tis", "tis", false, false, true);
            echo '</td></tr>';
            echo '<tr><td></td><td>Agency: </td><td><input type="text" name="agency" /></td></tr>';
            echo '<tr><td></td><td>Additional Notes: </td><td><input type="text" name="notes" /></td></tr><tr><td></td></tr>';
            echo '<tr><td></td><td><input type="submit" name="saveBtn" value="Save" /></td><td>';
            echo '<input type="hidden" name="addBtn" value="true" />';
            echo '<input type="submit" name="goBackBtn" value="Back To Reserves" /></td></tr>';
            echo '</table></div>';
        }
    }
}
function reserveDetails($config, $reserveID){
    $mysqli = connectToSQL($reserveDB = TRUE);
    echo 'Details for: ' . $reserveID . '<input type="hidden" name="reserveID" value="'.$reserveID.'" />';
    if($config->adminLvl >= 75){
        $updateBtn = isset($_POST['updateBtn']) ? true : false;
        
        if($updateBtn){
            $group = isset($_POST['resGroup']) ?  $mysqli->real_escape_string($_POST['resGroup']) : "";
            $fName = isset($_POST['foundUserFNAME']) ?  $mysqli->real_escape_string($_POST['foundUserFNAME']) : "";
            $lName = isset($_POST['foundUserLNAME']) ?  $mysqli->real_escape_string($_POST['foundUserLNAME']) : "";
            $radio = isset($_POST['radioNum']) ?  $mysqli->real_escape_string($_POST['radioNum']) : "";
            $address = isset($_POST['address']) ?  $mysqli->real_escape_string($_POST['address']) : "";
            $city = isset($_POST['city']) ?  $mysqli->real_escape_string($_POST['city']) : "";
            $state = isset($_POST['state']) ?  $mysqli->real_escape_string($_POST['state']) : "";
            $zip = isset($_POST['zip']) ?  $mysqli->real_escape_string($_POST['zip']) : "";
            $hPhone = isset($_POST['hPhone']) ?  $mysqli->real_escape_string($_POST['hPhone']) : "";
            $cPhone = isset($_POST['cPhone']) ?  $mysqli->real_escape_string($_POST['cPhone']) : "";
            $wPhone = isset($_POST['wPhone']) ?  $mysqli->real_escape_string($_POST['wPhone']) : "";
            $tis = isset($_POST['tis']) ?  $mysqli->real_escape_string($_POST['tis']) : "";
            $agency = isset($_POST['agency']) ?  $mysqli->real_escape_string($_POST['agency']) : "";
            $notes = isset($_POST['notes']) ?  $mysqli->real_escape_string($_POST['notes']) : "";
            
            if(empty($fName) || empty($lName) || empty($group)){
                echo '<br />Must provide all the highlighted items<br /> Did not Save<br />';
            }
            else{
                //Update Fields
                $myq = "UPDATE `RESERVE`.`RESERVE` SET
                    `GRP` = ".$group.",
                    `LNAME` = '".$lName."',
                    `FNAME` = '".$fName."',
                    `RADIO` = '".$radio."',
                    `ADDRESS` = '".$address."',
                    `CITY` = '".$city."',
                    `ST` = '".$state."',
                    `ZIP` = '".$zip."',
                    `HOMEPH` = '".$hPhone."',
                    `CELLPH` = '".$cPhone."',
                    `WORKPH` = '".$wPhone."',
                    `TIS` = '".$tis."',
                    `AGENCY` = '".$agency."',
                    `NOTES` = '".$notes."' 
                    WHERE `IDNUM` = ".$reserveID;
                
                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result);
                echo 'Reserve Successfully Updated.<br/>';
            }
        }
        else{
            
            $myq = "SELECT * FROM `RESERVE` WHERE `IDNUM` = ".$reserveID;
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            $row = $result->fetch_assoc();
            $group = $row['GRP'];
            $fName = $row['FNAME'];
            $lName = $row['LNAME'];
            $radio = $row['RADIO'];
            $address = $row['ADDRESS'];
            $city = $row['CITY'];
            $state = $row['ST'];
            $zip = $row['ZIP'];
            $hPhone = $row['HOMEPH'];
            $cPhone = $row['CELLPH'];
            $wPhone = $row['WORKPH'];
            $tis = $row['TIS'];
            $agency = $row['AGENCY'];
            $notes = $row['NOTES'];
        }
        echo '</div><div align="left" class="login"><table>';
        echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>First Name: </td><td><input type="text" name="foundUserFNAME" value="'.$fName.'" /></td></tr>';
        echo '<tr><td></td><td>Last Name: </td><td><input type="text" name="foundUserLNAME" value="'.$lName.'" /></td></tr>';
        echo '<tr><td></td><td>Group: </td><td><select name="resGroup">
            <option value="">Select Group</option>            
            <option value="1"'; if($group == "1") echo " SELECTED"; echo'>Group 1</option>
            <option value="2"'; if($group == "2") echo " SELECTED"; echo'>Group 2</option>
            <option value="3"'; if($group == "3") echo " SELECTED"; echo'>Group 3</option>
            <option value="4"'; if($group == "4") echo " SELECTED"; echo'>Group 4</option>
            <option value="5"'; if($group == "5") echo " SELECTED"; echo'>Group 5</option>
            </select></td></tr>';
        echo '<tr><td></td><td>Radio#: </td><td><input type="text" name="radioNum" value="'.$radio.'" /></td></tr>';
        echo '<tr><td></td><td>Address: </td><td><input type="text" name="address" value="'.$address.'" /></td></tr>';
        echo '<tr><td></td><td>City: </td><td><input type="text" name="city" value="'.$city.'" /></td></tr>';
        echo '<tr><td></td><td>State: </td><td><input type="text" name="state" value="'.$state.'" /></td></tr>';
        echo '<tr><td></td><td>ZIP: </td><td><input type="text" name="zip" value="'.$zip.'" /></td></tr>';
        echo '<tr><td></td><td>Home Phone: </td><td><input type="text" name="hPhone" value="'.$hPhone.'" /></td></tr>';
        echo '<tr><td></td><td>Cell Phone: </td><td><input type="text" name="cPhone" value="'.$cPhone.'" /></td></tr>';
        echo '<tr><td></td><td>Work Phone: </td><td><input type="text" name="wPhone" value="'.$wPhone.'" /></td></tr>';
        echo '<tr><td></td><td>Time in Service: </td><td>';
        displayDateSelect("tis", "tis", $tis, false, false);
        echo '</td></tr>';
        echo '<tr><td></td><td>Agency: </td><td><input type="text" name="agency" value="'.$agency.'" /></td></tr>';
        echo '<tr><td></td><td>Additional Notes: </td><td><input type="text" name="notes" value="'.$notes.'" /></td></tr><tr><td></td></tr>';
        echo '<tr><td></td><td><input type="submit" name="updateBtn" value="Update and Save" /></td><td>';
        echo '<input type="submit" name="delBtn" value="Delete Reserve" /> <input type="submit" name="goBackBtn" value="Back To Reserves" /></td></tr>';
        echo '</table></div>';
    }
}
?>
