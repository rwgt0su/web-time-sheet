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
        $addBtn = isset($_POST['addBtn']) ? True : false;
        $editBtn = isset($_POST['editBtn']) ? True : false;
        $secLogID = isset($_POST['secLogID']) ? $_POST['secLogID'] : false;
        $rowNum = isset($_POST['rowNum ']) ? $_POST['rowNum '] : false;
        
        if(!$dateSelect){
            echo 'Select Date: ';
            displayDateSelect("dateSelect", "dateSel",false,false,true);
            echo '<input type=submit name="goBtn" value="Go" />'; 
        }
        else{
            echo '<h3>Date: '.$dateSelect.'</h3>';
            echo '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />';
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
            showSecLogDetails($config, $secLogID, false, $addBtn);
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
function showSecLog($config, $dateSelect, $isSup){
    $mysqli = $config->mysqli;
    $myq = "SELECT *
        FROM `SECLOG`
        WHERE `SHIFTDATE` = '".Date('Y-m-d', strtotime($dateSelect))."'";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $echo = '<table>';
    if($isSup){
        $i=0;
        $echo = '<table><tr><td>Deputy</td><td>Radio#</td><td>Log In</td><td>C/Deputy</td><td>Site Name/Address</td>
            <td>City/Twp</td><td>Shift Start</td><td>Shift End</td><td>Dress</td><td>Log Off</td><td>C/Deputy</td>
            <td>Supervisor</td><td>Sign Off</td></tr>';

        while($row = $result->fetch_assoc()) {
            $echo .= '<tr><td>'.$row['DEPUTYID'].'</td>
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
       $echo = '<tr><td>Deputy</td><td>Radio#</td><td>Log In</td><td>C/Deputy</td><td>Site Name/Address</td>
            <td>City/Twp</td><td>Contact#</td><td>Shift Start</td><td>Shift End</td></tr>';
       $i=0;
       
        while($row = $result->fetch_assoc()) {
            $echo .= '<tr><td>'.$row['DEPUTYID'].'</td>
                <td>'.$row['RADIO'].'</td>
                <td>'.$row['TIMEIN'].'</td>
                <td>'.$row['AUDIT_IN_ID'].'</td>
                <td>'.$row['LOCATION'].'</td>
                <td>'.$row['CITY'].'</td>
                <td>'.$row['PHONE'].'</td>
                <td>'.$row['SHIFTSTART'].'</td>
                <td>'.$row['SHIFTEND'].'</td>
                <input type="hidden" name="secLogID'.$i.'" value="'.$row['IDNUM'].' />
                </tr>';
            $i++;
        } 
    }
    $echo .= '<input type="hidden" name="rowNum" value="'.$i.'" /></table>';
    $echo .= '<input type="hidden" name="dateSelect" value="'.$dateSelect.'" />';
    $echo .= '<input type="submit" name="addBtn" value="New Log In" />';
    echo $echo;
    
}
function showSecLogDetails($config, $secLogID, $isSup, $isEditing=false){
    $mysqli = $config->mysqli;
    $myq = "SELECT *
        FROM `SECLOG`
        WHERE `IDNUM` = '".$secLogID."'";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $row = $result->fetch_assoc();
    $echo = "";
    $echo .= 'Deputy: <br/>';
    $echo .= 'Radio#: <br/>';
    $echo .= 'Login Time: <br/>';
    $echo .= 'Site Name or Address: <br/>';
    $echo .= 'City/Twp: <br/>';
    $echo .= 'Contact#: <br/>';
    $echo .= 'Shift Start Time: <br/>';
    $echo .= 'Shift End Time: <br/>';
    $echo .= 'Dress: <select name="dress">
        <option value=""></option>
        <option value="U">Uniform</option>
        <option value="PC">Plain Clothes</option>
        </select><br/>
        <input type="submit" name="addSecLog" value="Add" />
        <input type="submit" name="goBtn" value="Cancel" />';
    
        if($isSup){

        }
        else{

        }
    echo $echo;
}
?>