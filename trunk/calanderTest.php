<?php

function viewClandar(){
	if($_POST['mon'] != "")
	{
		$month = $_POST['mon'];
	}
	else
	{
		$month = date('n');
	}
	if($_POST['year'] != ""){
		$year = $_POST['year'];
	}
	else{
		$year = date('Y');
	}
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
		$Prenavigation = "<form action=\"calanderTest.php\" method=\"post\"><input type=\"hidden\" name=\"mon\" value=\"$prev_month\">
		<input type=\"hidden\" name=\"year\" value=\"$year\">
		<input type=\"submit\" value=\"Previous\" /></form>";

		$Nextnavigation = "<form action=\"calanderTest.php\" method=\"POST\"><input type=\"hidden\" name=\"mon\" value=\"$next_month\">
		<input type=\"hidden\" name=\"year\" value=\"$year\">
		<input type=\"submit\" value=\"Next\" /></form>";


	//Here we start building the table heads
	echo "<table border=1 width = 714 style='border-collapse:collapse;'>";
	echo "<tr><th colspan=7> ";

	echo "<div class=\"my-calander-top\"><div class=\"clear\"></div><br />
	<table align=\"center\" style=\"background-color: #000000;\" width=\"714\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"pd-top\">
	<tr>
	  <td style=\"background-color: white;\" width=\"20\">&nbsp;</td>
	  <td style=\"background-color: white;\" width=\"225\" align=\"left\" valign=\"middle\">
	  <table width=\"250\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		<tr>
		  <td style=\"background-color:white\" width=\"8\" height=\"5\" align=\"center\" valign=\"middle\">".$Prenavigation."</td>
		  <td style=\"background-color:white\" height='8'  width=\"175\" align=\"center\" valign=\"middle\" style=\"padding:0px 0px 0px 0px;\">".$dtFirstDay ." - ".$dtLastDay."</td>
		  <td style=\"background-color:white\" width=\"8\" height=\"5\" align=\"center\" valign=\"middle\">".$Nextnavigation."</td>
		</tr>
	  </table>                          </td>
	  <td style=\"background-color: white;\" width=\"434\" align=\"right\" valign=\"middle\"><a href=\"index.php\">HOME</a><br />".$title."&nbsp;".$year ."</td>
	  <td style=\"background-color: white;\" width=\"60\">&nbsp;</td><td>
	</tr>
  </table>
 </div>";

	echo "</th></tr>";
	echo "<tr><td style=\"background-color: #FFFFFF;\" align=\"center\" width=102>Sunday</td><td align=\"center\" width=102>Monday</td><td align=\"center\" width=102>Tuesday</td><td align=\"center\" width=102>Wednesday</td><td align=\"center\" width=102>Thurday</td><td align=\"center\" width=102>Friday</td><td align=\"center\" width=102>Saturday</td></tr>";

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
		echo "<td height='100' valign = \"top\" align=\"center\"><div style=\"background-color:grey\">
                <a href=\"events.php?date1_year=$year&date1_month=$month&date1_day=$day_num\"> $day_num  </a></div><div>";
		$day_count++;

		//Make sure we start a new row every week
		if ($day_count > 7)
		{
			echo "</tr><tr  height='25'>";
			$day_count = 1;
		}
                $day_num++;
                if($day_num == 2){
                    $day_num = "02";
                }
                if($day_num == 3){
                    $day_num = "03";
                }
                if($day_num == 4){
                    $day_num = "04";
                }
                if($day_num == 5){
                    $day_num = "05";
                }
                if($day_num == 6){
                    $day_num = "06";
                }
                if($day_num == 7){
                    $day_num = "07";
                }
                if($day_num == 8){
                    $day_num = "08";
                }
                if($day_num == 9){
                    $day_num = "09";
                }
	}


	//Finaly we finish out the table with some blank details if needed
	while ( $day_count >2 && $day_count <=7 )
	{
		echo "<td> </td>";
		$day_count++;
	}

	echo "</tr></table>";
return 0;
}
viewClandar();
?>