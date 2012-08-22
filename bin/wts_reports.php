<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function displayReportMenu($config){
    if($config->adminLvl >= 25){
        echo '<div class="divider"></div>';
        echo "<h2>Supervisor Menu</h2>";
        echo '<li><a href="?submittedRequests=true">Submitted Requests by Division and by Dates or Pay Period</a></li>';
        echo '<li><a href="?subReqCal=true">Submitted Requests Calendar</a></li>';
        echo '<li><a href="?lookup=true">Submitted Request by Employee by Date</a></li>';
        echo '</ul>';
    }
}

function reportsCal($config){
    $month =isset($_POST['mon']) ? $_POST['mon'] : date('n');
    $year =isset($_POST['year']) ? $_POST['year'] : date('Y');
    
    $passedDates = "";
    
    viewClandar($config, $month, $year);
}
?>
