<?php    
function displayUserMenu($config){	
	if (isset($_GET['ChangeBtn'])){
		displayPassChange(false, false);
	}
	else if (isset($_GET['AddUserBtn'])){
		displayPassChange(true, true);
	}
	else if (isset($_GET['EditUserBtn'])){
		displayPassChange(true, false);
	}
	else if (isset($_GET['DelUserBtn'])){
		displayDelUser();
	} 
	else{ ?>
		<div id="icon">&nbsp;</div>
		<h3>User Management Menu</h3>
		<a href="<?php echo $_SERVER['REQUEST_URI']; ?>&ChangeBtn=true">Change Your Password</a><br />
		<?php 
		if($config->adminLvl >= 75){ 
			?>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?usermenu=true&AddUserBtn=true">Add Users</a><br />
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?usermenu=true&EditUserBtn=true">Edit Users</a><br />
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?usermenu=true&DelUserBtn=true">Remove User</a><br />
		<?php 
		}
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
		if($useAdmin && !$addUser)
            $error .= delUser($username)."<br />";
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
        <form action="<?php echo $_SERVER['PHP_SELF']; if($addUser) echo "?AddUserBtn=true"; else if ($useAdmin) echo "EditUserBtn"; else echo "?ChangeBtn=true"; ?>" method="post" name="registerform">
		<table width="100%"><a href="<?php echo$_SERVER['PHP_SELF']; ?>">Back</a><br /><br /><?php
		
		if($useAdmin && $addUser){
            ?>
            <tr><td>Username:</td><td> <input class="text" name="username" type="text" value="<?php if(!$addUser) echo $_SESSION['userName']; ?>"  />
			<?php 
		}
		if($_SESSION['admin'] >= $adminLvl && $useAdmin && !$addUser){
			showAllUsers();        
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
            if ($error == '' && !$useAdmin && !$addUser) {
                    echo " Password was successfully changed!<br/><br/>";
					//history('Changed Password');
                    echo ' <a href="/">Home</a>';

            }
			else if ($error == '' && $useAdmin && $addUser) {
				echo "User Added!!!<br/><br/>";
				?><a href="<?php echo$_SERVER['PHP_SELF']; ?>?AddUserBtn=true">Add Another User</a><br /><?php
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
function displayDelUser(){
	checkAdmin();
	$error = '';
	
    if (isset($_POST['submitBtn'])){
            // Get user input
            $username  = isset($_POST['username']) ? $_POST['username'] : '';
            $error = delUser($username);
    }
    if ((!isset($_POST['submitBtn'])) || ($error != '')) {?>
        <div class="caption">Delete User</div>
        <div id="icon">&nbsp;</div>
        <div id="results"><a href="<?php echo $_SERVER['PHP_SELF']; ?>">Back</a></div>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?DelUserBtn=true" method="post" name="delform">
            <table width="100%"><?php
		if($_SESSION['admin'] == 1){
			showAllUsers();   
		}
		?>
            </td></tr>
            <tr><td colspan="2" align="center"><input class="text" type="submit" name="submitBtn" value="Delete User" /></td></tr>
            </table>  
        </form>

    <?php 
    }   
	if (isset($_POST['submitBtn'])){

    ?>
        <div class="caption">Deletion result:</div>
        <div id="icon2">&nbsp;</div>
        <div id="result">
            <table width="100%"><tr><td><br/>
    <?php
            if ($error == '') {
                    echo $username.", Was Successfully Deleted!<br/><br/>";
                            //history($username.", was deleted from the system");
                    echo ' <a href="/">Home</a>';
            }
            else echo $error;
    ?>
				<br/><br/><br/></td></tr>
			</table>
		</div>
    <?php            
	}
}
function showAllUsers(){
	?>
	<tr><td>Username:</td><td><SELECT name="username">
	<?php	
	//Display all users for drop down scroll selection
	//Get all users from DB
	echo '<OPTION value=\"Username\">Username</OPTION>';
	
	echo ' </SELECT>';
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
