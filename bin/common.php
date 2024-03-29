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

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
    session_save_path('C:\temp'); //windows server
else
    session_save_path('/var/www/sessions'); //linux server
session_start();


//Database related fucntions
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
    
//Announcements with Admin backend
require_once 'bin/wts_announce.php';

//Searching
require_once 'bin/wts_search.php';
require_once 'bin/WTS_Classes/Employee.php';

//Time gain/use (leave) request functions
require_once 'bin/time_request_functions.php';
include_once 'bin/Modules/TimeRequests/request_gui.php';

//WTS Logs
    //Secondary Employment Logs
    require_once 'bin/Modules/Logs/wts_sec_log.php';
    //Radio Checkout Logs
    require_once 'bin/Modules/Logs/wts_radio_log.php';
    //Key Checkout Logs
    require_once 'bin/Modules/Logs/wts_key_log.php';

//reserve employee management
require_once 'bin/wts_reserves.php';

//Alert functions
require_once 'bin/wts_alerts.php';

//Report functions
require_once 'bin/wts_reports.php';
require_once 'bin/wts_calendar.php';
require_once 'bin/wts_logging.php';

//ShiftBids
    //Available Shift Opening Bidding
    require_once 'bin/Modules/ShiftBids/wts_PosBids.php';
    require_once 'bin/Modules/ShiftBids/wts_positions.php';
    
