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