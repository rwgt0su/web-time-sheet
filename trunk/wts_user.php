<?php
	require_once('bin/common.php');
	checkUser();
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title><?php echo $config->getTitle(); ?></title>
   <link href="style/style.css" rel="stylesheet" type="text/css" />
</head>

	<div id="main">
	<div class="caption"><?php echo $config->getTitle(); ?></div>
	<?php 
	//displayLogin();
	
	displayUserMenu($config);
	?>	  
	<div id="source"><?php echo $config->getVersion(); ?></div>
	</div>     
</body>
</html>
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
		<div id="result">User Management Menu<br /><br />
		<a href="<?php echo $_SERVER['PHP_SELF']; ?>?ChangeBtn=true">Change Your Password</a><br />
		<?php 
		if($config->getAdmin() >= 75){ 
			?>
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?AddUserBtn=true">Add Users</a><br />
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?EditUserBtn=true">Edit Users</a><br />
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>?DelUserBtn=true">Remove User</a><br />
		<?php 
		}
	}
}	
?>