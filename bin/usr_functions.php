<?php
function registerUser($user,$pass1,$pass2,$adminLvl, $useAD = "0"){
	$errorText = '';
 
	// Check passwords
	if (strcmp($pass1, $pass2) != 0){
		$errorText = "Passwords are not identical!";
	}
	elseif (strlen($pass1) < 6){
		$errorText = "Password is too short!";
	}
	else{
        // If everything is OK -> store user data
        //Hash the password with salt
		$userpass = saltyHash($pass1);
		//popUpMessage("userpass: ".$userpass);
		
		//Insert new user record using $user, $pass1, $adminLvl
		$mysqli = connectToSQL();
                $myq = "SELECT *
                    FROM `EMPLOYEE`
                    WHERE `ID` =  '".strtoupper($user)."'";
                $result = $mysqli->query($myq);
                
                SQLerrorCatch($mysqli, $result);
                if($result->num_rows < 1){
                    $myq = "INSERT INTO EMPLOYEE 
                        (ID,PASSWD,ADMINLVL,isLDAP,ACTIVE) 
                        VALUES ('".strtoupper($user)."','".$userpass."','".$adminLvl."','".$useAD."', '1')";
                    $result = $mysqli->query($myq);
                    SQLerrorCatch($mysqli, $result);
                }
                else 
                    $errorText = "User already in Database, did not update or add";
		
	}
	return $errorText;
} // end registerUser()
function resetPass($user, $pass1, $pass2, $admin) {
    //reset $user's password to $pass
     
    // Check passwords match
	if (strcmp($pass1, $pass2) != 0){
		$error = "Passwords are not identical!";
                return $error;
	}
	else if (strlen($pass1) < 6){
		$error = "Password is too short!";
                return $error;
	}
        
    $mysqli = connectToSQL();
    $user = strtoupper($mysqli->real_escape_string($user));
    $pass1 = $mysqli->real_escape_string($pass1);
    $myq="UPDATE EMPLOYEE SET PASSWD='".saltyHash($pass1)."', ADMINLVL=". $admin ." " 
            . "WHERE ID='". $user . "' ";
    $result = $mysqli->query($myq);

    //show SQL error msg if query failed
    if (!$result) {
    throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    $error = "DATABASE ERROR";
    }
    else
        $error = '';
    return $error;
}
function loginUser($user,$pass){
	$errorText = '';
	$validUser = false;

        //user lookup
        $mysqli = connectToSQL();
        $user = strtoupper($mysqli->real_escape_string($user));
        $myq="SELECT IDNUM, ID, PASSWD, ADMINLVL FROM EMPLOYEE WHERE ID='". $user . "'";
        $result = $mysqli->query($myq);
        
        //show SQL error msg if query failed
        if (!$result) {
        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
        }
        
        //no loop, should be exactly one result
        $resultAssoc = $result->fetch_assoc(); 
        
	// Check user existence
        if (strcasecmp($user, $resultAssoc['ID']) == 0) 
       {
            $errorText = "User Found";
            $admin = $resultAssoc['ADMINLVL'];
        

            //check password entry with stored password
            if (strcmp(trim($resultAssoc['PASSWD']), trim(saltyHash($pass))) == 0)
            {
				$errorText .= " and Valid password ";
                $_SESSION['userIDnum'] = $resultAssoc['IDNUM'];
                $_SESSION['userName'] = $user;
                $_SESSION['admin'] = $admin;
                $_SESSION['validUser'] = true;
                $_SESSION['timeout'] = time();
                $validUser = true;
                echo '<meta http-equiv="refresh" content="0;url='.$_SERVER['PHP_SELF'].'" />';
            }
       }
       else{
            $validUser = false;
       }

    if ($validUser != true)
        $errorText .= "Invalid username or password!";

   else{
        $errorText = NULL;
    }
	
	return $errorText;
}
function loginLDAPUser($user,$pass,$config){
	$errorText = '';
	$validUser = false;

        //user lookup
        $mysqli = $config->mysqli;
        $sql_user = strtoupper($mysqli->real_escape_string($user));
        $myq = "SELECT * FROM EMPLOYEE WHERE ID='". $sql_user . "'";
        $result = $mysqli->query($myq);
        
        //show SQL error msg if query failed
        if (!$result) {
            throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
        }
        
        //no loop, should be exactly one result
        $resultAssoc = $result->fetch_assoc(); 
        
	// Check user existence
        if (strcasecmp($user, $resultAssoc['ID']) == 0) {
            $errorText = "User Found <br />";
            $admin = $resultAssoc['ADMINLVL'];
            
            //Check LDAP status
            if ($resultAssoc['isLDAP']){
                //login using LDAP Password
                if ($user != "" && $pass != "") {
                    $ds = ldap_connect($config->ldap_server);
                    $ldaprdn = $user . '@' . $config->domain;
                    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
                    ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
                    if($ldapbind = ldap_bind($ds, $ldaprdn, $pass)){ 
                        //Authorization success
                        $errorText .= " and Valid password ";
                        
                        //Set Last Login
                        $_SESSION['lastLogin'] = $resultAssoc['LASTLOGIN'];
                        //Update last login
                        $myq = "UPDATE `PAYROLL`.`EMPLOYEE` SET `LASTLOGIN` = NOW() WHERE CONVERT(`EMPLOYEE`.`ID` USING utf8) = '".strtoupper($user)."' LIMIT 1;";
                        $mysqli = connectToSQL();
                        $result = $mysqli->query($myq);

                        //show SQL error msg if query failed
                        if (!$result) {
                            throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
                        }
                        $_SESSION['userIDnum'] = $resultAssoc['IDNUM'];
                        $_SESSION['userName'] = $user;
                        $_SESSION['admin'] = $admin;
                        $_SESSION['validUser'] = true;
                        $_SESSION['isLDAP'] = true;
                        $_SESSION['timeout'] = time();
                        $validUser = true;
                        echo '<meta http-equiv="refresh" content="0;url='.$_SERVER['REQUEST_URI'].'" />';
                    }
                    else
                        $errorText .= "Failed to authenticate user: " . $ldapbind;
                }                        
            }
            else{
                //check password entry with database stored password
                if (strcmp(trim($resultAssoc['PASSWD']), trim(saltyHash($pass))) == 0){
                    //Set Last Login
                    $_SESSION['lastLogin'] = $resultAssoc['LASTLOGIN'];
                    //Update last login
                    $myq = "UPDATE `PAYROLL`.`EMPLOYEE` SET `LASTLOGIN` = NOW() WHERE CONVERT(`EMPLOYEE`.`ID` USING utf8) = '".strtoupper($user)."' LIMIT 1;";
                    $mysqli = connectToSQL();
                    $result = $mysqli->query($myq);

                    //show SQL error msg if query failed
                    if (!$result) {
                        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
                    }
                    $errorText .= " and Valid password ";
                    $_SESSION['userIDnum'] = $resultAssoc['IDNUM'];
                    $_SESSION['userName'] = $user;
                    $_SESSION['admin'] = $admin;
                    $_SESSION['validUser'] = true;
                    $_SESSION['isLDAP'] = false;
                    $_SESSION['timeout'] = time();
                    $validUser = true;
                    echo '<meta http-equiv="refresh" content="0;url='.$_SERVER['PHP_SELF'].'" />';
                }
            }
       }
       //User not found within database, check for Active Directory
       else{
            if ($user != "" && $pass != "") {
                $ds = ldap_connect($config->ldap_server);
                $ldaprdn = $user . '@' . $config->domain;
                ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
                ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
                if($ldapbind = ldap_bind($ds, $ldaprdn, $pass)){ 
                    //Authorization success
                    $admin = "0";
                    $_SESSION['lastLogin'] = "Never";
                    registerUser($user, $pass, $pass, $admin, "1");
                    //query to get the new auto_incremented IDNUM
                    $myq = "SELECT IDNUM FROM EMPLOYEE WHERE ID='".$user."'";
                    $resultAssoc = $mysqli->query($myq);
                    SQLerrorCatch($mysqli, $result);
                    
                    $errorText .= " and Valid password ";
                    $_SESSION['userIDnum'] = $resultAssoc['IDNUM'];
                    $_SESSION['userName'] = $user;
                    $_SESSION['admin'] = $admin;
                    $_SESSION['validUser'] = true;
                    $_SESSION['isLDAP'] = true;
                    $_SESSION['timeout'] = time();
                    $validUser = true;
                    echo '<meta http-equiv="refresh" content="0;url='.$_SERVER['PHP_SELF'].'?updateProfile=true" />';
                    
                }
                else
                    $errorText .= "Failed to authenticate user: " . $ldapbind;
            }
            $errorText .= "Invalid Input: Missing Arguments";
            
       }

    if ($validUser != true)
        $errorText .= "Invalid username or password!";

   else{
        $errorText = NULL;
    }
	
    return $errorText;
}
function logoutUser($message){
	unset($_SESSION['validUser']);
	unset($_SESSION['userName']);
	unset($_SESSION['admin']);
        unset($_SESSION['timeout']);
        unset($_SESSION['isLDAP']);
        unset($_SESSION['lastLogin']);
        
	session_destroy(); 
        
        echo '<meta http-equiv="refresh" content="1;url='.$_SERVER['PHP_SELF'].'" />';
        echo '<div class="post">'.$message.'<div class="clear"></div></div><div class="divider"></div>';
}
function displayUpdateProfile($config){
    $mysqli = $config->mysqli;   
    if(isset($_POST['updateBtn'])){
        $fname = isset($_POST['fname']) ? $mysqli->real_escape_string( strtoupper($_POST['fname']) ) : false;
        $lname = isset($_POST['lname']) ? $mysqli->real_escape_string( strtoupper($_POST['lname']) ) : false;
        $rankID = isset($_POST['rankID']) ? $_POST['rankID'] : '';
        $divisionID = isset($_POST['divisionID']) ? $_POST['divisionID'] : false;
        $assignID = isset($_POST['assignID']) ? $_POST['assignID'] : false;
        $supvID = isset($_POST['supvID']) ? $_POST['supvID'] : false;
        $hireDate = isset($_POST['hireDate']) ? $_POST['hireDate'] : false;
        $radioID = isset($_POST['radioID']) ? $mysqli->real_escape_string($_POST['radioID']) : false;
        $munisID = isset($_POST['munisID']) ? $mysqli->real_escape_string($_POST['munisID']) : false;
        $userID = isset($_POST['userID']) ? $_POST['userID'] : false;
        $address = isset($_POST['address']) ? $_POST['address'] : false;
        $phone = isset($_POST['phone']) ? $_POST['phone'] : false;
        $dob = isset($_POST['dob']) ? $_POST['dob'] : false;
        $emergency = isset($_POST['emergency']) ? $_POST['emergency'] : false;
        
        $myq = "UPDATE `PAYROLL`.`EMPLOYEE` SET 
            `MUNIS` = '".$munisID."',
            `LNAME` = '".$lname."',
            `FNAME` = '".$fname."',
            `GRADE` = '".$rankID."',
            `DIVISIONID` = '".$divisionID."',
            `SUPV` = '".$supvID."',
            `ASSIGN` = '".$assignID."',
            `TIS` = '".Date('Y-m-d', strtotime($hireDate))."',    
            `RADIO` = '".$radioID."',
             ADDRESS = '".$address."',
             PHONE = '".$phone."',
             DOB = '".Date('Y-m-d', strtotime($dob))."',
             EMERGCON = '".$emergency."',
            ADMINLVL = ".$config->adminLvl." 
            WHERE ID = '".$userID."'";
            echo $myq; //DEBUG
        //Perform SQL Query
        $result = $mysqli->query($myq);
        
        //show SQL error msg if query failed
        if (!SQLerrorCatch($mysqli, $result)) 
            $result = "Successfully Updated Profile";
    }   
    else{
        //Get stored information (first view)
        $sql_user = strtoupper($mysqli->real_escape_string($_SESSION['userName']));
        if(isset($_GET['userIDnum'])){
            $myq = "SELECT * FROM EMPLOYEE WHERE IDNUM=".$_GET['userIDnum'];
            
        }
        else
            $myq = "SELECT * FROM EMPLOYEE WHERE ID='". $sql_user . "'";
        
        $result = $mysqli->query($myq);
        
        //show SQL error msg if query failed
        if (!$result) {
            throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
        }
        
        //no loop, should be exactly one result
        $resultAssoc = $result->fetch_assoc();
        
        $fname = $resultAssoc['FNAME'];
        $lname = $resultAssoc['LNAME'];
        $rankID = $resultAssoc['GRADE'];
        $divisionID = $resultAssoc['DIVISIONID'];
        $assignID = $resultAssoc['ASSIGN'];
        $supvID = $resultAssoc['SUPV'];
        $hireDate = $resultAssoc['TIS'];
        $radioID = $resultAssoc['RADIO'];
        $munisID = $resultAssoc['MUNIS'];
        $address = $resultAssoc['ADDRESS'];
        $phone = $resultAssoc['PHONE'];
        $dob = $resultAssoc['DOB'];
        $emergency = $resultAssoc['EMERGCON'];
        
    }
    $username = strtoupper($_SESSION['userName']);
    ?>
        <form name="update" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        </div><div align="center" class="login">
            <table>
            <?php if ($_SESSION['admin'] >= 50) { 
                  echo "<tr><td>User: </td>";
                  $myq = "SELECT IDNUM, ID, CONCAT_WS(', ',LNAME,FNAME) FULLNAME FROM EMPLOYEE ORDER BY LNAME";
                  $result = $mysqli->query($myq);
                  SQLerrorCatch($mysqli, $result);
                  
                  $uri =  preg_replace("/&userIDnum=.*/", "", $_SERVER['REQUEST_URI']);
                  ?> <td> <select name="userIDnum" onchange="window.location=<?php echo "'".$uri."'"; ?> + '&userIDnum=' + this.value"> <?php
                  while ($row = $result->fetch_assoc()) {
                      echo '<option value="' . $row["IDNUM"] . '"';
                      if(isset($_GET['userIDnum'])) {
                          if($row['IDNUM']==$_GET['userIDnum']){
                              echo "selected";
                              $chosenUserID = $row['ID'];
                          }
                      }
                      else {
                      if($row['ID']==$username)
                          echo "selected";
                      }
                      echo ">".$row['FULLNAME']."</option>";
                  }
                 echo "</select></tr>";
                 echo '<input type="hidden" name="userID" value="'.$chosenUserID.'" />';
                 }
                  else  {  ?>                         
            <h3>Username: <?php echo $username; ?></h3>
            <input type="hidden" name="userID" value="<?php echo $username; ?>" />
            <?php } ?>
                
                    <tr><td>First Name: </td><td><input name="fname" type="text" <?php if(!$fname) showInputBoxError(); else echo 'value="'.$fname.'"'; ?> /></td></tr>
                    <tr><td>Last Name: </td><td><input name="lname" type="text" <?php if(!$lname) showInputBoxError(); else echo 'value="'.$lname.'"'; ?> /></td></tr>
                    <?php 
                    echo "<tr><td>Division:</td><td>"; displayDivisionID("divisionID", $divisionID); echo "</td></tr>";
                    echo "<tr><td>Supervisor:</td><td>"; displaySUPVDropDown("supvID", $supvID); echo "</td></tr>";
                    
                    //Payrate dependent
                    if($config->adminLvl >= 75){
                        echo "<tr><td>Rank:</td><td>"; displayRanks("rankID", $rankID); echo "</td></tr>";
                        echo "<tr><td>Assigned Shift:</td><td>"; displayAssign("assignID", $assignID); echo "</td></tr>";
                        ?>
                        <tr><td>MUNIS ID: </td><td><input name="munisID" type="text" <?php if(!$munisID) showInputBoxError(); else echo 'value="'.$munisID.'"'; ?> /></td></tr>
                        <?php
                    }
                    else{
                        ?>
                        <input type="hidden" name="rankID" value="<?php echo $rankID; ?>" />
                        <input type="hidden" name="assignID" value="<?php echo $assignID; ?>" />
                        <input type="hidden" name="munisID" value="<?php echo $munisID; ?>" />
                        <?php
                    }
                    
                    ?>
                    <tr><td>Hire Date: </td><td><?php displayDateSelect("hireDate", "date_1", $hireDate, $required=true); ?></td></tr>
                    <tr><td>Radio Number: </td><td><input name="radioID" type="text" <?php if(!$radioID) showInputBoxError(); else echo 'value="'.$radioID.'"'; ?> /></td></tr>
                    <tr><td>Address: </td><td><input name="address" type="text" <?php if(!$address) showInputBoxError(); else echo 'value="'.$address.'"'; ?> /></td></tr>
                    <tr><td>Phone: </td><td><input name="phone" type="text" <?php if(!$phone) showInputBoxError(); else echo 'value="'.$phone.'"'; ?> /></td></tr>
                    <tr><td>Date of Birth: </td><td><?php displayDateSelect("dob", "date_2", $dob, $required=true); ?></td></tr>
                    <script type="text/javascript">
                    $('.datepicker').datepicker( "option", "yearRange", "2000:2010" );
                    </script>
                    <tr><td>Emergency Contact: </td><td><input name="emergency" type="text" <?php if(!$emergency) showInputBoxError(); else echo 'value="'.$emergency.'"'; ?> /></td></tr>
                    
                    <tr><td></td><td><input type="submit" name="updateBtn" value="Update Profile" /></td></tr>
                    <?php 
                    /* this makes no sense, what is this supposed to do??
                    if(isset($_POST['updateBtn'])){
                      echo '<tr><td></td><td>'. $result .'</td></tr>';  
                    }*/
                    ?>
                </table>
            </div><div class="clear"></div>
        </form>
        <div class="divider"></div>
        
    <?php
}
function displayRanks($selectName, $selected=false){
   $mysqli = connectToSQL();
    $myq = "SELECT * FROM `GRADE` WHERE 1";
    $result = $mysqli->query($myq);

    //show SQL error msg if query failed
    if (!$result) {
        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    }
    echo '<select name="'.$selectName.'"><option value=""></option>';
    
    while ($row = $result->fetch_assoc()){
        echo '<option value="'.$row['ABBREV'].'"';
        if (strcmp($selected, $row['ABBREV']) == 0)
            echo " selected ";
        echo '>'.$row['DESCR'].'</option>';
    }

    echo '</select>';
}
function displayDivisionID($selectName, $selected=false){
    $mysqli = connectToSQL();
    $myq = "SELECT * FROM `DIVISION` WHERE 1";
    $result = $mysqli->query($myq);

    //show SQL error msg if query failed
    if (!$result) {
        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    }
    echo '<select name="'.$selectName.'"><option value=""></option>';
    
    while ($row = $result->fetch_assoc()){
        echo '<option value="'.$row['DIVISIONID'].'"';
        if (strcmp($selected, $row['DIVISIONID']) == 0)
            echo " selected ";
        echo '>'.$row['DESCR'].'</option>';
    }

    echo '</select>';

}
function displayAssign($selectName, $selected = false){
    $mysqli = connectToSQL();
    $myq = "SELECT * FROM `ASSIGNMENT` WHERE 1";
    $result = $mysqli->query($myq);

    //show SQL error msg if query failed
    if (!$result) {
        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    }
    echo '<select name="'.$selectName.'"><option value=""></option>';
    
    while ($row = $result->fetch_assoc()){
        echo '<option value="'.$row['ABBREV'].'"';
        if (strcmp($selected, $row['ABBREV']) == 0)
            echo " selected ";
        echo '>'.$row['DESCR'].'</option>';
    }

    echo '</select>';

}
function displaySUPVDropDown($selectName, $selected = false){
    
    $mysqli = connectToSQL();
    $myq = "SELECT * FROM `EMPLOYEE` WHERE `ADMINLVL` >=25";
    $result = $mysqli->query($myq);

    //show SQL error msg if query failed
    if (!$result) {
        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    }
    echo '<select name="'.$selectName.'"><option value=""></option>';
    
    while ($row = $result->fetch_assoc()){
        echo '<option value="'.$row['ID'].'"';
        if (strcmp($selected, $row['ID']) == 0)
            echo " selected ";
        echo '>'.$row['GRADE']." ".$row['FNAME']. " ".$row['LNAME']." (".$row['ID'].')</option>';
    }

    echo '</select>';
}
function displayDateSelect($inputName, $id, $selected = false, $required = false){
    ?>
    <link type="text/css" href="bin/jQuery/css/smoothness/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <script type="text/javascript" src="bin/jQuery/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="bin/jQuery/js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript">
        $(function() {
            // Datepicker
            $('.datepicker').datepicker({
                    inline: true,
                    changeYear: true,
                    yearRange: "-100:+100"
            });
        });
    </script>
    <input name="<?php echo $inputName ?>" type="text" class="datepicker" id="<?php echo $id; ?>" <?php if(!$selected){ if($required) showInputBoxError (); } else echo 'value="'.$selected.'"'; ?> />
    <?php
}
function delUser($user){
	$errorText = '';
        
        //remove user from database
        $mysqli = connectToSQL();
        $myq="DELETE FROM EMPLOYEE WHERE ID='". $mysqli->real_escape_string($user) . "'";
        $result = $mysqli->query($myq);
        
        if(!$result)
            $errorText = "No such user";
        else
            $errorText = "The User, ".$user." has been Removed";

   return $errorText;
}
function displayLogout(){
        $user = $_SESSION['userName'];
    	echo '<div id="result" align="right">Logged in as: <font size="3">';
        echo $user;
		echo "</font><br />";
        
                // Check user existence
                echo "Last Login: " .$_SESSION['lastLogin'];
		echo '<br /><a href="?logout=true">Log Out </a><br /><br />';
        echo "</div>";
}
function displayLogin($config){
    if (!isValidUser()){
        $error = '0';
    
        if (isset($_POST['submitBtn'])){
            $noPass = false;
            $noUser = false;
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            if(empty($username)) {
                    $noUser = true;
            }
            if(empty($password)){
                    $noPass = true;
            }

            // Try to login the user
            if($noUser && $noPass){
                    $error = 'Please Provide a Username and Password';
            }
            else{
                    //$error = loginUser($username,$password);
                    $error = loginLDAPUser($username,$password, $config);
            }
        } 
        if ($error != '') {
            //First time seeing this screen or Invalid User Input
            ?>
            <div class="thumbnail"><img src="/style/icon4.gif" alt="" /></div>
            <h3><?php echo $config->getTitle(); ?></h3>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="loginform">
                <table width="50%">
                <tr><td>Username:</td><td> <input class="text" name="username" type="text" 
                                        <?php echo "value=\"$username\""; if ($noUser) echo "style=\"background:#FFFFFF;border:1px solid #FF0000;\""; ?> /></td></tr>
                <tr><td>Password:</td><td> <input class="text" name="password" type="password" 
                                        <?php if (isset($_POST['submitBtn'])) echo "style=\"background:#FFFFFF;border:1px solid #FF0000;\""; ?>/></td></tr>
                <tr><td>&nbsp</td><td>&nbsp</td></tr>
                <tr><td></td><td align="center"><input style="font-size: 20px;" class="text" type="submit" name="submitBtn" value="Login" /></td></tr>
                </table>
                <div class="post_footer">
                    <div align="center"></div>
                </div>
            </form>
            <?php
            //User had input but had an error.  Display the error
            if (isset($_POST['submitBtn'])){        
                ?>
                <div class="thumbnail"><img src="/style/icon2.gif" alt="" /></div>  
                <h3>Error Message:</h3>
                <p><?php echo $error; ?></p>
            <?php
            }
        }
    }
    else{
        displayLogout();
    }
}
function displayInsertUser(){

    if (isset($_POST['submit'])) {
        $ID=$_POST['ID'];
        $pass1=$_POST['pass1'];
        $pass2=$_POST['pass1'];
        $adminLvl=$_POST['adminLvl'];

        $msg = registerUser($ID,$pass1,$pass2,$adminLvl);
        
        if(empty($msg))
            echo "New user <b>".strtoupper($ID)."</b> added successfully.";
    }
        
?>
        
<form name="insert" method="post" action="/?newuser=true">
        <p>Add a new user:</p>
        <p>Login ID:<input type="text" name="ID"></p>
	<p>Password:<input type="password" name="pass1"></p>
        <p>Re-type password:<input type="password" name="pass2"></p>
        <p>Admin Level:<input type="text" name="adminLvl"></p>
        
        <p><input type="submit" name="submit" value="Submit"></p>
</form>
        
<?php        
}  
function disableUser($config, $userID){
    if($config->adminLvl >= 50){
        $mysqli = $config->mysqli;
        $myq = "UPDATE `PAYROLL`.`EMPLOYEE` SET 
            `ACTIVE` = '0',
            WHERE EMPLOYEE.IDNUM = '".$userID."'";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
    }
}

?>
