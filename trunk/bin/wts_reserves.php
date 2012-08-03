<?php

function displayReserves($config){
    $prevNum = isset($_POST['prevNum']) ? $_POST['prevNum'] : "0";
    $nextNum = isset($_POST['nextNum']) ? $_POST['nextNum'] : "25";
    $limit = 25;
    
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
    if($config->adminLvl > 75)
        $myq = "SELECT *  FROM `RESERVE` WHERE `LNAME` LIKE CONVERT(_utf8 '%' USING latin1) COLLATE latin1_swedish_ci LIMIT ".$nextNum.",  ".$limit;
    else
        $myq = "SELECT *  FROM `RESERVE` WHERE `GRP` != 5 AND `LNAME` LIKE CONVERT(_utf8 '%' USING latin1) COLLATE latin1_swedish_ci LIMIT 0, 50";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $rowCount = 0;
    $echo = "";
    
    while($row = $result->fetch_assoc()) {
        $rowCount++;
        $echo .= '<div align="center"><table width="400"><tr><td>';
        $echo .= '<input name="foundUser'.$rowCount.'" type="radio" onclick="this.form.submit();" />Select</td><td>';
        $echo .= '<input type="hidden" name="foundUserFNAME'.$rowCount.'" value="'.$row['FNAME'].'" /> First name: ' . $row['FNAME'] . "<br />";
        $echo .= '<input type="hidden" name="foundUserLNAME'.$rowCount.'" value="'.$row['LNAME'].'" /> Last Name: ' . $row['LNAME'] . "<br />";
        $echo .= '<input type="hidden" name="foundUserID'.$rowCount.'" value="'.$row['IDNUM'].'" /> Username: ' . $row['FNAME'].".".$row['LNAME'] . '<br />';
        $echo .= '<input type="hidden" name="foundUserName'.$rowCount.'" value="'.$row['FNAME'].".".$row['LNAME'].'" />';
        $echo .= "Rank: Reserve Group " . $row['GRP'] . "<br />";
        $echo .= "</td></tr></table></div><br /><hr />";
    }//end While Loop
    
    echo "Number of entries found in the reserve database is: " . $rowCount. '<br /><br /><hr />
        <form name="resMan" method="POST" action="'.$_SERVER['REQUEST_URI'].'" >';
    echo '<input type="hidden" name="prevNum" value="'.$prevNum.'" />';
    echo '<input type="hidden" name="nextNum" value="'.$nextNum.'" />';
    echo 'Rows To Show: <select name="limit">
        <option value="25"';
    if($limit = "25")
        echo ' SELECTED';
    echo '>25</option>
        <option value="50"';
    if($limit = "50")
        echo ' SELECTED';
    echo '>50</option>
        </select>';
    if($prevNum > 0)
        echo '<input type="submit" name="prevBtn" value="Previous" />';
    if($limit = $rowCount)
        echo '<input type="submit" name="nextBtn" value="Next" />';
    echo $echo;
    echo '</form>';
    
}
?>
