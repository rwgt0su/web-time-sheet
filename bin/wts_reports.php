<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function displayReportMenu($config){
    if($config->adminLvl >= 25){
        echo '<div class="divider"></div>';
        echo "<h2>Supervisor Menu</h2>";
        echo '<li><a href="?submittedRequests=true">Submitted Requests by Division and by Dates or Pay Period</a></li>';
        echo '<li><a href="?subReqCal=true">Submitted Requests Calendar</a></li>';
        echo '<li><a href="?lookup=true">Submitted Request by Employee by Date</a></li>';
        if($config->adminLvl >= 50){
            echo '<li><a href="?hrEmpRep=true">Approved and Denied Requests by Employee by Payperiod</a></li>';
        }
        echo '</ul>';
    }
}

function reportsCal($config){
    $month =isset($_POST['mon']) ? $_POST['mon'] : date('n');
    $year =isset($_POST['year']) ? $_POST['year'] : date('Y');
    
    $passedDates = "";
    
    viewClandar($config, $month, $year);
}
function hrPayrolReportByEmployee($config){
    //what pay period are we currently in?
    $mysqli = $config->mysqli;
    
    $payPeriodQuery = "SELECT * FROM PAYPERIOD WHERE NOW() BETWEEN PPBEG AND PPEND";
    $ppResult = $mysqli->query($payPeriodQuery);
    $ppArray = $ppResult->fetch_assoc();

    /* $ppOffset stands for the number of pay periods to adjust the query by 
    * relative to the current period
    */
    $ppOffset = isset($_GET['ppOffset']) ? $_GET['ppOffset'] : '0';
    //set the right URI for link
    if(isset($ppOffset))
        //strip off the old GET variable and its value
        $uri =  preg_replace("/&ppOffset=.*/", "", $_SERVER['REQUEST_URI'])."&ppOffset=";
    else
        $uri = $_SERVER['REQUEST_URI']."&ppOffset="; //1st time set

    $startDate = new DateTime("{$ppArray['PPBEG']}");
    if($ppOffset < 0)
        //backward in time by $ppOffset number of periods
        $startDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
    else
        //forward in time by $ppOffset number of periods
        $startDate->add(new DateInterval("P".($ppOffset*14)."D"));

    $endDate = new DateTime("{$ppArray['PPEND']}");
    if($ppOffset < 0)
        //backward in time by $ppOffset number of periods
        $endDate->sub(new DateInterval("P".(abs($ppOffset)*14)."D"));
    else
        //forward in time by $ppOffset number of periods
        $endDate->add(new DateInterval("P".($ppOffset*14)."D"));

    ?>
    <p><a href="<?php echo $_SERVER['REQUEST_URI'].'&cust=true'; ?>">Use Custom Date Range</a></br>
    <?php 
    if (isset($_GET['cust']) && !isset($_POST['cancelBtn'])) {
        echo "<form name='custRange' action='".$_SERVER['REQUEST_URI']."' method='post'>";
        echo "<p> Start";
        displayDateSelect('start', 'date_1');   
        echo "End";
        displayDateSelect('end', 'date_2');
        echo "<input type='submit' value='Go' /><input type='submit' name='cancelBtn' value='Cancel' /></p></form>";
        //overwrite current period date variables with 
        //those provided by user
        if ( isset($_POST['start']) && isset($_POST['end']) ) {
            $startDate =  new DateTime( $_POST['start'] );
            $endDate =  new DateTime( $_POST['end'] );
            ?> <h3><center>Time Gained/Used from <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3> <?php
        }
    }
    else {
        ?>
        <p><div style="float:left"><a href="<?php echo $uri.($ppOffset-1); ?>">Previous</a></div>  
        <div style="float:right"><a href="<?php echo $uri.($ppOffset+1); ?>">Next</a></div></p>
        <h3><center>Time Gained/Used in pay period <?php echo $startDate->format('j M Y'); ?> through <?php echo $endDate->format('j M Y'); ?>.</center></h3>
        <?php
        $viewBtn = isset($_POST['viewDetailsBtn']) ? true : false;
        if($viewBtn){
            $myq = "SELECT REFER 'RefNo', REQ.MUNIS 'Munis', CONCAT_WS(', ',REQ.LNAME,REQ.FNAME) 'Name', 
                    DATE_FORMAT(USEDATE,'%a %d %b %Y') 'Used', STATUS 'Status',
                        DATE_FORMAT(BEGTIME,'%H%i') 'Start',
                        DATE_FORMAT(ENDTIME,'%H%i') 'End', HOURS 'Hrs',
                        T.DESCR 'Type', SUBTYPE 'Subtype', CALLOFF 'Calloff', NOTE 'Comment', 
                        APR.LNAME 'ApprovedBy', REASON 'Reason' 
                    FROM REQUEST
                    LEFT JOIN EMPLOYEE AS REQ ON REQ.IDNUM=REQUEST.IDNUM
                    LEFT JOIN EMPLOYEE AS APR ON APR.IDNUM=REQUEST.APPROVEDBY
                    INNER JOIN TIMETYPE AS T ON T.TIMETYPEID=REQUEST.TIMETYPEID
                    WHERE USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."'
                    AND REQ.IDNUM='".$_POST['empID']."'
                    AND (STATUS='APPROVED' OR STATUS='DENIED')
                    ";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            
            $theTable = array(array());
            $x = 0;
            $theTable[$x][0] = "Ref #";
            $theTable[$x][1] = "Munis #";
            $theTable[$x][2] = "Employee";
            $theTable[$x][3] = "Date of Use";
            $theTable[$x][4] = "Start Time";
            $theTable[$x][5] = "End Time";
            $theTable[$x][6] = "Hours";
            $theTable[$x][7] = "Type";
            $theTable[$x][8] = "Subtype";
            $theTable[$x][9] = "Call Off";
            $theTable[$x][10] = "Comment";
            $theTable[$x][11] = 'Status';
            $theTable[$x][12] = 'ApprovedBy';
            $theTable[$x][13] = 'Reason';
            
            while($row = $result->fetch_assoc()) {
                $x++;
                $theTable[$x][0] = $row['RefNo'];
                $theTable[$x][1] = $row['Munis'];
                $theTable[$x][2] = $row['Name'];
                $theTable[$x][3] = $row['Used'];
                $theTable[$x][4] = $row['Start'];
                $theTable[$x][5] = $row['End'];
                $theTable[$x][6] = $row['Hrs'];
                $theTable[$x][7] = $row['Type'];
                $theTable[$x][8] = $row['Subtype'];
                $theTable[$x][9] = $row['Calloff'];
                $theTable[$x][10] = $row['Comment'];
                $theTable[$x][11] = $row['Status'];
                $theTable[$x][12] = $row['ApprovedBy'];
                $theTable[$x][13] = $row['Reason'];
            }
            showSortableTable($theTable, 1);
        }
        else{
            $myq = "SELECT REFER, MUNIS, LNAME,FNAME,R.IDNUM
                    FROM REQUEST R, EMPLOYEE E
                    WHERE R.IDNUM=E.IDNUM
                    AND USEDATE BETWEEN '". $startDate->format('Y-m-d')."' AND '".$endDate->format('Y-m-d')."'
                    AND (STATUS='APPROVED' OR STATUS='DENIED')
                    ORDER BY LNAME";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);

            $theTable = array(array());
            $x = 0;
            $theTable[$x][0] = "View";
            $theTable[$x][1] = "Munis #";
            $theTable[$x][2] = "Employee";
            $theTable[$x][3] = "Number of Requests";

            $lastUser = '';
            $lastUserRow = 0;
            $recordCounter = 0;

            while($row = $result->fetch_assoc()) {
                if(strcmp($lastUser, $row['LNAME'].', '.$row['FNAME'])==0){
                    $recordCounter++;
                    $theTable[$x][3] = $recordCounter;
                }
                else{
                    $x++;
                    $recordCounter = 1;
                    $lastUser = $row['LNAME'].', '.$row['FNAME'];

                    $theTable[$x][0] = '<form method="POST">
                        <input type="submit" name="viewDetailsBtn" value="View" />
                        <input type="hidden" name="empID" value="'.$row['IDNUM'].'" />
                        </form>';
                    $theTable[$x][1] = $row['MUNIS'];
                    $theTable[$x][2] = $lastUser;
                    $theTable[$x][3] = $recordCounter;

                }


            }//end While loop
            echo 'number of rows: '.$x;
            showSortableTable($theTable, 1);
        }
    }
    //show a print button. printed look defined by print.css
    echo '<a href="javascript:window.print()">Print</a>';
    
}
?>
