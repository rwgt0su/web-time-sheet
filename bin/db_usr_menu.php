<?phpfunction displayMenu($config){	        //Menu Items        echo '<h2>User Menu</h2>';        echo '<ul>';        echo '<li><a href="'.$_SERVER['PHP_SELF'].'" >Home</a></li>';				        echo '<li><a href="?action=changePass">Change Your Password</a><br /><br />';        echo '<li><a href="https://mail.mahoningcountyoh.gov" target="_blank">County E-Mail</a></li>';        echo '<li><a href="http://mail.sheriff.mahoning.local" target="_blank">Sheriff E-Mail</a></li>';        echo '<li><a href="http://techsupport.sheriff.mahoning.local" target="_blank">Technical Support</a></li>';                if (isValidUser()){            echo '<li><a href="?leave=true">Leave Request</a></li>';            echo '<li><a href="?pending=true">Pending Requests</a></li>';        }        echo '</ul>';        displaySupv($config);        displayAdmin($config);    }       function displaySupv($config){    if($config->adminLvl > 0){        echo '<div class="divider"></div>';        echo "<h2>Supervisor Menu</h2>";        echo '<ul>';        echo '<li><a href="?approve=true">Leave Approval</a></li>';        echo '</ul>';    }}function displayAdmin($config){    if($config->adminLvl > 75){            echo '<div class="divider"></div>';            echo "<h2>Admin Menu</h2>";            echo '<ul>';            echo '<li><a href="?dbtest=true">DB Query Test</a></li>';            echo '<li><a href="login.php">Login Test</a></li>';            echo '<li><a href="?newuser=true">Insert New User</a></li>';            echo '<li><a href="emp_query_form.php">Employee Lookup</a></li>';            echo '<li><a href="?usermenu=true">User Manager</a></li>';            echo '<li><a href="viewhistory.php">History</a></li>';            echo '<li><a href="?pending=true">Pending Requests</a></li>';            echo '</ul>';    }   }?>