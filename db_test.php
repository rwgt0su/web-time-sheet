<script language="javascript">
function Clickheretoprint()
{ 
  var disp_setting="toolbar=yes,location=no,directories=yes,menubar=yes,"; 
      disp_setting+="scrollbars=yes,width=650, height=600, left=100, top=25"; 
  var content_vlue = document.getElementById("print_content").innerHTML; 

  var docprint=window.open("","",disp_setting); 
   docprint.document.open(); 
   docprint.document.write('<html><head><title>MCSO - Portal Printing System</title>'); 
   docprint.document.write('</head><body onLoad="self.print()"><center>');          
   docprint.document.write(content_vlue);          
   docprint.document.write('</center></body></html>'); 
   docprint.document.close(); 
   docprint.focus(); 
}
</script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>

    <script type="text/javascript">
        function UpdateTableHeaders() {
            $("div.sortable").each(function() {
                var originalHeaderRow = $(".tableFloatingHeaderOriginal", this);
                var floatingHeaderRow = $(".tableFloatingHeader", this);
                var offset = $(this).offset();
                var scrollTop = $(window).scrollTop();
                if ((scrollTop > offset.top) && (scrollTop < offset.top + $(this).height())) {
                    floatingHeaderRow.css("visibility", "visible");
                    floatingHeaderRow.css("top", Math.min(scrollTop - offset.top, $(this).height() - floatingHeaderRow.height()) + "px");

                    // Copy cell widths from original header
                    $("th", floatingHeaderRow).each(function(index) {
                        var cellWidth = $("th", originalHeaderRow).eq(index).css('width');
                        //alert(cellWidth);
                        cellWidth = "300px";
                        //$(this).css('width', cellWidth);
                    });

                    // Copy row width from whole table
                    floatingHeaderRow.css("width", $(this).css("width"));
                    
                    
                    // Copy cell widths from original header
//                    var y = 0;
//                    $("th", floatingHeaderRow).each(function(index) {
//                        var cellWidth = $("th", originalHeaderRow).eq(index).css('width');
//                        //var cellWidth = document.getElementById('sorter').rows[0].cells[y].offsetWidth;
//                        //alert('cell '+y+' is '+cellWidth);
//                        $(this).css("width", "350px");
//                        y = y+ 1;
//                    });

                    // Copy row width from whole table
                    //floatingHeaderRow.css("width", $(this).css("width"));
                }
                else {
                    floatingHeaderRow.css("visibility", "hidden");
                    floatingHeaderRow.css("top", "0px");
                }
            });
        }

        $(document).ready(function() {
            $("table.sortable").each(function() {
                $(this).wrap("<div class=\"sortable\" id=\"sorter\" style=\"position:relative\"></div>");

                var originalHeaderRow = $("tr:first", this)
                originalHeaderRow.before(originalHeaderRow.clone());
                var clonedHeaderRow = $("tr:first", this)

                clonedHeaderRow.addClass("tableFloatingHeader");
                clonedHeaderRow.css("position", "absolute");
                clonedHeaderRow.css("top", "0px");
                clonedHeaderRow.css("left", $(this).css("margin-left"));
                clonedHeaderRow.css("visibility", "hidden");

                originalHeaderRow.addClass("tableFloatingHeaderOriginal");
            });
            UpdateTableHeaders();
            $(window).scroll(UpdateTableHeaders);
            $(window).resize(UpdateTableHeaders);
        });
    </script>
<a href="javascript:Clickheretoprint()">Print</a>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Mahoning County Sheriff's Office - Web Time Sheet</title>
    <link type="text/css" href="templetes/DarkTemp/styles/reset.css" rel="stylesheet" media="all" />
    <link type="text/css" href="templetes/DarkTemp/styles/text.css" rel="stylesheet" media="all" />
    <link type="text/css" href="templetes/DarkTemp/styles/960.css" rel="stylesheet" media="all" />
    <link type="text/css" href="templetes/DarkTemp/styles/style.css" rel="stylesheet" media="all" />
    <link type="text/css" href="templetes/DarkTemp/styles/print.css" rel="stylesheet" media="print" />
    <link rel="stylesheet" href="templetes/DarkTemp/styles/tableSort.css" />
    <script type="text/javascript" src="bin/jQuery/js/tableSort.js"></script>
    
