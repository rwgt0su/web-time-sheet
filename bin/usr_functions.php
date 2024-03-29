<?php
function registerUser($user,$pass1,$pass2,$adminLvl, $useAD = "0", $useMCO="0"){
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
		
		//Insert new user record using $user, $pass1, $adminLvl if user doesn't already exist
		$mysqli = connectToSQL();
                $myq = "SELECT *
                    FROM `EMPLOYEE`
                    WHERE `ID` =  '".strtoupper($user)."'";
                $result = $mysqli->query($myq);
                
                SQLerrorCatch($mysqli, $result);
                if($result->num_rows < 1){
                    $myq = "INSERT INTO EMPLOYEE 
                        (ID,PASSWD,ADMINLVL,isLDAP,IS_ACTIVE,isMCO) 
                        VALUES ('".strtoupper($user)."','".$userpass."','".$adminLvl."','".$useAD."', '1','".$useMCO."')";                    $result = $mysqli->query($myq);
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
    if(!empty($pass1)){
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
    }
    else{
            $mysqli = connectToSQL();
        $user = strtoupper($mysqli->real_escape_string($user));
        $pass1 = $mysqli->real_escape_string($pass1);
        $myq="UPDATE EMPLOYEE SET ADMINLVL=". $admin ." " 
                . "WHERE ID='". $user . "' ";
        $result = $mysqli->query($myq);
        $error = "Admin Level Changed Successfully";
    }

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
function loginLDAPUser($user,$pass,$config, $domain=false){
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
        } else {}
        
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
                    if($resultAssoc['isMCO']){
                        $ldap_domain = $config->ldap_MCO_domain;
                        $cnx = ldap_connect($config->ldap_MCO_server);
                    }
                    else{
                        $ldap_domain =$config->domain;
                        $cnx = ldap_connect($config->ldap_server);
                    }
                    $ldaprdn = $user . '@' . $ldap_domain;
                    ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
                    ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
                    if (ldap_bind($cnx, $ldaprdn, $pass)) { 
                        //Authorization success
                        $errorText .= " and Valid password ";
                        
                        //Set Last Login
                        $lastLogin = new DateTime($resultAssoc['LASTLOGIN']);
                        $_SESSION['lastLogin'] = $lastLogin->format('m-d-Y H:i');
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
                        //session_regenerate_id();
                        $validUser = true;
                        $configNew = new Config();
                        $configNew->setAdmin(isset($_SESSION['admin']) ? $_SESSION['admin'] : -1);
                        addLog($configNew, 'Logged in to system');
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
                    //session_regenerate_id();
                    $validUser = true;
                    $configNew = new Config();
                    $configNew->setAdmin(isset($_SESSION['admin']) ? $_SESSION['admin'] : -1);
                    addLog($configNew, 'Logged in to system');
                    echo '<meta http-equiv="refresh" content="0;url='.$_SERVER['PHP_SELF'].'" />';
                }
            }
       }
       //User not found within database, check for Active Directory
       else{
            if ($user != "" && $pass != "") {
                //Attempt login and registration for Mahoning County Domain
//                if(strcmp($domain, "MAHONINGCO") == 0){
                    $ldap_domain = $config->ldap_MCO_domain;
                    $cnx = ldap_connect($config->ldap_MCO_server);
//                }
//                else{
//                    $ldap_domain =$config->domain;
//                    $cnx = ldap_connect($config->ldap_server);
//                }
                $ldaprdn = $user . '@' . $ldap_domain;
                ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
                ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
                if ($ldapbind = ldap_bind($cnx, $ldaprdn, $pass)) {
                    //Authorization success
                    $admin = "0";
                    
                    error_reporting(E_ALL ^ E_NOTICE);   //Suppress some unnecessary messages
                    //Split given domain into LDAP Base DN
                    $temp = explode(".", $ldap_domain);
                    $dn = null;
                    foreach ($temp as $dc) {
                        if (empty($dn))
                            $dn = "DC=" . $dc;
                        else
                            $dn = $dn . ",DC=" . $dc;
                    }
//                    if(strcmp($domain, "MAHONINGCO") == 0)
//                        $dn = $config->ldap_MCO_OU.$dn;
                    $userToFind = $user;
                    $filter = "(&(objectCategory=person)(objectClass=user)";
                    $filter.="(|(samaccountname=*" . $userToFind . "*)(sn=*" . $userToFind . "*)(displayname=*" . $userToFind . "*)";
                    $filter.="(mail=*" . $userToFind . "*)(department=*" . $userToFind . "*)(title=*" . $userToFind . "*)))";  //Search fields
                    $isAuthOU = false;
                    $dnAppend = $dn;
                    foreach ($config->ldap_MCSO_OUS as $dn) {
                        $dn = $dn.$dnAppend;
                        $res = ldap_search($cnx, $dn, $filter);
                        $info = ldap_get_entries($cnx, $res);
                        if($info['count'] > 0){
                            $isAuthOU = true;
                            break;
                        }
                    }
                    $res = ldap_search($cnx, $dn, $filter);
                    
                    $info = ldap_get_entries($cnx, $res);
                    if($isAuthOU){
                        if(strcmp($domain, "MAHONINGCO") == 0)
                            registerUser($user, $pass, $pass, $admin, "1", "1");
                        else
                            registerUser($user, $pass, $pass, $admin, "1");

                        $idNum = getUserID($config, $user);
                        $myq = "UPDATE `PAYROLL`.`EMPLOYEE` SET 
                            `LNAME` = '" . $info[0]["sn"][0] . "',
                            `FNAME` = '" . $info[0]["givenname"][0] . "'
                            WHERE EMPLOYEE.IDNUM = '" . $idNum . "'";
                        //Perform SQL Query
                        $result = $mysqli->query($myq);

                        $errorText .= " and Valid password ";

                        //Set Session variables
                        $_SESSION['userIDnum'] = getUserID($config, $user);
                        $_SESSION['lastLogin'] = "Never";
                        $_SESSION['userName'] = $user;
                        $_SESSION['admin'] = $admin;
                        $_SESSION['validUser'] = true;
                        $_SESSION['isLDAP'] = true;
                        $_SESSION['timeout'] = time();
                        //session_regenerate_id();
                        $validUser = true;
                        $configNew = new Config();
                        $configNew->setAdmin(isset($_SESSION['admin']) ? $_SESSION['admin'] : -1);
                        addLog($configNew, 'Logged in to system');
                        echo '<meta http-equiv="refresh" content="0;url='.$_SERVER['PHP_SELF'].'?updateProfile=true" />';
                    }
                    else{
                        $errorText .= "WARNING! Failed to authenticate as a Sheriff user: " . $ldapbind;
                    }
                    
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
function logoutUser($config, $message){
    addLog($config, 'Logged off the system');
    unset($_SESSION['userIDnum']);
    unset($_SESSION['validUser']);
    unset($_SESSION['userName']);
    unset($_SESSION['admin']);
    unset($_SESSION['timeout']);
    unset($_SESSION['isLDAP']);
    unset($_SESSION['lastLogin']);

    myAlertsLogout();
        
    session_destroy(); 

    echo '<meta http-equiv="refresh" content="1;url='.$_SERVER['PHP_SELF'].'" />';
    echo '<div class="post">'.$message.'<div class="clear"></div></div><div class="divider"></div>';
}
function displayUpdateProfile($config){
    //Get pass search results
    //var_dump($_POST); //DEBUG
    $foundUserFNAME = '';
    $foundUserLNAME = '';
    $foundUserName = '';
    $foundUserID = $_SESSION['userIDnum'];
    $totalRows = isset($_POST['totalRows']) ? $_POST['totalRows'] : 0;
    if($totalRows > 0) {
        //get post info providied from search results
        for($i=0;$i<=$totalRows;$i++){
            if(isset($_POST['foundUser'.$i])) {
                $foundUserID = $_POST['foundUserID'.$i];
                break;
            }//end if
        }//end for
    }//end If Nothing passed
    
    
    $mysqli = $config->mysqli;   
    if(isset($_POST['updateBtn'])){
        $fname = isset($_POST['fname']) ? $mysqli->real_escape_string( strtoupper($_POST['fname']) ) : false;
        $lname = isset($_POST['lname']) ? $mysqli->real_escape_string( strtoupper($_POST['lname']) ) : false;
        $rankID = isset($_POST['rankID']) ? $mysqli->real_escape_string($_POST['rankID']) : '';
        $divisionID = isset($_POST['divisionID']) ? $mysqli->real_escape_string($_POST['divisionID']) : false;
        $assignID = isset($_POST['assignID']) ? $mysqli->real_escape_string($_POST['assignID']) : false;
        $supvID = isset($_POST['supvID']) ? $mysqli->real_escape_string($_POST['supvID']) : false;
        $hireDate = isset($_POST['hireDate']) ? $mysqli->real_escape_string($_POST['hireDate']) : false;
        $radioID = isset($_POST['radioID']) ? $mysqli->real_escape_string($_POST['radioID']) : false;
        $munisID = isset($_POST['munisID']) ? $mysqli->real_escape_string($_POST['munisID']) : false;
        $userID = isset($_POST['userID']) ? $mysqli->real_escape_string($_POST['userID']) : false;
        $address = isset($_POST['address']) ? $mysqli->real_escape_string($_POST['address']) : false;
        $hphone = isset($_POST['hphone']) ? $mysqli->real_escape_string($_POST['hphone']) : false;
        $cphone = isset($_POST['cphone']) ? $mysqli->real_escape_string($_POST['cphone']) : false;
        $wphone = isset($_POST['wphone']) ? $mysqli->real_escape_string($_POST['wphone']) : false;
        $dob = isset($_POST['dob']) ? $mysqli->real_escape_string($_POST['dob']) : false;
        $emergency = isset($_POST['emergency']) ? $mysqli->real_escape_string($_POST['emergency']) : false;
        $postAminLvl = isset($_POST['adminLvl']) ? $mysqli->real_escape_string($_POST['adminLvl']) : "0";
        $email = isset($_POST['empEmail']) ? $mysqli->real_escape_string($_POST['empEmail']) : false;
        
        if($config->adminLvl >= 50){
            $myq = "UPDATE `EMPLOYEE` SET 
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
                HOMEPH = '".$hphone."',
                CELLPH = '".$cphone."',
                WORKPH = '".$wphone."',
                EMAIL = '".$email."',
                DOB = '".Date('Y-m-d', strtotime($dob))."',
                EMERGCON = '".$emergency."',
                ADMINLVL = '".$postAminLvl."',
                IS_VERIFY = 1,
                AUDITID = '".$_SESSION['userIDnum']."',
                AUDIT_TIME = NOW(),
                AUDIT_IP = INET_ATON('".$_SERVER['REMOTE_ADDR']."')
                WHERE IDNUM = '".$userID."'";
        }
        else{
            $myq = "UPDATE `EMPLOYEE` SET 
                `MUNIS` = '".$munisID."',
                `LNAME` = '".$lname."',
                `FNAME` = '".$fname."',
                `GRADE` = '".$rankID."',
                `DIVISIONID` = '".$divisionID."',
                `SUPV` = '".$supvID."',
                `ASSIGN` = '".$assignID."',                                                           
                AUDITID = '".$_SESSION['userIDnum']."',
                AUDIT_TIME = NOW(),
                AUDIT_IP = INET_ATON('".$_SERVER['REMOTE_ADDR']."')
                WHERE IDNUM = '".$userID."'";
        }
            //popUpMessage($myq); //DEBUG
        //Perform SQL Query
       
        $result = $mysqli->query($myq);
        
        //show SQL error msg if query failed
        if (!SQLerrorCatch($mysqli, $result)) 
            echo "Successfully Updated Profile<br />";
        else{
            echo "Did not Update<br />";
        }
        $fromVerify = strcmp(isset($_POST['formName']) ? $_POST['formName'] : false, "userVerify") == 0 ? true : false;
        if($fromVerify)
            echo '<form method="POST"><input type="button" name="backToVerify" value="Back To Users To Verify List" onClick="this.form.action='."'?userVerify=true'".';this.form.submit()" /></form>';
    }   
    else{
        //Get stored information (first view)
        $sql_user = strtoupper($mysqli->real_escape_string($foundUserID));
        $myq = "SELECT * FROM EMPLOYEE WHERE IDNUM=".$foundUserID;
        
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
        $hphone = $resultAssoc['HOMEPH'];
        $cphone = $resultAssoc['CELLPH'];
        $wphone = $resultAssoc['WORKPH'];
        $email = $resultAssoc['EMAIL'];
        $dob = $resultAssoc['DOB'];
        $emergency = $resultAssoc['EMERGCON'];
        $adminLvl = $resultAssoc['ADMINLVL'];
        
    
    $username = strtoupper($_SESSION['userName']);
    ?>
        <form name="update" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <input type="hidden" name="formName" value="updateProfile" />
        <?php 
        //how does this double overloaded call work!!!!
        $fromVerify = strcmp(isset($_POST['formName']) ? $_POST['formName'] : false, "userVerify") == 0 ? true : false;
        if($fromVerify)
            echo '<input type="button" name="backToVerify" value="Back To Users To Verify List" onClick="this.form.action='."'?userVerify=true'".';this.form.submit()" />
                    <input type="hidden" name="formName" value="userVerify" />';
        ?>
        </div><div align="center" class="login">
            <table>
        <?php if ($_SESSION['admin'] >= 25) { 
                  echo "<tr><td>User: </td><td>";
                if($totalRows > 0) { 
                    echo $lname. ', '. $fname. ' ';
                    echo '<input type="hidden" name="userID" value="'.$foundUserID.'" />';
                    echo '<input type="hidden" name="totalRows" value="1" />';
                    echo '<input type="hidden" name="foundUserName1" value="'.$foundUserID.'" />';
                }
                else{
                    echo $_SESSION['userName']."  ";
                    echo '<input type="hidden" name="userID" value="'.$_SESSION['userIDnum'].'" />';
                }
                displayUserLookup($config);
                echo '<input type="hidden" name="searchReserves" value="false" />';
                echo '</td></tr>';
                 
                }
                  else  {  ?>                         
            <h3>Username: <?php echo $username; ?></h3>
            <input type="hidden" name="userID" value="<?php echo $foundUserID; ?>" />
            <?php } ?>
                
                    <tr><td>First Name: </td><td><input name="fname" type="text" <?php if(!$fname) showInputBoxError(); else echo 'value="'.$fname.'"'; ?> /></td></tr>
                    <tr><td>Last Name: </td><td><input name="lname" type="text" <?php if(!$lname) showInputBoxError(); else echo 'value="'.$lname.'"'; ?> /></td></tr>
                    <?php 
                    echo "<tr><td>Division:</td><td>"; displayDivisionID("divisionID", $divisionID); echo "</td></tr>";
                    echo "<tr><td>Supervisor:</td><td>"; displaySUPVDropDown("supvID", $supvID); echo "</td></tr>";
                    
                    //Payrate dependent
                    if($config->adminLvl >= 50){
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
                    
                    if($config->adminLvl >= 50){
                        ?>
                        <tr><td>Hire Date: </td><td><?php displayDateSelect("hireDate", "date_1", $hireDate, $required=true); ?></td></tr>
                        <tr><td>Radio Number: </td><td><input name="radioID" type="text" <?php if(!$radioID) showInputBoxError(); else echo 'value="'.$radioID.'"'; ?> /></td></tr>
                        <tr><td >Address: </td><td><textarea rows="3" cols="40" name="address" <?php if(!$address) showInputBoxError(); ?> ><?php echo $address; ?></textarea></td></tr>
                        <tr><td>Home Phone: </td><td><input name="hphone" type="text" <?php if(!$hphone && !$cphone && !$wphone) showInputBoxError(); else echo 'value="'.$hphone.'"'; ?> /></td></tr>
                        <tr><td>Cell Phone: </td><td><input name="cphone" type="text" <?php if(!$hphone && !$cphone && !$wphone) showInputBoxError(); else echo 'value="'.$cphone.'"'; ?> /></td></tr>
                        <tr><td>Work Phone: </td><td><input name="wphone" type="text" <?php if(!$hphone && !$cphone && !$wphone) showInputBoxError(); else echo 'value="'.$wphone.'"'; ?> /></td></tr>
                        <tr><td>Email: </td><td><input size="40" name="empEmail" type="text" <?php if(!$email) showInputBoxError(); else echo 'value="'.$email.'"'; ?> /></td></tr>
                        <tr><td>Date of Birth: </td><td><?php displayDateSelect("dob", "date_2", $dob, $required=true); ?></td></tr>

                        <tr><td>Emergency Contact: </td><td><textarea rows="2" cols="40" name="emergency" <?php if(!$emergency) showInputBoxError(); ?> ><?php echo $emergency; ?></textarea></td></tr>
                        <tr><td>Admin Level:</td><td><?php selectAdminLevel($config, $adminLvl); ?></td></tr>
                        <tr><td></td><td><input type="submit" name="updateBtn" value="Update Profile" /></td></tr>
                        <?php
                    }
                    else{
                      ?><tr><td>Hire Date: </td><td><?php echo $hireDate; ?></td></tr>
                        <tr><td>Radio Number: </td><td> <?php echo $radioID; ?> </td></tr>
                        <tr><td>Address: </td><td> <?php echo $address; ?> </td></tr>
                        <tr><td>Home Phone: </td><td> <?php echo $hphone; ?> </td></tr>
                        <tr><td>Cell Phone: </td><td> <?php echo $cphone; ?> </td></tr>
                        <tr><td>Work Phone: </td><td> <?php echo $wphone; ?> </td></tr>
                         <tr><td>Email: </td><td><?php echo $email; ?></td></tr>
                        <tr><td>Date of Birth: </td><td><?php echo $dob; ?></td></tr>

                        <tr><td>Emergency Contact: </td><td> <?php echo $emergency; ?> </td></tr> 

                        <tr><td></td><td><?php if($_SESSION['userIDnum']==$foundUserID || $config->adminLvl >= 25) echo '<input type="submit" name="updateBtn" value="Update Profile" />'; ?></td></tr>
                        <?php 
                    }
                    ?>
                </table>
            </div><div class="clear"></div>
        </form>
        <div class="divider"></div>
        
    <?php
    }
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
function displayDivisionID($selectName, $selected=false, $showAllOpt=false){
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
    if($showAllOpt){
        echo '<option value="1"';
        if (strcmp($selected, "1") == 0)
            echo ' SELECTED ';
        echo '>Everyone</option>';
        
        echo '<option value="2"';
        if (strcmp($selected, "2") == 0)
            echo ' SELECTED ';
        echo '>ALL</option>';
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
function displayDateSelect($inputName, $id, $oldValue = false, $required = false,$today=false,$submitForm=false){
    ?>
    <link type="text/css" href="bin/jQuery/css/smoothness/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <script type="text/javascript" src="bin/jQuery/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="bin/jQuery/js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript">
        $(function() {
            // Datepicker
            $('.datepicker').datepicker({
                    inline: true,
                    <?php
                    if($submitForm){
                        echo 'onSelect: function(){
                            var _form = document.getElementById("secLog");
                            var _goBtn = document.getElementById("goBtn");
                            _goBtn.click();
                            _form.submit();
                            
                            },';
                    }
                    ?>
                    changeYear: true,
                    yearRange: "-100:+100"
            });
        });
    </script>
    <input name="<?php echo $inputName ?>" type="text" class="datepicker" <?php 
        if($today && !$oldValue) 
            echo 'value="'. date('m/d/Y', strtotime('today')).'"'; 
        ?> id="<?php echo $id; ?>" <?php 
            if(!$oldValue){ 
                if($required) 
                    showInputBoxError(); 
            } 
            else 
                echo 'value="'.$oldValue.'"';
//            if($submitForm)
//                echo ' onchange="submitSecLog();this.form.submit()"';
            echo ' />';
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
    if (!isValidUser($config)){
        $error = '0';
        $noPass = false;
        $noUser = false;
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        if (isset($_POST['submitBtn'])){

            $domain = isset($_POST['domainOPT']) ? $_POST['domainOPT'] : 'SHERIFF';

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
                    $error = loginLDAPUser($username,$password, $config, $domain);
            }
        } 
        if ($error != '') {
            //First time seeing this screen or Invalid User Input
            ?>
            <div class="thumbnail"><img src="style/icon4.gif" alt="" /></div>
            <h3><?php echo $config->getTitle(); ?></h3>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="loginform">
                <table width="50%">
                <tr><td>Username:</td><td> <input class="text" name="username" type="text" 
                                        <?php echo "value='".$username."'"; if ($noUser) echo "style='background:#FFFFFF;border:1px solid #FF0000'"; ?> /></td></tr>
                <tr><td>Password:</td><td> <input class="text" name="password" type="password" 
                                        <?php if (isset($_POST['submitBtn'])) echo "style=\"background:#FFFFFF;border:1px solid #FF0000;\""; ?>/></td></tr>
                <tr><td>Domain:</td><td> 
                        <select name="domainOPT">
                            <option value="MAHONINGCO">MAHONING COUNTY</option>
                        </select> 
                                        </td></tr>
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
    $results = "User was not disabled!";
    if($config->adminLvl >= 50){
        $mysqli = $config->mysqli;
        $myq = "UPDATE `PAYROLL`.`EMPLOYEE` 
            SET `ACTIVE` = '0'
            WHERE EMPLOYEE.IDNUM = '".$userID."'";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result);
        $results = "User has been disabled!";
    }
    return $results;
}
function getUserID($config, $userName){
    $mysqli = $config->mysqli;
    $myq = "SELECT *
        FROM `EMPLOYEE`
        WHERE `ID` =  '".strtoupper($userName)."'";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $row = $result->fetch_assoc();
    
    return $row['IDNUM'];
}
function displayUserVerify($config){
    $mysqli = $config->mysqli;
    $myq = "SELECT E.IDNUM, E.ID, E.LNAME, E.FNAME, E.RADIO, E.SUPV, E.HOMEPH, E.CELLPH, E.WORKPH, E.DOB, E.EMERGCON, D.DESCR
        FROM `EMPLOYEE` E
        LEFT JOIN DIVISION AS D USING (DIVISIONID)
        LEFT JOIN EMPLOYEE AS SUP ON E.IDNUM=SUP.IDNUM
        WHERE E.IS_VERIFY =  0
        ORDER BY E.LNAME";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    
    if($config->adminLvl >= 50){
        $theTable = array(array());
        $x=0;
        $formName = "userVerify";
        echo '<h3>Verify Users</h3><form name="'.$formName.'" method="POST">';
        
        $theTable[$x][0] = "Edit";
        $theTable[$x][1] = "Deputy";
        $theTable[$x][2] = "Radio #";
        $theTable[$x][3] = "Division";
        $theTable[$x][4] = "Supervisor";
        $theTable[$x][5] = "Home Phone";
        $theTable[$x][6] = "Cell Phone";
        $theTable[$x][7] = "Work Phone";
        $theTable[$x][8] = "Date Of Birth";
        $theTable[$x][9] = "Emergency Contact";

        while($row = $result->fetch_assoc()) {
            $x++;
            $theTable[$x][0] = '<input type="hidden" name="foundUserID'.$x.'" value= "'.$row['IDNUM'].'" />
                '.$x.'<input type="radio" name="foundUser'.$x.'" onClick="this.form.action='."'?updateProfile=true'".';this.form.submit()" />';
            $theTable[$x][1] = $row['LNAME'].", ".$row['FNAME'];
            $theTable[$x][2] = $row['RADIO'];
            $theTable[$x][3] = $row['DESCR'];
            $theTable[$x][4] = $row['SUPV'];
            $theTable[$x][5] = $row['HOMEPH'];
            $theTable[$x][6] = $row['CELLPH'];
            $theTable[$x][7] = $row['WORKPH'];
            $theTable[$x][8] = $row['DOB'];
            $theTable[$x][9] = $row['EMERGCON'];  
        }
       
        showSortableTable($theTable, 1);
        echo '<input type="hidden" name="formName" value="'.$formName.'" /><a href="javascript:window.print()">Print</a></form>';

    }
    else{
        echo 'Unauthorized Access'; 
    }
}
function selectAdminLevel($config, $adminLvl="0"){
    $mysqli = $config->mysqli;
    $myq = "SELECT * FROM `ADMINLVL` ORDER BY IDNUM";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    
    echo '<select name="adminLvl">';
    while($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['IDNUM'].'"';
        if(strcmp($adminLvl, $row['IDNUM'])==0){
            echo ' selected="selected"';
        }
        echo '>'.$row['DESCR'].'</option>';
    }
    echo '</select>';
}
?>
