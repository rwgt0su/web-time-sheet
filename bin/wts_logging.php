<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function addLog($config, $event){
    $mysqli = $config->mysqli;
    $myq="INSERT INTO `WTS_EVENTS` (`IDNUM` ,`USERID` ,`USERIP` , DATE, `TIME` ,`DESCR`)
            VALUES ('NULL', '".$_SESSION['userIDnum']."', INET_ATON('${_SERVER['REMOTE_ADDR']}'), NOW(), NOW(), '".$event."')";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
}

function displayLogs($config){
    if($config->adminLvl >75){
        echo "<form name='custRange' action='".$_SERVER['REQUEST_URI']."' method='post'>";
        echo 'Date Range to Display (Blank will use today\'s Date)';
        echo "<p> Start";
        if ( isset($_POST['start']) && isset($_POST['end']) ) {
            displayDateSelect('start', 'date_1', $_POST['start'],false,false);   
            echo "End";
            displayDateSelect('end', 'date_2',$_POST['end'],false,false);
        }
        else{
            displayDateSelect('start', 'date_1', false,false,true);   
            echo "End";
            displayDateSelect('end', 'date_2',false,false,true);
        }
        echo "<input type='submit' value='Go' /></p>";

        //overwrite current period date variables with 
        //those provided by user
        if ( isset($_POST['start']) && isset($_POST['end']) ) {
            $startDate =  new DateTime( $_POST['start'] );
            $startDate = $startDate->format('Y-m-d');
            $endDate =  new DateTime( $_POST['end'] );
            $endDate = $endDate->format('Y-m-d');
        }
        else{
            $startDate = date("Y-m-d");
            $endDate = date("Y-m-d");
        }
        
        if($startDate == $endDate)
            $dateQ = "WHERE DATE = '".$startDate."'";
        else
            $dateQ = "WHERE DATE BETWEEN '". $startDate."' AND '".$endDate."'";
        
        $x = 0;
        $y = 0;
        $theTable = array(array());
        $theTable[$x][$y] = "Event#"; $y++;
        $theTable[$x][$y] = "User"; $y++;
        $theTable[$x][$y] = "User IP"; $y++;
        $theTable[$x][$y] = "Time of Event"; $y++;
        $theTable[$x][$y] = "Description of Event"; $y++;

        $mysqli = $config->mysqli;
        $myq="SELECT EMP.LNAME 'LName', EMP.FNAME 'FName', WTS_EVENTS.IDNUM 'refNo', 
                DATE_FORMAT(DATE,'%a %d %b %Y') 'Date',
                DATE_FORMAT(TIME,'%H%i') 'Time', 
                DESCR 'Descr', INET_NTOA(USERIP) 'UserIP'
            FROM WTS_EVENTS
            LEFT JOIN EMPLOYEE AS EMP ON EMP.IDNUM=WTS_EVENTS.USERID
            ".$dateQ;
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        while($row = $result->fetch_assoc()) {
            $x++;
            $y=0;
            $theTable[$x][$y] = $row['refNo']; $y++;
            $theTable[$x][$y] = $row['LName']. ', '.$row['FName']; $y++;
            $theTable[$x][$y] = $row['UserIP']; $y++;
            $theTable[$x][$y] = $row['Date'].' '.$row['Time']; $y++;
            $theTable[$x][$y] = $row['Descr']; $y++;

        }

        echo '<h3>User Event Logs</h3>';
        echo 'Showing events between '.$startDate.' and '.$endDate;
        showSortableTable($theTable, 1);
    }
    else
        echo '<h3>User Event Logs</h3>Access Denied!';
}

?>
