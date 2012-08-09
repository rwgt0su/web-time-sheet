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
        echo '</ul>';
    }
}
?>
