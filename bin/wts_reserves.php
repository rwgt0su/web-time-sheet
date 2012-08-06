<?php

function displayReserves($config){
    if($config->adminLvl > 75){
        //get passed variables
        $addBtn = isset($_POST['addBtn']) ? true : false;
        $editSelect = isset($_POST['totalRows']) ? $_POST['totalRows'] : false;
        $reserveID = false;

        if(isset($_POST['totalRows'])){
            for ($i=0; $i <= $editSelect; $i++){
                if(isset($_POST['resRadioBtn'.$i])){
                    $reserveID = $_POST['foundUserID'.$i];
                    break;
                }
            }
        }

        //Main Content
        echo '<form name="resMan" method="POST" action="'.$_SERVER['REQUEST_URI'].'" >';

        if(!$addBtn && !$reserveID){
            reservesTable($config);
            echo '<input type="submit" name="addBtn" value="Add Reserve" />';
        }
        if($addBtn){
            showAddReserve($config);
            echo '<input type="submit" name="goBackBtn" value="Back To Reserves" />';
        }
        if(strcmp($reserveID, "false") != 0){
            reserveDetails($config, $reserveID);
            echo '<input type="submit" name="goBackBtn" value="Back To Reserves" />';
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
    //popUpMessage('limit: '.$limit.' prevnum: '.$prevNum.' nextnum: '.$nextNum);
    
    $mysqli = connectToSQL($reserveDB = TRUE);
    $myq = "SELECT *  FROM `RESERVE` WHERE `LNAME` LIKE CONVERT(_utf8 '%' USING latin1) COLLATE latin1_swedish_ci";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $totalRows = $result->num_rows;
    
    if($config->adminLvl > 75)
        $myq = "SELECT *  FROM `RESERVE` WHERE `LNAME` LIKE CONVERT(_utf8 '%' USING latin1) COLLATE latin1_swedish_ci LIMIT ".$nextNum.",  ".$limit;
    else
        $myq = "SELECT *  FROM `RESERVE` WHERE `GRP` != 5 AND `LNAME` LIKE CONVERT(_utf8 '%' USING latin1) COLLATE latin1_swedish_ci LIMIT 0, 50";
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
    $theTable[$rowCount][4] = "Group";
    
    while($row = $result->fetch_assoc()) {
        $rowCount++;
        $theTable[$rowCount][0] = $rowCount.'<input name="resRadioBtn'.$rowCount.'" type="submit" value="Edit/View" />';
        $theTable[$rowCount][1] = '<input type="hidden" name="foundUserFNAME'.$rowCount.'" value="'.$row['FNAME'].'" /> ' . $row['FNAME'];
        $theTable[$rowCount][2] = '<input type="hidden" name="foundUserLNAME'.$rowCount.'" value="'.$row['LNAME'].'" />' . $row['LNAME'];
        $theTable[$rowCount][3] =  '<input type="hidden" name="foundUserID'.$rowCount.'" value="'.$row['IDNUM'].'" />' . $row['FNAME'].".".$row['LNAME'].
                '<input type="hidden" name="foundUserName'.$rowCount.'" value="'.$row['FNAME'].".".$row['LNAME'].'" />';
        $theTable[$rowCount][4] = $row['GRP'];
//        $echo .= '<div align="center"><table width="400"><tr><td>';
//        $echo .= $rowCount.'<input name="foundUser'.$rowCount.'" type="radio" onclick="this.form.submit();" />Select</td><td>';
//        $echo .= '<input type="hidden" name="foundUserFNAME'.$rowCount.'" value="'.$row['FNAME'].'" /> First name: ' . $row['FNAME'] . "<br />";
//        $echo .= '<input type="hidden" name="foundUserLNAME'.$rowCount.'" value="'.$row['LNAME'].'" /> Last Name: ' . $row['LNAME'] . "<br />";
//        $echo .= '<input type="hidden" name="foundUserID'.$rowCount.'" value="'.$row['IDNUM'].'" /> Username: ' . $row['FNAME'].".".$row['LNAME'] . '<br />';
//        $echo .= '<input type="hidden" name="foundUserName'.$rowCount.'" value="'.$row['FNAME'].".".$row['LNAME'].'" />';
//        $echo .= "Reserve Group " . $row['GRP'] . "<br />";
//        $echo .= "</td></tr></table></div><br /><hr />";
    }//end While Loop
    
    
    
    echo "Number of entries found in the reserve database is: " . $totalRows. '<br /><br /><hr />';
    echo '<input type="hidden" name="prevNum" value="'.$prevNum.'" />';
    echo '<input type="hidden" name="nextNum" value="'.$nextNum.'" />';
    echo 'Showing Records '. $prevNum . ' to ' . $nextNum;
    //Spacing characters
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    if(!$prevNum > 0){
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    echo 'Records: <select name="limit">
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
    if($limit = $rowCount)
        echo '<input type="submit" name="nextBtn" value="Next" />';
    //echo $echo;
    showSortableTable($theTable, 0);
}
function showAddReserve($config){
    echo 'Add button Pressed';
}
function reserveDetails($config, $reserveID){
    echo 'Details for: ' . $reserveID;
}
?>
