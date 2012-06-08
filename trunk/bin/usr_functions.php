<?php
function registerUser($user,$pass1,$pass2, $adminLvl){
	$errorText = '';

	// Check passwords
	if ($pass1 != $pass2)
            $errorText = "Passwords are not identical!";
	elseif (strlen($pass1) < 6)
            $errorText = "Password is too short!";

        //Check for user existence

        // If everything is OK -> store user data
        //Hash the password
	$userpass = md5($pass1);

        //write data: $user, $pass1, $adminLvl

    fclose($pfile);


	return $errorText;
}

function loginUser($user,$pass){
	$errorText = '';
	$validUser = false;

        //user lookup
        $mysqli=connectToSQL(); 
        $query="SELECT ID, PASSWD FROM EMPLOYEE WHERE ID='". $user . "';";
        $result = $mysqli->query($query);
        
        $resultAssoc = $result->fetch_assoc(); //no loop, should be exactly one result
        
	// Check user existence
        if (strcasecmp($user, $resultAssoc['ID']) == 0) 
       {
            $errorText = "User Found";
            $admin = 100;
        
        /*if (strcmp($user, "user") == 0){
			$errorText = "User Found";
			$admin = 100;*/

            //check password entry with stored password
            if (strcmp(trim($resultAssoc['PASSWD']), trim(md5($pass))) == 0)
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

        //verify user exists
        //remove user from database

   return $error;
}

function displayLogin(){
    	echo '<div id="result" align="right">Logged in as: <font size="3">';
        echo $_SESSION['userName'];
		echo "</font>";
		echo '<br /><a href="wts_logout.php">Log Out </a><br /><br />';
        echo "</div>";
}

?>
