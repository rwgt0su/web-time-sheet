<?phpfunction displayMenu(){	        //Menu Items        echo '<h2>User Menu</h2>';        echo '<ul>';        echo '<li><a href="/" >Home</a></li>';				        echo '<li><a href="?action=changePass">Change Your Password</a><br /><br />';        echo '<li><a href="https://mail.mahoningcountyoh.gov" target="_blank">County E-Mail</a></li>';        echo '<li><a href="http://mail.sheriff.mahoning.local" target="_blank">Sheriff E-Mail</a></li>';        echo '<li><a href="http://techsupport.sheriff.mahoning.local" target="_blank">Technical Support</a></li>';         echo '</ul>';    }               function displayAdmin($config){    if($config->getAdmin() > 75){            echo "<h2>Admin Menu</h2>";            echo '<ul>';            echo '<li><a href="/?dbtest=true">DB Query Test</a></li>';            echo '<li><a href="login.php">Login Test</a></li>';            echo '<li><a href="insert_user.php">Insert New User</a></li>';            echo '<li><a href="emp_query_form.php">Employee Lookup</a></li>';            echo '<li><a href="wts_user.php">User Manager</a></li>';            echo '<li><a href="viewhistory.php">History</a></li>';            echo '<li><a href="leave_form.php">Leave Request</a></li>';            echo '<li><a href="pending_requests.php">Pending Requests</a></li>';            echo '</ul>';    }   }?>