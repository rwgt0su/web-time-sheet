<?php

require_once('bin/common.php');


$error = '0';
$noPass = false;
$noUser = false;
if (isset($_POST['submitBtn'])){
	// Get user input
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
		$error = loginUser($username,$password);
	}
}


?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title><?php echo $config->getTitle(); ?></title>
   <link href="style/style.css" rel="stylesheet" type="text/css" />
	<LINK REL="SHORTCUT ICON" HREF="../favicon.ico">
</head>
<body>
    <div id="main">
	<div class="caption"><?php echo $config->getTitle(); ?></div>
	  <div id="icon3">&nbsp;</div>
	<?php 
	if ((isset($_SESSION['validUser'])) && ($_SESSION['validUser'] == true)){
		$error = '';
		displayLogin();
	}
	
	displayMenu($config); 
	?>
	
<?php	
 if ($error != '') {
//First time seeing this screen or Invalid User Input
?>

      <div class="caption"><?php echo $config->getTitle(); ?></div>
      <div id="icon">&nbsp;</div>
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="loginform">
        <table width="100%">
          <tr><td>Username:</td><td> <input class="text" name="username" type="text" 
				<?php echo "value=\"$username\""; if ($noUser) echo "style=\"background:#FFFFFF;border:1px solid #FF0000;\""; ?> /></td></tr>
          <tr><td>Password:</td><td> <input class="text" name="password" type="password" 
				<?php if (isset($_POST['submitBtn'])) echo "style=\"background:#FFFFFF;border:1px solid #FF0000;\""; ?>/></td></tr>
          <tr><td colspan="2" align="center"><input class="text" type="submit" name="submitBtn" value="Login" /></td></tr>
        </table>
      </form>

      &nbsp
	<?php
	if (isset($_POST['submitBtn'])){
	//User had input but had an error.  Display the error
	?>
	  <div class="caption">Error Message:</div>
	  <div id="icon2">&nbsp;</div>
	  <div id="result">
		<br /><?php echo $error; ?><br /><br />
	</div>
	<?php
	}
}
?>
	<div id="source"><?php echo $config->getVersion(); ?></div>
    </div>
</body>
</html>