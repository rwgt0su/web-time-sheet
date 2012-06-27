<?php

function displayContent($wts_content, $config){
    if($wts_content->isHome){
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
        displayAnnounce($config);
    }
    if($wts_content->isWelcome()){
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isAnounceAdmin){
        ?>
        <div class="post"><?php displayAdminAnnounce($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isDBTest()){
        ?>
        <div class="post"><?php displayDBTest(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isLeaveForm){
        ?>
        <div class="post"><?php displayLeaveForm(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isSubmittedRequests){
        ?>
        <div class="post"><?php displaySubmittedRequests(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isLeaveApproval){
        ?>
        <div class="post"><?php displayLeaveApproval(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isUserMenu){
        ?>
        <div class="post"><?php displayUserMenu($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isLogout()){
        logoutUser("You have logged out");
    }
    if($wts_content->isSearching){
        ?>
        <div class="post"><?php searchPage(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isUpdateProfile){
        ?>
        <div class="post"><?php displayUpdateProfile(); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
         
}

function displayWelcome($config){
    ?>
    <div class="thumbnail"><img src="/style/WellingtonBadge.gif" alt="" /></div>
    <h3><?php echo $config->getTitle(); ?></h3> 
    <p>Welcome to the Mahoning County Sheriff's Office Web Portal</p>
    <?php
}
?>
    
<script language="JavaScript" type="text/javascript">
function addRowToTable()
{
  var tbl = document.getElementById('tblSample');
  var lastRow = tbl.rows.length;
  // if there's no header row in the table, then iteration = lastRow + 1
  var iteration = lastRow;
  var row = tbl.insertRow(lastRow);
  
  // left cell
  var cellLeft = row.insertCell(0);
  var textNode = document.createTextNode(iteration);
  cellLeft.appendChild(textNode);
  
  // right cell
  var cellRight = row.insertCell(1);
  var newCode1 = "Event: <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp&nbsp&nbsp<input type=\"text\"size=\"35\"name=\"event" + iteration + "\" value=\"\" />";
  var newCode0 = "<br />Start Time: <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp&nbsp&nbsp<input type=\"text\"size=\"35\"name=\"time" + iteration + "\" value=\"\" />";
  var newCode2 = "<br />Description:<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<textarea name=\"description" +iteration + "\" cols=80 rows=3></textarea>";
  var newCode3 = "<br />URL: <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<input name=\"url" + iteration + "\" type\"text\" size=\"35\" value=\"\" />";
  cellRight.innerHTML = newCode1 +newCode0 + newCode2 + newCode3;
  
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
  var r=confirm("Are You Sure You Want To Remove The Previous Event?");
  if (r==true)
    {
      var tbl = document.getElementById('tblSample');
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






