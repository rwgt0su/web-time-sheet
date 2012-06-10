<?php
	require_once('bin/common.php');
	logoutUser();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title><?php echo $config->getTitle(); ?></title>
	<?php echo '<meta http-equiv="refresh" content="3;url=/"/>'; ?>
   <link href="style/style.css" rel="stylesheet" type="text/css" />
	<LINK REL="SHORTCUT ICON" HREF="../favicon.ico">
</head>
<body>
    <div id="main">
	<div class="caption"></div><?php echo $config->getTitle(); ?>
      <div id="icon">&nbsp;</div>
	  <div id="result">User Account Logged Out!</div>
	  	<div id="source"><?php echo $config->getVersion(); ?></div>
    </div>
</body>
</html>
	