</head>
<body>
    <div class="header_cotainer">
        <div class="container_12">
            <div class="grid_3">
                <a href="/" ><h1 class="logo">MCSO</h1></a>
            </div>
            <div class="grid_9">
                <div class="menu_items">                
                    <a href="/Andrew/wts_index.php" class="button_link" title="Home">Home</a>
                    <a href="https://mail.mahoningcountyoh.gov" target="_blank" class="button_link" title="Email">Email</a>
                    <a href="?about=true" class="button_link" title="About">About</a>
                    <a href="/wts_help.htm" target="_blank" class="button_link" title="Help">Help</a>

                    <div class="search">
                        <form action="/Andrew/wts_index.php" name="Search_Form" method="POST" >
                        <input type="text" name="searchInput" value="" />
                        <input type="submit" name="searchBtn" value="Search" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Content Section -->
    <div class="content_container">
        <div class="container_12 content_highlight">
            <!--Left Menu Section -->
            <div class="grid_4">
                <h2>Main Menu</h2><ul><li><a href="/Andrew/wts_index.php" >Home</a></li><li><a href="https://mail.mahoningcountyoh.gov" target="_blank">County E-Mail</a></li><li><a href="http://mail.sheriff.mahoning.local" target="_blank">Sheriff E-Mail</a></li><li><a href="http://techsupport.sheriff.mahoning.local" target="_blank">Technical Support</a></li><li><a href="https://ijis.mahoningsheriff.com" target="_blank">IJIS Website</a></li><li><a href="http://connect.mahoningcountyoh.gov" target="_blank">Remote Support</a></li><li><a href="https://ijis.mahoningsheriff.com" target="_blank">IJIS Website</a></li></ul><h2>User Menu</h2><ul><li><a href="?updateProfile=true">Update Profile</a></li><li><a href="?usermenu=true&ChangeBtn=true">Change Your Password</a><br /><br /><li><a href="?secLog=true">Secondary Logs</a></li><li><a href="?radioLog=true">Radio Logs</a></li><li><a href="?leave=true">Request Form</a></li><li><a href="?myReq=true">My Submitted Requests</a></li></ul><div class="divider"></div><h2>Supervisor Menu</h2><ul><li><a href="?reports=true">Reports</a></li><li><a href="?approve=true">Approve Leave Requests</a></li><li><a href="?secApprove=true">Approve Secondary Logs</a></li></ul><div class="divider"></div><h2>HR Menu</h2><ul><li><a href="?approvedUse=true">Approved Requests Report</a></li><li><a href="?usereport=true">Time Use/Gain Report</a></li><li><a href="?munis=true">MUNIS Entry Report</a></li><li><a href="?usermenu=true">User Manager</a></li><li><a href="?isAnounceAdmin=true">Anouncement Manager</a></li><li><a href="?phpMyEdit=true">Database Admin (phpMyEdit)</a></li></ul><div class="divider"></div><h2>Admin Menu</h2><ul><li><a href="?resManage=true">Reserve Manager</a></li><li><a href="?phpMyEdit=true">Database Admin (phpMyEdit)</a></li><li><a href="?eventLogs=true">Event Logs</a></li></ul>            </div>
            
            <!--Center Content Section -->
            <div class="grid_8" >
                <!--Content Login Section -->
                <div class="login">
                    <div id="result" align="right">Logged in as: <font size="3">admin</font><br />Last Login: 2012-09-28 14:51:51<br /><a href="?logout=true">Log Out </a><br /><br /></div>                    <div class="clear"></div>
                </div> 
             
                <div class="divider"></div>
                
                <!--Content Section -->
                            <div class="post">    <p><a href="/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3&cust=true">Use Custom Date Range</a></br>
    <form name='custRange' action='/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3' method='post'><div align="center">
            Show Submitted Requests for the following division: 
            <select name="divisionID" onchange="this.form.submit()"><option value="600">JAIL</option><option value="601">JAIL ADMIN</option><option value="602">SHERIFF ADMIN</option><option value="603">COURT & ANNEX</option><option value="604">JJC</option><option value="605">HEADQUARTERS</option><option value="606">CIVIL</option><option value="607">WARRANTS & RECORDS</option><option value="608">OAKHILL</option><option value="609">CSEA & LITTER</option><option value="0">Not Assigned</option><option value="All">All</option></select></div></form>    <p><div style="float:left"><a href="/Andrew/wts_index.php?submittedRequests=true&ppOffset=-4">Previous</a></div>  
    <div style="float:right"><a href="/Andrew/wts_index.php?submittedRequests=true&ppOffset=-2">Next</a></div></p>
                            <div id="print_content"><h3><center>Gain/Use Requests for pay period 26 Aug 2012 through 8 Sep 2012.</center></h3>
    
     <form name="submittedRequests" method="POST"> <input type="hidden" name="formName" value="submittedRequests"/> 
    <link rel="stylesheet" href="templetes/DarkTemp/styles/tableSort.css" />
            <script type="text/javascript" src="bin/jQuery/js/tableSort.js"></script>
                <div id="wrapper"><table class="sortable" id="sorter">
                        <tr><th>Edit</th><th>Delete</th><th>RefNo</th><th>Employee</th><th>Requested</th><th>Used</th><th>Start</th><th>End</th><th>Hrs</th><th>Type</th><th>Subtype</th><th>Calloff</th><th>Comment</th><th>Status</th><th>ApprovedBy</th><th>Reason</th><th>isHRApproved</th></tr><tr style="text-decoration:line-through" ><td><input type="submit"  name="editBtn1" value="Edit" onClick="this.form.action='?leave=true'" />
                            <input type="hidden" name="requestID1" value="104" /></td><td><button type="submit"  name="unDeleteBtn1" value="104" onClick="this.form.action=/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3;this.form.submit()" >unDelete</button></td><td>104</td><td>SUPERVISOR, SUPER</td><td>Aug 09 2012 0950</td><td>Fri Aug 31 2012</td><td>0900</td><td>1700</td><td>8</td><td>SICK</td><td>NONE</td><td>NO</td><td>Spider Bit</td><td>EXPUNGED</td><td></td><td></td><td>0</td><tr ><td><input type="submit"  name="editBtn2" value="Edit" onClick="this.form.action='?leave=true'" />
                                <input type="hidden" name="requestID2" value="105" /></td><td><button type="submit"  name="deleteBtn2" value="105" onClick="this.form.action=/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3;this.form.submit()" >Delete</button></td><td>105</td><td>SUPERVISOR, SUPER</td><td>Aug 09 2012 0950</td><td>Sat Sep 01 2012</td><td>0900</td><td>1700</td><td>8</td><td>SICK</td><td>NONE</td><td>NO</td><td>Spider Bit</td><td>PENDING</td><td></td><td></td><td>0</td><tr ><td><input type="submit"  name="editBtn3" value="Edit" onClick="this.form.action='?leave=true'" />
                                <input type="hidden" name="requestID3" value="106" /></td><td><button type="submit"  name="deleteBtn3" value="106" onClick="this.form.action=/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3;this.form.submit()" >Delete</button></td><td>106</td><td>SUPERVISOR, SUPER</td><td>Aug 09 2012 0950</td><td>Sun Sep 02 2012</td><td>0900</td><td>1700</td><td>8</td><td>SICK</td><td>NONE</td><td>NO</td><td>Spider Bit</td><td>PENDING</td><td></td><td></td><td>0</td><tr ><td><input type="submit"  name="editBtn4" value="Edit" onClick="this.form.action='?leave=true'" />
                                <input type="hidden" name="requestID4" value="107" /></td><td><button type="submit"  name="deleteBtn4" value="107" onClick="this.form.action=/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3;this.form.submit()" >Delete</button></td><td>107</td><td>SUPERVISOR, SUPER</td><td>Aug 09 2012 0950</td><td>Mon Sep 03 2012</td><td>0900</td><td>1700</td><td>8</td><td>SICK</td><td>NONE</td><td>NO</td><td>Spider Bit</td><td>PENDING</td><td></td><td></td><td>0</td><tr ><td><input type="submit"  name="editBtn5" value="Edit" onClick="this.form.action='?leave=true'" />
                                <input type="hidden" name="requestID5" value="108" /></td><td><button type="submit"  name="deleteBtn5" value="108" onClick="this.form.action=/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3;this.form.submit()" >Delete</button></td><td>108</td><td>SUPERVISOR, SUPER</td><td>Aug 09 2012 0950</td><td>Tue Sep 04 2012</td><td>0900</td><td>1700</td><td>8</td><td>SICK</td><td>NONE</td><td>NO</td><td>Spider Bit</td><td>PENDING</td><td></td><td></td><td>0</td><tr ><td><input type="submit"  name="editBtn6" value="Edit" onClick="this.form.action='?leave=true'" />
                                <input type="hidden" name="requestID6" value="109" /></td><td><button type="submit"  name="deleteBtn6" value="109" onClick="this.form.action=/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3;this.form.submit()" >Delete</button></td><td>109</td><td>SUPERVISOR, SUPER</td><td>Aug 09 2012 0950</td><td>Wed Sep 05 2012</td><td>0900</td><td>1700</td><td>8</td><td>SICK</td><td>NONE</td><td>NO</td><td>Spider Bit</td><td>PENDING</td><td></td><td></td><td>0</td><tr ><td><input type="submit"  name="editBtn7" value="Edit" onClick="this.form.action='?leave=true'" />
                                <input type="hidden" name="requestID7" value="152" /></td><td><button type="submit"  name="deleteBtn7" value="152" onClick="this.form.action=/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3;this.form.submit()" >Delete</button></td><td>152</td><td>MCUSER, JOHNNY</td><td>Aug 29 2012 2046</td><td>Wed Aug 29 2012</td><td>0000</td><td>0100</td><td>1</td><td>COMP TIME GAIN</td><td>NONE</td><td>NO</td><td></td><td>PENDING</td><td></td><td></td><td>0</td><tr ><td><input type="submit"  name="editBtn8" value="Edit" onClick="this.form.action='?leave=true'" />
                                <input type="hidden" name="requestID8" value="153" /></td><td><button type="submit"  name="deleteBtn8" value="153" onClick="this.form.action=/Andrew/wts_index.php?submittedRequests=true&ppOffset=-3;this.form.submit()" >Delete</button></td><td>153</td><td>MCUSER, JOHNNY</td><td>Aug 29 2012 2051</td><td>Wed Aug 29 2012</td><td>0000</td><td>0100</td><td>1</td><td>OVERTIME</td><td>NONE</td><td>NO</td><td></td><td>PENDING</td><td></td><td></td><td>0</td><input type="hidden" name="totalRows" value="9" /></tr></table></form></div>
                <script type="text/javascript">
                    var sorter=new table.sorter("sorter");
                    sorter.init("sorter",2);
                </script>
                            </div><a href="javascript:window.print()">Print</a><div class="clear"></div></div><div class="divider"></div>
                            
                <!--Content Post 2 Section -->
                <!-- <div class="post">
                    <div class="thumbnail"><a href="#"><img src="templetes/DarkTemp/images/image.jpg" alt="" /></a></div>
                    <h3><a href="#">Sample Header</a></h3>
                    <p>Sample content wouuld be placed within this paragraph</p>
                    <div class="post_footer">
                        <div class="comments">left column</div>
                        <div class="more"><a href="#">right column</a></div>
                    </div>
                    <div class="clear"></div>
                </div>
                
                <div class="divider"></div> -->
                

                
               <!--End Content Post Section --> 
            </div>
            <div class="clear"></div>
        </div>
    </div>
    
    <!--Footer Section -->
    <div class="footer_container">
        <div class="container_12">
            <div class="grid_4">© Mahoning County Sheriff's Office 2012</div>
            <div class="grid_8">
                <a href="?about=true">Terms and Conditions</a>
                <a href="https://www.facebook.com/pages/Mahoning-County-Sheriffs-Office/211950208818335" target="_blank"><img src="templetes/DarkTemp/images/facebook_icon.jpg" alt="" /></a>
            </div>
        </div>
    </div>
