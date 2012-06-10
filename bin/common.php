<?php
/*
*************************************************************
*************************************************************
*************************************************************
**********                                         **********
**********   Mahoning County Sheriff's Office      **********
**********           Web Time Sheets               **********
**********                                         **********
*************************************************************
*************************************************************
**********                                         **********
**********       Developed by: Daniel Lumpp        **********
**********                                         **********
*************************************************************
*************************************************************
**********                                         **********
**********        @2012 All Rights Reserved.       **********
**********         Do Not Copy, Modify, or         **********
**********         Reuse this code without         **********
**********         expressed written consent       **********
*********     of the developer of this product    **********
**********                                         **********
*************************************************************
*************************************************************
 */
error_reporting(E_ALL);
ini_set('display_errors', True);

session_save_path('/var/www/sessions');
session_start();

require_once 'bin/usr_functions.php';
require_once 'bin/usr_display.php';
require_once 'bin/db_sqli.php';
require_once 'bin/db_config.php';
require_once 'bin/db_usr_menu.php';

$config = new Config();
$config->setAdmin(isset($_SESSION['admin']) ? $_SESSION['admin'] : '-1');

function popUpMessage($message){
	echo '<script type="text/javascript">';
	echo "alert(\"".$message."\")</script>";
}
function checkUser(){
	if ((!isset($_SESSION['validUser'])) || ($_SESSION['validUser'] != true)){
		header('Location: /');
	}
}
function checkAdmin(){
	if ((isset($_SESSION['admin'])) || ($_SESSION['admin'] >= 1)){
	}
	else{
		header('Location: /');
		}
}
function saltyHash($plain){
    //double hash a salt string, then append it to password, and md5 that
	$salt = "1Jpt34dM2s49kCy8";
        $salt = sha1(md5($salt));
	$cipher = md5($salt.$plain);
        return $cipher;		
}

?>
