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

require_once 'bin/db_sqli.php';

//User Based Files
require_once 'bin/db_config.php';
require_once 'bin/db_usr_menu.php';
require_once 'bin/usr_functions.php';
require_once 'bin/usr_display.php';
    
    //Class Declarations for User Based Control
    $config = new Config();
    $config->setAdmin(isset($_SESSION['admin']) ? $_SESSION['admin'] : -1);

//Content Based Files
require_once 'bin/wts_content.php';
require_once 'bin/wts_content_class.php';

    //Class Declarations for Content Based Control
    $wts_content = new wts_content();

function popUpMessage($message){
	echo '<script type="text/javascript">';
	echo "alert(\"".$message."\")</script>";
}
function isValidUser(){
	if ((!isset($_SESSION['validUser'])) || ($_SESSION['validUser'] != true)){
            return false;
	}
        else
            return true;
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

function showDynamicTable($tableName, $rowArray){
    ?>
    <script language="JavaScript" type="text/javascript">
    function addRowToTable()
    {
    var tbl = document.getElementById('<?php echo $tableName; ?>');
    var lastRow = tbl.rows.length;
    // if there's no header row in the table, then iteration = lastRow + 1
    var iteration = lastRow;
    var row = tbl.insertRow(lastRow);

    // left cell
    var cellLeft = row.insertCell(0);
    var textNode = document.createTextNode(iteration);
    //cellLeft.appendChild(textNode);

    // right cell
    //var cellRight = row.insertCell(1);
    
    <?php
    //New Row to Add based on passed values
    $columnCount = 0;
    foreach ($rowArray as $column){
        echo "var newCode".$columnCount." = ";
        echo '"<input type=\"text\"size=\"10\"name=\"'.$column.$columnCount.'\" value=\"\" />"';        
            echo ";\n";
        $columnCount = $columnCount + 1;
    }
    for ($i = 0; $i < $columnCount; $i++){
        echo "\n row.insertCell(".$i.").innerHTML = newCode".$i.";";
    }
    ?>
    }
    function keyPressTest(e, obj)
    {
    var validateChkb = document.getElementById('chkValidateOnKeyPress');
    if (validateChkb.checked) {
        var displayObj = document.getElementById('spanOutput');
        var key;
        if(window.event) {
        key = window.event.keyCode; 
        }
        else if(e.which) {
        key = e.which;
        }
        var objId;
        if (obj != null) {
        objId = obj.id;
        } else {
        objId = this.id;
        }
        displayObj.innerHTML = objId + ' : ' + String.fromCharCode(key);
    }
    }
    function removeRowFromTable()
    {
    var r=confirm("Are You Sure You Want To Remove The Last Row?");
    if (r==true)
        {
        var tbl = document.getElementById('<?php echo $tableName; ?>');
            var lastRow = tbl.rows.length;
            if (lastRow > 2) tbl.deleteRow(lastRow - 1);
        }
    else
        {
        }

    }
    function openInNewWindow(frm)
    {
    // open a blank window
    var aWindow = window.open('', 'TableAddRowNewWindow',
    'scrollbars=yes,menubar=yes,resizable=yes,toolbar=no,width=400,height=400');

    // set the target to the blank window
    frm.target = 'TableAddRowNewWindow';

    // submit
    frm.submit();
    }
    function validateRow(frm)
    {
    var chkb = document.getElementById('chkValidate');
    if (chkb.checked) {
        var tbl = document.getElementById('tblSample');
        var lastRow = tbl.rows.length - 1;
        var i;
        for (i=1; i<=lastRow; i++) {
        var aRow = document.getElementById('txtRow' + i);
        if (aRow.value.length <= 0) {
            alert('Row ' + i + ' is empty');
            return;
        }
        }
    }
    openInNewWindow(frm);
    }
    </script>
    <form>
    <input type="button" value="Add" onclick="addRowToTable();" />
    <input type="button" value="Remove" onclick="removeRowFromTable();" />
    </form>
    <?php
    
}

?>
