<?php
function viewClandar($config, $month, $year){
	$day = date('j');
	$short = date('y');
	if($month > 12){
		$month = $month - 12;
		$year = $year + 1;
	}
	if($month < 1){
		$month = $month + 12;
		$year = $year - 1;
	}

	$next_month = $month + 1;
	$prev_month = $month - 1;

	$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

	//Here we generate the first day of the month
	$first_day = mktime(0,0,0, $month, 1, $year);
	$dtFirstDay =  date('F', mktime(0,0,0, $month-1, 1, $year));
	$dtLastDay = date('F', mktime(0,0,0, $month+1, 1, $year));

	//This gets us the month name
	$title = date('F', $first_day);


	//Here we find out what day of the week the first day of the month falls on
	$day_of_first_day = date('w', mktime(0, 0, 0, $month, 1, $year));

        $myDivID = "";
        if(isset($_POST['divisionID'])){
            $myDivID = $_POST['divisionID'];
        }
	// Navigation for the monthly calender view
        $Prenavigation = "<input type=\"submit\" name=\"prevMonth\"  value=\"<< ".$dtFirstDay."\" />";

        $Nextnavigation = "<input type=\"submit\" name=\"nextMonth\" value=\"".$dtLastDay." >>\" />";

        $mysqli = $config->mysqli;

	//Here we start building the table heads
	echo "</div><div class=\"cal\"><table width=720>";
	echo "<tr><th colspan=7> ";

        
	echo "<br/><h3>Approved Requests<br/></h3><br/>";
        echo '<form name="divisionForm" method="POST">';
        echo "<input type=\"hidden\" name=\"prevMon\" value=\"$prev_month\">
            <input type=\"hidden\" name=\"curMon\" value=\"$month\">
            <input type=\"hidden\" name=\"nextMon\" value=\"$next_month\">
            <input type=\"hidden\" name=\"year\" value=\"$year\">
            <table border=\"0\" width=\"700\" cellspacing=\"0\" cellpadding=\"0\">
                    <tr>
                    <td width=10>&nbsp;</td>
                    <td width=\"8\" height=\"5\" align=\"center\" valign=\"middle\">".$Prenavigation."</td>
                    <td height='8'  width=\"100\" align=\"center\" valign=\"middle\" style=\"padding:0px 0px 0px 0px;\"> ".$title."&nbsp;".$year ." </td>
                    <td width=\"8\" height=\"5\" align=\"center\" valign=\"middle\">".$Nextnavigation."</td>
                    <td align=\"right\" valign=\"middle\">";
             $requestReport = new request_reports($config);
             $requestReport->config = $config;
             $requestReport->showDivisionDropDown();
             $myDivID = $requestReport->divisionID;
            //echo 'Show for division: 
            //<select name="divisionID" onchange="this.form.submit()">';

//            if(isset($_POST['divisionID'])){
//                $myDivID = $_POST['divisionID'];
//            }
//            else{
//                if($admin >= 50){
//                    $myDivID = "All"; 
//                }
//                else{
//                    $mydivq = "SELECT DIVISIONID FROM EMPLOYEE E WHERE E.IDNUM='" . $_SESSION['userIDnum']."'";
//                    $myDivResult = $mysqli->query($mydivq);
//                    SQLerrorCatch($mysqli, $myDivResult);
//                    $temp = $myDivResult->fetch_assoc();
//                    $myDivID = $temp['DIVISIONID'];
//                }
//            }
//
//            $alldivq = "SELECT * FROM `DIVISION` WHERE 1";
//            $allDivResult = $mysqli->query($alldivq);
//            SQLerrorCatch($mysqli, $allDivResult);
//            while($Divrow = $allDivResult->fetch_assoc()) {
//                echo '<option value="'.$Divrow['DIVISIONID'].'"';
//                if($Divrow['DIVISIONID']==$myDivID)
//                    echo ' SELECTED ';
//                echo '>'.$Divrow['DESCR'].'</option>';
//            }
//            if(isset($_POST['divisionID'])){
//                if($myDivID == "All")
//                    echo '<option value="All" SELECTED>All</option>';
//                else
//                    echo '<option value="All">All</option>';
//            }
//            else
//                echo '<option value="All">All</option>';
//            echo '</select></form></div>';
        
        echo "      </td>
                </tr>
            </form></table></td>";

	echo "</th></tr>";
	echo "<tr><td align=\"center\" width=102>Sunday</td>
                <td align=\"center\" width=102>Monday</td>
                <td align=\"center\" width=102>Tuesday</td>
                <td align=\"center\" width=102>Wednesday</td>
                <td align=\"center\" width=102>Thurday</td>
                <td align=\"center\" width=102>Friday</td>
                <td align=\"center\" width=102>Saturday</td>
            </tr>";

	//This counts the days in the week, up to 7
	$day_count = 1;
	$blank = $day_of_first_day;
	echo "<tr height='25'>";
	//first we take care of those blank days
	while ( $blank > 0 )
	{
		echo "<td ></td>";
		$blank = $blank-1;
		$day_count++;
	}


	//sets the first day of the month to 1
	$day_num = "01";
        $timetype[0] = "OT";
        $timetype[1] = "SK";
        $timetype[2] = "PR";
        $timetype[3] = "VA";

	//count up the days, untill we've done all of them in the month
	while ( $day_num <= $days_in_month )
	{        
            for($i=0;$i<count($timetype);$i++){
                if($myDivID == "All"){
                    $myq = "SELECT `REFER` , `IDNUM` , `TIMETYPEID` , `USEDATE` , `STATUS`
                        FROM `REQUEST`
                        WHERE `TIMETYPEID` = '".$timetype[$i]."'
                        AND USEDATE = '".$year."-".$month."-".$day_num."'
                        AND `STATUS` = 'APPROVED'";
                }
                else{
                    $myq = "SELECT DISTINCT REFER 'RefNo', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Employee', DATE_FORMAT(REQDATE,'%d %b %Y %H%i') 'Requested', 
                                    DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                                    DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                                    T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', STATUS 'Status', 
                                    APR.LNAME 'ApprovedBy', REASON 'Reason' 
                                FROM REQUEST R
                                INNER JOIN EMPLOYEE AS REQ ON REQ.IDNUM=R.IDNUM
                                LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=R.APPROVEDBY
                                INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=R.TIMETYPEID                         
                                WHERE R.TIMETYPEID = '".$timetype[$i]."'
                                AND USEDATE = '".$year."-".$month."-".$day_num."' 
                                
                                ".$requestReport->filters."
                                AND R.STATUS = 'APPROVED'
                                ORDER BY REFER";
                }

                $result = $mysqli->query($myq);
                SQLerrorCatch($mysqli, $result);
                if($i == 0)
                    $overTime = $result->num_rows;
                if($i == 1)
                    $sick = $result->num_rows;
                if($i == 2)
                    $personal = $result->num_rows;
                if($i == 3)
                    $vacation = $result->num_rows;
            }

            echo "<td height='100' valign = \"top\" align=\"center\"><div style=\"background-color:grey\">";
            echo '<form name="goToDetails" method="POST" action="?submittedRequestsNEW=true&cust=true">
                <input type="hidden" name="divisionID" value="'.$myDivID.'" />
                <input type="hidden" name="customDate" value="true" />
                <input name="start" type="hidden" value="'.$month.'/'.$day_num.'/'.$year.'" />
                <input name="end" type="hidden" value="'.$month.'/'.$day_num.'/'.$year.'" />
                <input type="submit" name="goToDetails" value="'.$day_num.'" /></form></div>';
            
            if($overTime > 0)
                echo 'Overtime: '.$overTime.'<br/>';
            if($sick > 0)
                echo 'Sick: '.$sick.'<br/>';
            if($personal > 0)
                echo 'Personal: '.$personal.'<br/>';
            if($vacation > 0)
                echo 'Vacation: '.$vacation.'<br/>';
            echo "<div>";

            $day_count++;

            //Make sure we start a new row every week
            if ($day_count > 7)
            {
                    echo "</tr><tr  height='25'>";
                    $day_count = 1;
            }
            $day_num++;
            if(strlen((string)$day_num) == 1){
                $day_num = "0".(string)$day_num;
            }
	}


	//Finaly we finish out the table with some blank details if needed
	while ( $day_count >2 && $day_count <=7 )
	{
		echo "<td> </td>";
		$day_count++;
	}

	echo "</tr></table></div>";
}

?>
