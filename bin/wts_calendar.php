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

	// Navigation for the monthly calender view
        $Prenavigation = "<form action=\"".$_SERVER['REQUEST_URI']."\" method=\"post\"><input type=\"hidden\" name=\"mon\" value=\"$prev_month\">
        <input type=\"hidden\" name=\"year\" value=\"$year\">
        <input type=\"submit\" value=\"<< ".$dtFirstDay."\" /></form>";

        $Nextnavigation = "<form action=\"".$_SERVER['REQUEST_URI']."\" method=\"POST\"><input type=\"hidden\" name=\"mon\" value=\"$next_month\">
        <input type=\"hidden\" name=\"year\" value=\"$year\">
        <input type=\"submit\" value=\"".$dtLastDay." >>\" /></form>";

        $mysqli = $config->mysqli;

	//Here we start building the table heads
	echo "<table width=720>";
	echo "<tr><th colspan=7> ";

	echo "<div class=\"login\"><br />Approved Requests

            <table border=\"1\" width=\"250\" cellspacing=\"0\" cellpadding=\"0\">
                    <tr>
                    <td width=\"8\" height=\"5\" align=\"center\" valign=\"middle\">".$Prenavigation."</td>
                    <td height='8'  width=\"175\" align=\"center\" valign=\"middle\" style=\"padding:0px 0px 0px 0px;\"> ".$title."&nbsp;".$year ." </td>
                    <td width=\"8\" height=\"5\" align=\"center\" valign=\"middle\">".$Nextnavigation."</td>
                       </tr>
            </table></td>

        </div>";

	echo "</th></tr>";
	echo "<tr><td align=\"center\" width=102>Sunday</td><td align=\"center\" width=102>Monday</td><td align=\"center\" width=102>Tuesday</td><td align=\"center\" width=102>Wednesday</td><td align=\"center\" width=102>Thurday</td><td align=\"center\" width=102>Friday</td><td align=\"center\" width=102>Saturday</td></tr>";

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


	//count up the days, untill we've done all of them in the month
	while ( $day_num <= $days_in_month )
	{        
            //Number of OverTime 
            $myq = "SELECT `REFER` , `IDNUM` , `TIMETYPEID` , `USEDATE` , `STATUS`
                FROM `REQUEST`
                WHERE `TIMETYPEID` = 'OT'
                AND USEDATE = '".$year."-".$month."-".$day_num."'
                AND `STATUS` = 'APPROVED'";
            //popUpMessage($myq); //DEBUG
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            
            $overTime = $result->num_rows;
            
            //Number of Sick Call Offs
            $myq = "SELECT `REFER` , `IDNUM` , `TIMETYPEID` , `USEDATE` , `STATUS`
                FROM `REQUEST`
                WHERE `TIMETYPEID` = 'SK' 
                AND `STATUS` = 'APPROVED'
                AND USEDATE = '".$year."-".$month."-".$day_num."'";
            //popUpMessage($myq); //DEBUG
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            
            $sick = $result->num_rows;
            
            //Number of Personal
            $myq = "SELECT `REFER` , `IDNUM` , `TIMETYPEID` , `USEDATE` , `STATUS`
                FROM `REQUEST`
                WHERE `TIMETYPEID` = 'PR' 
                AND `STATUS` = 'APPROVED'
                AND USEDATE = '".$year."-".$month."-".$day_num."'";
            //popUpMessage($myq); //DEBUG
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            
            $personal = $result->num_rows;
            
            //Number of Vacations
            $myq = "SELECT `REFER` , `IDNUM` , `TIMETYPEID` , `USEDATE` , `STATUS`
                FROM `REQUEST`
                WHERE `TIMETYPEID` = 'VA' 
                AND `STATUS` = 'APPROVED'
                AND USEDATE = '".$year."-".$month."-".$day_num."'";
            //popUpMessage($myq); //DEBUG
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            
            $vacation = $result->num_rows;
            
            echo "<td height='100' valign = \"top\" align=\"center\"><div style=\"background-color:grey\">";
            echo '<form name="goToDetails" method="POST" action="?submittedRequests=true&cust=true">
            <input name="start" type="hidden" value="'.$month.'/'.$day_num.'/'.$year.'" />
            <input name="end" type="hidden" value="'.$month.'/'.$day_num.'/'.$year.'" />
            <input type="submit" name="goToDetails" value="'.$day_num.'" /></form></div>';
            
            if($overTime > 0)
                echo 'Overtime: '.$overTime.'<br/>';
            if($sick > 0)
                echo 'Sick: '.$sick.'<br/>';
            if($personal > 0)
                echo 'Person: '.$personal.'<br/>';
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

	echo "</tr></table>";
}

?>