</body>





<?php
error_reporting(E_ALL);
ini_set('display_errors', True);







//$email = '3302072388@vtext';
// echo 'Email: '.$email;
// $subject = "Testing Email";
// 
// $body = "this is just a test email.
//     
//     
//Thanks,
//
//Andrew Turner
//Computer & Information Systems Manager
//Mahoning County Sheriff's Office
//
//Email: aturner@mahoningcountyoh.gov
//Phone: (330) 480-4947
//Cell: (330) 720-5431
//
//Disclaimer: This e-mail and any files transmitted with it are the property of 
//the Mahoning County Sheriff's Office. It is intended only for the use of the 
//individual or entity to whom it is addressed and may contain information that 
//is privileged, confidential, and exempt from disclosure under applicable law. 
//If the reader of this message is not the intended recipient, or the employee or 
//agent responsible for delivering the message to the intended recipient, you are 
//hear by notified that any dissemination, distribution, copying, or conveying of 
//this communication in any matter is strictly prohibited. If you have received 
//this communication in error, please delete and notify us immediately at 
//(330) 480-4943. Thank you for your cooperation.";
// 
// $fromEmail = "mis@sheriff.mahoning.local";
// 
// if(mail($email, $subject, $body, "From: $fromEmail"))
//    echo "<p>successfull!</p>";
//else
//    echo '<p>Failed to send!</p>';
//
//require('class.phpmailer.php');
//$message = "Hello world";
//$mail = new PHPMailer();
//$mail->SMTPDebug  = 2;
//$mail->IsSMTP();
//$mail->Host = "localhost";
//$mail->CharSet = "UTF-8";
//$mail->AddAddress($email, "A Turner");
//$mail->SetFrom($fromEmail,"Test From");
//$mail->Subject = $subject;
//$mail->Body = $body;
//$mail->Send();

//phpinfo();

?>