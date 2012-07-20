<?php    
function displayUserMenu($config){	
	if (isset($_GET['ChangeBtn'])){
            if(isValidUser())
		displayPassChange(false, false);
	}
	else if (isset($_GET['AddUserBtn'])){
            if(isValidUser())
		displayPassChange(true, true);
	}
	else if (isset($_GET['EditUserBtn'])){
            if(isValidUser())
		displayPassChange(true, false);
	}
	else if (isset($_GET['DelUserBtn'])){
            if(isValidUser())
		displayDelUser($config);
	}
        else if (isset($_GET['DispUsers'])){
		displayUsers();
        }
	else{ ?>
		<div align="center">&nbsp;
		<h3>User Management Menu</h3>
		<a href="<?php echo $_SERVER['REQUEST_URI']; ?>&ChangeBtn=true">Change Your Password</a><br />
		<?php 
		if($config->adminLvl >= 50){ 
			?>
			<a href="<?php echo $_SERVER['REQUEST_URI']; ?>&AddUserBtn=true">Add Users</a><br />
			<a href="<?php echo $_SERVER['REQUEST_URI']; ?>&EditUserBtn=true">Change User Password or Admin Level</a><br />
			<a href="<?php echo $_SERVER['REQUEST_URI']; ?>&DelUserBtn=true">Remove User</a><br />
                        <a href="<?php echo $_SERVER['REQUEST_URI']; ?>&DispUsers=true">Display/Edit All Users</a><br />
		<?php 
		}
                echo '</div>';
	}
}	
function displayPassChange($useAdmin, $addUser){
	$error = '';
	$adminLvl = 75;
    if (isset($_POST['submitBtn'])){
		// Get user input
		$username  = isset($_POST['username']) ? $_POST['username'] : '';
		$password1 = isset($_POST['password1']) ? $_POST['password1'] : '';
		$password2 = isset($_POST['password2']) ? $_POST['password2'] : '';
		$admin = isset($_POST['admin']) ? $_POST['admin'] : '';
		if(!$addUser)
                    $error .= resetPass($username, $password1, $password2, $admin);
                else
		$error .= registerUser($username,$password1,$password2, $admin);
    }
    
    if ((!isset($_POST['submitBtn'])) || ($error != '')) {
        if(!$addUser){
            echo '<div class="caption">Change Password</div>';
        }
        else{
            echo '<div class="caption">Add User</div>';
        }
        ?>
        <div id="icon">&nbsp;</div>
        <div id="results"></div>
        <form action="<?php echo $_SERVER['REQUEST_URI'];  ?>" method="post" name="registerform">
		<table width="100%"><a href="<?php echo$_SERVER['PHP_SELF']; ?>">Back</a><br /><br /><?php
		
		if($useAdmin && $addUser){
            ?>
            <tr><td>Username:</td><td> <input class="text" name="username" type="text" value="<?php if(!$addUser) echo $_SESSION['userName']; ?>"  />
			<?php 
		}
		if($_SESSION['admin'] >= $adminLvl && $useAdmin && !$addUser){
                     echo '<tr><td>Username:</td><td><SELECT name="username">';
                     showAllUsers();      
                     echo ' </SELECT>';
		}
		if(!$useAdmin && !$addUser){
			?>
			<input name="username" type="hidden" value="<?php echo $_SESSION['userName']; ?>"  />
			<input name="admin" type="hidden" value="<?php echo $_SESSION['admin']; ?>"  />
			<tr><td>Username:</td><td><?php echo $_SESSION['userName']; ?>
			<?php
		}
		?>
            </td></tr>
            <tr><td>Password:</td><td> <input class="text" name="password1" type="password" /></td></tr>
            <tr><td>Confirm password:</td><td> <input class="text" name="password2" type="password" /></td></tr><?php
		if($_SESSION['admin'] >= $adminLvl  && $useAdmin){ ?>
			<tr><td>Admin Level:</td><td> 
			<?php showAdminLvls(); ?>
			</td></tr>
				<?php 
		} 
		?>		
			<tr><td colspan="2" align="center">
				<input class="text" type="submit" name="submitBtn" value="<?php if(!$addUser) echo "Change Password"; else echo "Add User"; ?>" />
			</td></tr>
		</table>  
		</form>
    <?php   
	}
	if (isset($_POST['submitBtn'])){
                
    ?>
        <div class="caption">Result:</div>
        <div id="icon2">&nbsp;</div>
        <div id="result">
            <table width="100%"><tr><td><br/>
    <?php
            if ($error == '' && !$addUser) {
                    echo " Password was successfully changed!<br/><br/>";
					//history('Changed Password');
            }
            else if ($error == '' && $useAdmin && $addUser) {
                    echo "User Added!!!<br/><br/>";
                    ?><a href="<?php echo $_SERVER['REQUEST_URI']; ?>">Add Another User</a><br /><?php
                    //history('Changed Password');
                    echo ' <a href="/">Home</a>';
            }
            else 
                echo $error;

    ?>
                    <br/><br/><br/></td></tr></table>
            </div>
                    

    <?php            
	}
}        
function displayDelUser($config){
    if($config->adminLvl >= 75){
	$error = '';
	
        if (isset($_POST['removeBtn'])){
                // Get user input
                $username  = isset($_POST['user_to_Delete']) ? $_POST['user_to_Delete'] : '';
                $error = delUser($username);
        }
        if (isset($_POST['disableBtn'])){
                // Get user input
                $username  = isset($_POST['user_to_Delete']) ? $_POST['user_to_Delete'] : '';
                $userID = getUserID($config, $username);
                $error = disableUser($config, $userID);
        }
        if ((!isset($_POST['submitBtn'])) || ($error != '')) {?>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?usermenu=true">Back</a>
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>?DelUserBtn=true" method="post" name="delform">
                <table width="100%"><?php

                        echo '<tr><td align="center"><select name="user_to_Delete">';
                            showAllUsers(); 
                            echo '</select>';
                    ?>
                </td></tr>
                <tr><td colspan="2" align="center"><input class="text" type="submit" name="removeBtn" value="Delete User" />
                    <input class="text" type="submit" name="disableBtn" value="Disable User" /></td></tr>
                </table>  
            </form>

        <?php 
    }   
	if (isset($_POST['removeBtn']) || isset($_POST['disableBtn'])){
            if(isset($_POST['disableBtn']))
                echo '<h2>Disable Results</h2>';
            else
                echo '<h2>Deletion result:</h2>';

    ?>
        <div id="icon2">&nbsp;</div>
        <div id="result">
            <table width="100%"><tr><td><br/>
    <?php
         echo $error;
    ?>
				<br/><br/><br/></td></tr>
			</table>
		</div>
    <?php            
	}
    }
}
function showAllUsers(){
	?>
	
	<?php	
	//Display all users for drop down scroll selection
	//Get all users from DB
        $mysqli = connectToSQL();
        $myq = "SELECT ID FROM EMPLOYEE";
        $result = $mysqli->query($myq);
        if (!$result) 
            throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
            
        while($row = $result->fetch_assoc()) {
	echo "<OPTION value='" . $row['ID'] . "'>" . $row['ID'] . "</OPTION>";
        }
	
	
}
function showAdminLvls(){
	echo '<select name="admin" class="text">';
	echo '<option value="0">User</option>';
	echo '<option value="25">Supervisors</option>';
	echo '<option value="50">Human Resource Managers</option>';
	echo '<option value="99">Sheriff</option>';
	echo '<option value="100">Full Admin</option>';
}
?>
                    