function popUpMessage($message, $title="Message", $width = '300'){
	?>
    <link type="text/css" href="bin/jQuery/css/smoothness/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
    <script type="text/javascript" src="bin/jQuery/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="bin/jQuery/js/jquery-ui-1.8.21.custom.min.js"></script>
    <script type="text/javascript">
    $(document).ready(function() {
        $(function() {
           // Dialog
           $( "#dialog" ).dialog({
               title: '<?php echo $title ?>',
               width: <?php echo $width ?>,
            });
                  
//                buttons: {
//                        "Ok": function() {
//                                $(this).dialog("close");
//                        },
//                        "Cancel": function() {
//                                $(this).dialog("close");
//                        }
//                }
        });
    });
    </script>
    <div id="dialog" title="<?php echo $title; ?>">
	<?php echo $message; ?>
    </div>
    <?php
}
function isValidUser($config){
	if ((!isset($_SESSION['validUser'])) || ($_SESSION['validUser'] != true)){
                return false;
	}
        else{
            $timeout = 60; //minutes
            if ($_SESSION['timeout'] + ($timeout * 60) < time()) {
                //User has been inactive for 30 minutes
                popUpMessage("Your Session has Timed Out. Please log back in");
                logoutUser($config, "Session Timeout after ".$timeout." Minutes");
                return false;
            }
            else
                return true;
        }
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
function showTimeSelector($inputName, $input1, $input2, $required=true){

        echo '<select name="'.$inputName.'1" ';
        if($required)
            showInputBoxError();
        echo '>';
        echo '<option value=""></option>';

        for ($i = 00; $i <= 23; $i++) {
            if($i == $input1 && !empty($input1))
                echo '<option value="'.str_pad($i,2,"0",STR_PAD_LEFT).'" SELECTED >'.str_pad($i,2,"0",STR_PAD_LEFT).'</option>';
            else
                echo '<option value="'.str_pad($i,2,"0",STR_PAD_LEFT).'">'.str_pad($i,2,"0",STR_PAD_LEFT).'</option>';
        }
        echo '</select> : ';
        echo '<select name="'.$inputName.'2" ';
        if($required)
            showInputBoxError();
        echo '>';
        echo '<option value=""></option>';
        for ($i = 00; $i <= 59; $i++) {
             if($i == $input2 && !empty($input2))
                echo '<option value="'.str_pad($i,2,"0",STR_PAD_LEFT).'" SELECTED >'.str_pad($i,2,"0",STR_PAD_LEFT).'</option>';
            else
                echo '<option value="'.str_pad($i,2,"0",STR_PAD_LEFT).'">'.str_pad($i,2,"0",STR_PAD_LEFT).'</option>';
        }
        echo '</select>';
}
function showSortableTable($table, $rowToSort, $tableID = 'sorter', $rowsToSortNext = array(), $noSort = false){
    
    //two dim array is $table.  Place any html code within any cell
    //do not pass this with a form
    //creates hidden input for FormName and totalRows in the table.
        echo '
            <div id="wrapper">';
            
        $echo = '<table class="sortable" id="'.$tableID.'">
                <tr>';
        for($y=0;$y<sizeof($table[0]);$y++){
            $echo .= '<th>'.$table[0][$y].'</th>';
        }
        $echo .= '</tr>
            ';
        $x=1;
        for($x;$x<sizeof($table);$x++){
            $echo .= '<tr>';
            for($y=0;$y<sizeof($table[$x]);$y++){
                $pos = strpos($table[$x][$y], '<td');
                if($pos !== false){
                    $temps = explode('<td', $table[$x][$y]);
                    $i = 0;
                    $ftemp = '';
                    foreach ($temps as $temp){
                        if($i > 0)
                            $ftemp .= $temp;
                        $i++;
                    } 
                    $temps = explode('>', $ftemp);
                    $i = 0;
                    $ftemp = '';
                    foreach ($temps as $temp){
                        if($i > 0)
                            $ftemp .= $temp;
                        $i++;
                    } 
                    $echo .= '<td '.$temps[0].'>'.$ftemp.'</td>';
                }else{      
                    $echo .= '<td>'.$table[$x][$y].'</td>';
                }
            }
            $echo .= '</tr>
                ';
        }
        $echo = '<input type="hidden" name="totalRows" value="'.$x.'" />'.$echo;
        $echo .= '</table></div>';
        
        if(!$noSort){            
            $echo .= '<script type="text/javascript">
                    var '.$tableID.'=new table.sorter("'.$tableID.'");
                    '.$tableID.'.init("'.$tableID.'",'.$rowToSort.');';
            $count = count($rowsToSortNext);
            if($count > 0){
                for ($i = 0; $i < $count; $i++) {
                    $echo .= $tableID.'.work('.$rowsToSortNext[$i].');';
                }
            }
            $echo .= '</script>';
        }
        echo $echo;
}
function nslookup ($hostname, $timeout = 3) {
    $query = `nslookup -timeout=$timeout -retry=1 $hostname`;
   if(preg_match('/\nAddress: (.*)\n/', $query, $matches))
      return trim($matches[1]);
}
function moveTablesOnSelect($theTable, $selectedValues = array(array()), $rowToSort = 1, $selectOnly=false, $tableHeight=200){
    ?>
    <link rel="stylesheet" type="text/css" href="bin/jQuery/css/smoothness/jquery-ui-1.8.4.custom.css" id="link"/>
    <link rel="stylesheet" type="text/css" href="bin/jQuery/css/base.css" />			
    <script type="text/javascript" src="bin/jQuery/js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="bin/jQuery/js/jquery-ui-1.8.4.min.js"></script>
    <script type="text/javascript" src="bin/jQuery/js/highlighter/codehighlighter.js"></script>	
    <script type="text/javascript" src="bin/jQuery/js/highlighter/javascript.js"></script>			
    <script type="text/javascript" src="bin/jQuery/js/jquery.fixheadertable.min.js"></script>		

    <script type="text/javascript">  
        $(document).ready(function(){
	// Write on keyup event of keyword input element
	$("#kwd_search").keyup(function(){
		// When value of the input is not blank
		if( $(this).val() != "")
		{
			// Show only matching TR, hide rest of them
			$("#floatingTH tbody>tr").hide();
			$("#floatingTH td:contains-ci('" + $(this).val() + "')").parent("tr").show();
		}
		else
		{
			// When there is no input or clean again, show everything back
			$("#floatingTH tbody>tr").show();
		}                
            });
        });
        // jQuery expression for case-insensitive filter
        $.extend($.expr[":"], 
        {
            "contains-ci": function(elem, i, match, array) 
                {
                        return (elem.textContent || elem.innerText || $(elem).text() || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
                }
        });
        $(document).ready(function() {
            if(/msie|MSIE 6/.test(navigator.userAgent)){
                    $('#selectTable').fixheadertable({ 
                        caption : 'Selected Values', 
                        showhide : false,
                        startHide : true,
                        colratio : [50,100,100,250<?php if($selectOnly) echo ',100,100'; else echo ',200'; ?>], 
                        height : <?php echo $tableHeight; ?>, 
                        width : 700,
                        minWidthAuto   : true,
                        whiteSpace : 'normal',
                        zebra : true, 
                        sortable : true,
                        //sortedColId : 1, 
                        resizeCol : true,
                        pager : false,
                        rowsPerPage	 : 10, //default
                        //sortType : ['integer', 'string', 'string', 'string', 'string', 'date'],
                        sortedColId    : <?php echo $rowToSort; ?>,
                        dateFormat : 'm/d/Y'
                });
                alert('Your computer is using Internet Explorer Version 6!  \nFunctionality is limited in order to use this website');
            }
            else{            
                $('#selectTable').fixheadertable({ 
                        caption : 'Selected Values', 
                        showhide : false,
                        startHide : false,
                        colratio : [50,100,100,250<?php if($selectOnly) echo ',100,100'; else echo ',200'; ?>], 
                        height : <?php echo $tableHeight; ?>, 
                        width : 700,
                        minWidthAuto   : true,
                        whiteSpace : 'normal',
                        zebra : true, 
                        sortable : true,
                        //sortedColId : 1, 
                        resizeCol : true,
                        pager : false,
                        rowsPerPage	 : 10, //default
                        //sortType : ['integer', 'string', 'string', 'string', 'string', 'date'],
                        sortedColId    : <?php echo $rowToSort; ?>,
                        dateFormat : 'm/d/Y'
                });                
            }
            $('#floatingTH').fixheadertable({ 
                    caption : 'Choose From', 
                    showhide : true<?php if(!empty($selectedValues)) echo ',startHide : true'; ?>,
                    colratio : [100,100,100,200<?php if($selectOnly) echo ',100,100'; else echo ',200'; ?>], 
                    height : <?php echo $tableHeight; ?>, 
                    width : 700,
                    minWidthAuto   : true,
                    whiteSpace : 'normal',
                    zebra : true, 
                    sortable : true,
                    resizeCol : true,
                    pager : false,
                    rowsPerPage	 : 10, //default
                    //sortType : ['integer', 'string', 'string', 'string', 'string', 'date'],
                    sortedColId    : <?php echo $rowToSort; ?>,
                    dateFormat : 'm/d/Y'
            });       
              // When value of the input is not blank
		if( $("#kwd_search").val() != "")
		{
			// Show only matching TR, hide rest of them
			$("#floatingTH tbody>tr").hide();
			$("#floatingTH td:contains-ci('" + $("#kwd_search").val() + "')").parent("tr").show();
		} 
        });
    </script>
    <?php if(!$selectOnly){ ?>
        <script type="text/javascript">
            function Move(tr,cell)
            {
                if(/msie|MSIE 6/.test(navigator.userAgent)){
                    //alert('Your computer is using Internet Explorer Version 6!  \nFunctionality is limited in order to use this website');
                }
                else{
                    while (tr.parentNode&&tr.nodeName.toUpperCase()!='TR')
                    {
                        tr=tr.parentNode;
                    }
                    var table1=document.getElementById('tst1');
                    if (!this.rows)
                    {
                        var rows=table1.getElementsByTagName('TR');
                        this.rows=[];
                        for (var z0=0;z0<rows.length;z0++)
                        {
                            this.rows[z0]=rows[z0];
                        }
                    }
                    var table2=document.getElementById('tst2');
                    if (tr.parentNode!=table2)
                    {
                        //if tr's parent is not in test 2 table then add it to test2
                        table2.appendChild(tr);
                    }
                    else 
                    {       
                        table1.appendChild(tr);

                        for (var z0=0;z0<this.rows.length;z0++)
                        {
                            if (this.rows[z0].parentNode==table1)
                            {
                                table1.appendChild(this.rows[z0]);
                            }
                        }
                    }
                }
            }

        </script>
        <?php
    }
    $debug = '';
    $echo = '';
    $x=1;
    if(!$selectOnly){
        $kwd_search = isset($_POST['kwd_search']) ? $_POST['kwd_search'] : '';
        $echo = '<div align="center">Quick Search: <input name="kwd_search" type="text" id="kwd_search" value="'.$kwd_search.'"/>
            </div><br/>
            <table id="floatingTH" border="1" style="width:100%;">
                    <thead>
                    <tr>';
        for($y=0;$y<sizeof($theTable[0]);$y++){
            $echo .= '<th style="background-color:black;">'.$theTable[0][$y].'</th>';
        }
        $echo .= '</tr>
            <tbody id="tst1">
            ';
        for($x;$x<sizeof($theTable);$x++){
            $echo .= '<tr>';
            for($y=0;$y<sizeof($theTable[$x]);$y++){
                if($theTable[$x][$y] == "EMERGENCY")
                    $echo .= '<td id="red">';
                else if($theTable[$x][$y] == "CONTROLED KEY")
                    $echo .= '<td id="yellow">';
                else
                    $echo .= '<td>';
                $echo .= $theTable[$x][$y].'</td>';
            }
            $echo .= '</tr>
                ';
        }
        $echo .= '</tbody></table><br/>';
    }

    $echo .= '<table id="selectTable">
                    <thead><tr>
             ';
    for($y=0;$y<sizeof($theTable[0]);$y++){
        $echo .= '<th>'.$theTable[0][$y].'</th>';
    }
    $echo .= '</tr>
        <tbody id="tst2" >
        ';
   $debug .= 'selected values: '.sizeof($selectedValues);
   if(!empty($selectedValues)){
        for($z=0;$z<sizeof($selectedValues);$z++){
            $echo .= '<tr>';
            for($y=0;$y<sizeof($selectedValues[$z]);$y++){
                if($selectedValues[$z][$y] == "EMERGENCY")
                    $echo .= '<td id="red">';
                else if($selectedValues[$z][$y] == "CONTROLED KEY")
                    $echo .= '<td id="yellow">';
                else
                    $echo .= '<td id="blue">';
                $echo .= $selectedValues[$z][$y].'</td>';
                $x++; //Total Rows Counter
            }
            $echo .= '</tr>
                ';
        }
   }
    $echo .= '
        </tbody>
        </table><br/>';    
    $echo = '<input type="hidden" name="totalRows" value="'.$x.'" />'.$echo;
    echo $echo;
    //popUpMessage($debug);

}
function capitolizeFirstLetterOnly($string){
    $temp = strtolower($string);
    $newString = strtoupper(substr($temp, 0, 1));
    $newString .= substr($temp, 1);
    
    return $newString;
}
?>
