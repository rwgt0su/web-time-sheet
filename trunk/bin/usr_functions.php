<?php
function registerUser($user,$pass1,$pass2, $adminLvl){
	$errorText = '';

	// Check passwords
	if ($pass1 != $pass2)
            $errorText = "Passwords are not identical!";
	elseif (strlen($pass1) < 6)
            $errorText = "Password is too short!";

        // If everything is OK -> store user data
        //Hash the password with salt
	$userpass = saltyHash($pass1);

        //Insert new user record using $user, $pass1, $adminLvl
        $mysqli = connectToSQL();
        $myq = "INSERT INTO EMPLOYEE (ID,PASSWD,ADMINLVL) VALUES
                 ('".strtoupper($user)."','".$userpass."','".$adminLvl."')";
        $result = $mysqli->query($myq);
        
        //show SQL error msg if query failed
        if (!$result) {
        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
        }
        
        if($result)
            $errorText = "New user registered successfully.";

	return $errorText;
}

function loginUser($user,$pass){
	$errorText = '';
	$validUser = false;

        //user lookup
        $mysqli = connectToSQL();
        $myq="SELECT ID, PASSWD FROM EMPLOYEE WHERE ID='". strtoupper($user) . "'";
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
            $admin = 100;
        

            //check password entry with stored password
            if (strcmp(trim($resultAssoc['PASSWD']), trim(saltyHash($pass))) == 0)
            {
				$errorText .= " and Valid password ";
                $_SESSION['userName'] = $user;
                $_SESSION['admin'] = $admin;
                $_SESSION['validUser'] = true;
				$validUser = true;
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

function logoutUser(){
	unset($_SESSION['validUser']);
	unset($_SESSION['userName']);
	unset($_SESSION['admin']);
	
	session_destroy(); 
}
function delUser($user){
	$errorText = '';
        
        //remove user from database
        $mysqli = connectToSQL();
        $myq="DELETE FROM EMPLOYEE WHERE ID='". $user . "'";
        $result = $mysqli->query($myq);
        
        if(!$result)
            $errorText = "No such user";
        
        

   return $errorText;
}

function displayLogin(){
    	echo '<div id="result" align="right">Logged in as: <font size="3">';
        echo $_SESSION['userName'];
		echo "</font>";
		echo '<br /><a href="wts_logout.php">Log Out </a><br /><br />';
        echo "</div>";
}

?>
