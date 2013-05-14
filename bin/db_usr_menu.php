<?phpfunction displayMenu($config){	        //Menu Items        echo '<h2>Main Menu</h2>';        echo '<ul>';        echo '<li><a href="'.$_SERVER['PHP_SELF'].'" >Home</a></li>';				        echo '<li><a href="https://mail.mahoningcountyoh.gov" target="_blank">County E-Mail</a></li>';        echo '<li><a href="http://mail.sheriff.mahoning.local" target="_blank">Sheriff E-Mail</a></li>';        echo '<li><a href="http://10.1.30.89/portal" target="_blank">Technical Support</a></li>';        echo '<li><a href="https://ijis.mahoningsheriff.com" target="_blank">IJIS Website</a></li>';        echo '<li><a href="http://connect.mahoningcountyoh.gov" target="_blank">Remote Support</a></li>';        echo '<li><a href="https://ijis.mahoningsheriff.com" target="_blank">IJIS Website</a></li>';        echo '</ul>';        if (isValidUser($config)){            echo '<h2>User Menu</h2>';            echo '<ul>';            echo '<li><a href="?updateProfile=true">Update Profile</a></li>';            if($_SESSION['isLDAP'] == false)                echo '<li><a href="?usermenu=true&ChangeBtn=true">Change Your Password</a><br /><br />';            echo '<li><a href="?secLog=true">Secondary Logs</a></li>';             //if($config->adminLvl >= 25)                echo '<li><a href="?radioLog=true">Inventory Checkout Logs</a></li>';             //echo '<li><a href="?leave=true">Request Form</a></li>';            echo '<li><a href="?isTimeRequestForm=true">Time Request Form</a></li>';            echo '<li><a href="?myReq=true">My Submitted Requests</a></li>';            echo '<li><a href="?myInv=true">My Inventory</a></li>';         }        echo '</ul>';        displaySupv($config);        displayAcct($config);        displayAdmin($config);    }       function displaySupv($config){    if($config->adminLvl > 0){        echo '<div class="divider"></div>';        echo "<h2>Supervisor Menu</h2>";        echo '<ul>';        echo '<li><a href="?reports=true">Reports</a></li>';        echo '<li><a href="?approve=true">Approve Leave Requests</a></li>';        echo '<li><a href="?secApprove=true">Approve Secondary Logs</a></li>';        if($config->adminLvl >=30)            echo '<li><a href="?isAnounceAdmin=true">Anouncement Manager</a></li>';        echo '</ul>';    }}function displayAcct($config){    if($config->adminLvl >= 50){            echo '<div class="divider"></div>';            echo "<h2>HR Menu</h2>";            echo '<ul>';            echo '<li><a href="?approvedUse=true">Approved Requests Report</a></li>';            echo '<li><a href="?usereport=true">Time Use/Gain Report</a></li>';            echo '<li><a href="?munis=true">MUNIS Entry Report</a></li>';            echo '<li><a href="?usermenu=true">User Manager</a></li>';            echo '<li><a href="?isAnounceAdmin=true">Anouncement Manager</a></li>';            echo '<li><a href="?phpMyEdit=true">Database Admin (phpMyEdit)</a></li>';            echo '</ul>';    }   }function displayAdmin($config){    if($config->adminLvl >= 75){            echo '<div class="divider"></div>';            echo "<h2>Admin Menu</h2>";            echo '<ul>';            echo '<li><a href="?resManage=true">Reserve Manager</a></li>';            echo '<li><a href="?phpMyEdit=true">Database Admin (phpMyEdit)</a></li>';            echo '<li><a href="?eventLogs=true">Event Logs</a></li>';            echo '</ul>';    }   }function displayPhpMyEditMenu() { ?>			 <h2>phpMyEdit Menu</h2>           <ul>           <li><a href="phpMyEdit/myEditEmployee.php" target="_blank">Employee Table</a></li>                      <li><a href="phpMyEdit/myEditRequest.php" target="_blank">Request Table</a><br /><br />              <li><a href="phpMyEdit/phpMyEditSetup.php" target="_blank">Setup (code generator)</a><br /><br />            </ul><?php  } ?>		  