<?php
function displayUsers(){   
/*
 * Shows all users and gives the option to edit any fields
 */
    $admin = $_SESSION['admin'];
    
    if($admin >= 50 && isValidUser()) { 
        $mysqli = connectToSQL();
        $myq = "SELECT *
                FROM EMPLOYEE";
        $result = $mysqli->query($myq);
        if (!$result) 
            throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");

      //build table
        resultTable($mysqli, $result);

    //write any updates to DB when Save is pressed
    if (isset($_POST['saveBtn'])) { //saveBtn created in resultTable()
        $result = $mysqli->query($myq);
        
        $i = 0;
        $fieldNameArray = array();
        $values = array();
        
        while($finfo = mysqli_fetch_field($result)) {
            $tableName = $finfo->orgtable;
            $fieldNameArray[$i] = $finfo->orgname;
            $values["$fieldNameArray[$i]"] = $fieldNameArray[$i] ."="."'". $mysqli->real_escape_string($_POST["$fieldNameArray[$i]"])."'";
            $i++;
        }
        
        //turn the array into comma seperated values
        $csvValues = implode(',' , $values);
        
        $updateQuery = "UPDATE ".$tableName." SET ".$csvValues." 
            WHERE " .$values['ID'];
        
       echo "<br>" . $updateQuery;
        $updateResult = $mysqli->query($updateQuery);

        if (!$updateResult) 
            throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
        } 

    } 
 
}
?>
