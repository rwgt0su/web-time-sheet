<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of request_reports
 *
 * @author aturner
 */
class request_reports {

    public $config;
    public $db;
    private $startDate;
    private $endDate;
    private $shiftTimeID;
    private $shiftStart;
    private $shiftEnd;
    private $request_class;
    public $filters;
    public $divisionID;

    public function request_reports($config = '') {
        $this->request_class = new request_class();
        $this->config = $config;
        $this->db = new request_db($this->config);
    }

    public function showTimeRequestsByDate($hiddenInputs, $showCustomDates = true, $showPayPeriods = true, $showDivisions = true) {
        echo "<form name='timeRequests' method='post'>";
        echo '<h2>Submitted Requests By Division and By Date</h2>';
        $requests = new request_class();
        if($showCustomDates)
            $this->showCustomDateRange();
        if($showPayPeriods)
            $this->getCurrentPayPeriods();
        if($showDivisions)
            $this->showDivisionDropDown();
        $this->filters .= getTimeRequestFiltersBetweenDates($this->config, $this->startDate, $this->endDate);
        //$filters .= " AND (STATUS='APPROVED' OR STATUS='DENIED')";
        $hiddenInputs = $this->setHiddenPostInputs();
        $requests->debug = false;
        $requests->showTimeRequestTable($this->config, $this->filters, $orderBy = "ORDER BY REFER DESC", $hiddenInputs);
    }
    public function showTimeRequestFilterOptions($showCustomDates = true, $showPayPeriods = true, $showDivisions = true){
         echo "<form name='timeRequests' method='post'>";
        echo '<h2>Submitted Requests By Division and By Date</h2>';        
        if($showCustomDates)
            $this->showCustomDateRange();
        if($showPayPeriods)
            $this->getCurrentPayPeriods();
        if($showDivisions)
            $this->showDivisionDropDown();
        $this->filters .= getTimeRequestFiltersBetweenDates($this->config, $this->startDate, $this->endDate);
        //$filters .= " AND (STATUS='APPROVED' OR STATUS='DENIED')";
        $hiddenInputs = $this->setHiddenPostInputs();
        $requests->debug = false;        
    }

    private function setHiddenPostInputs(){
        $hiddenInputs = '';
        if (isset($_POST['customDate']))
            $hiddenInputs .= '<input type="hidden" name="customDate" value="' . $_POST['customDate'] . '" />';
        if (isset($_POST['GoBtn']))
            $hiddenInputs .= '<input type="hidden" name="GoBtn" value="' . $_POST['GoBtn'] . '" />';
        if (isset($_POST['usePayPeriodBtn']))
            $hiddenInputs .= '<input type="hidden" name="usePayPeriodBtn" value="' . $_POST['usePayPeriodBtn'] . '" />';
        if (isset($_POST['ppOffset']))
            $hiddenInputs .= '<input type="hidden" name="ppOffset" value="' . $_POST['ppOffset'] . '" />';
        if (isset($_POST['start']))
            $hiddenInputs .= '<input type="hidden" name="start" value="' . $_POST['start'] . '" />';
        if (isset($_POST['end']))
            $hiddenInputs .= '<input type="hidden" name="end" value="' . $_POST['end'] . '" />';
        if (isset($_POST['divisionID']))
            $hiddenInputs .= '<input type="hidden" name="divisionID" value="' . $_POST['divisionID'] . '" />';
        if (isset($_POST['shiftTime']))
            $hiddenInputs .= '<input type="hidden" name="shiftTime" value="' . $_POST['shiftTime'] . '" />';
        
        return $hiddenInputs;
    }
    public function getCurrentPayPeriods() {
        if (!isset($_POST['customDate']) && !isset($_POST['GoBtn']) || isset($_POST['usePayPeriodBtn'])) {
            //what pay period are we currently in?
            $myq = getCurrentPayPeriod();
            $result = getQueryResult($this->config, $myq, $debug = false);

            $ppArray = $result->fetch_assoc();
            /* $ppOffset stands for the number of pay periods to adjust the query by 
             * relative to the current period
             */
            $ppOffset = isset($_POST['ppOffset']) ? $_POST['ppOffset'] : '0';
            if (isset($_POST['prevBtn']))
                $ppOffset--;
            elseif (isset($_POST['nextBtn']))
                $ppOffset++;

            $this->startDate = new DateTime($ppArray['PPBEG']);
            if ($ppOffset < 0) {
                //backward in time by $ppOffset number of periods
                $this->startDate->sub(new DateInterval("P" . (abs($ppOffset) * 14) . "D"));
            } else {
                //forward in time by $ppOffset number of periods
                $this->startDate->add(new DateInterval("P" . ($ppOffset * 14) . "D"));
            }
            $this->startDate = $this->startDate->format('Y-m-d');

            $this->endDate = new DateTime($ppArray['PPEND']);
            if ($ppOffset < 0) {
                //backward in time by $ppOffset number of periods
                $this->endDate->sub(new DateInterval("P" . (abs($ppOffset) * 14) . "D"));
            } else {
                //forward in time by $ppOffset number of periods
                $this->endDate->add(new DateInterval("P" . ($ppOffset * 14) . "D"));
            }
            $this->endDate = $this->endDate->format('Y-m-d');
            echo '<input type="hidden" name="ppOffset" value="' . $ppOffset . '" />';
            ?>      
            <p>
            <div style="float:left"><input type="submit" name="prevBtn" value="< Previous" /></div>        
            <div style="float:right"><input type="submit" name="nextBtn" value="Next >" /></div></p>
            <h3><center>Pay Period: <?php echo date('m-d-Y', strtotime($this->startDate)); ?> to <?php echo date('m-d-Y', strtotime($this->endDate)); ?>.</center></h3>

            <?php
        } else {
            
        }
    }

    public function showCustomDateRange() {
        if ((isset($_POST['customDate']) || isset($_POST['GoBtn'])) && !isset($_POST['usePayPeriodBtn'])) {
            echo '<h3><center><input type="submit" name="usePayPeriodBtn" value="Remove Custom Dates" /></center></h3><br/>';
            echo '<div align="center">Start';
            if (isset($_POST['start']) && isset($_POST['end'])) {
                $this->startDate = $_POST['start'];
                displayDateSelect('start', 'date_1', $this->startDate, false, false);
                echo " End";
                $this->endDate = $_POST['end'];
                displayDateSelect('end', 'date_2', $this->endDate, false, false);
            } else {
                displayDateSelect('start', 'date_1', false, false, true);
                echo " End";
                displayDateSelect('end', 'date_2', false, false, true);
            }
            echo '<input type="hidden" name="customDate" value="true" />';
            echo "<input type='submit' name='GoBtn' value='Go' /></div><br/>";
        } else {
            echo '<h2><center><input type="submit" name="customDate" value="Use Custom Date Range" /></center></h2>';
        }
    }

    public function showDivisionDropDown() {
        
        if ($this->config->adminLvl >= 25) {
            echo '<div align="center">Show for the following division: 
            <select name="divisionID" onchange="this.form.submit()">';

            if (isset($_POST['divisionID'])) {
                $myDivID = $_POST['divisionID'];
                popupmessage('isset '.$_POST['divisionID']);
            } else {
                if ($this->config->adminLvl >= 50) {
                    $myDivID = "All";
                } else {
                    $mydivq = "SELECT DIVISIONID FROM EMPLOYEE E WHERE E.IDNUM='" . $this->config->mysqli->real_escape_string($_SESSION['userIDnum']) . "'";
                    $myDivResult = getQueryResult($this->config, $mydivq, $debug = false);

                    $temp = $myDivResult->fetch_assoc();
                    $myDivID = $temp['DIVISIONID'];
                }
            }

            $alldivq = "SELECT * FROM `DIVISION`";
            $allDivResult = getQueryResult($this->config, $alldivq, $debug = false);
            while ($Divrow = $allDivResult->fetch_assoc()) {
                echo '<option value="' . $Divrow['DIVISIONID'] . '"';
                if ($Divrow['DIVISIONID'] == $myDivID)
                    echo ' SELECTED ';
                echo '>' . $Divrow['DESCR'] . '</option>';
            }
            if ($this->config->adminLvl >= 25) {
                if (strcmp($myDivID, "All") == 0)
                    echo '<option value="All" SELECTED>All</option>';
                else
                    echo '<option value="All">All</option>';
            }
            echo '</select><br/><br/>';
            if ($myDivID != "All")
                $this->showShiftDropDown($myDivID, $onChangeSubmit = true);
            echo '</div>';
            $this->divisionID = $myDivID;
            $this->filters .= getFilerDivision($this->config, $myDivID);

        }
    }

    public function showShiftDropDown($divID, $onchangeSubmit) {
        $shiftTimeID = '';

        if (isset($_POST['shiftTime'])) {
            $shiftTimeID = $this->config->mysqli->real_escape_string($_POST['shiftTime']);
        } else {
            $currentTime = time();
            $dayShiftStart = mktime(7, 00, 0);
            $nightShiftStart = mktime(19, 00, 0);

            if ($currentTime < $dayShiftStart || $currentTime >= $nightShiftStart) {
                //Between midnight and 0659 or 1900 and midnight
                $shiftTimeID = "2";
            } elseif ($currentTime >= $dayShiftStart && $currentTime < $nightShiftStart) {
                //Between 0700 and 1859
                $shiftTimeID = "1";
            }
        }

        //Get all possible values
        $myq = getShiftsByDivision($this->config, $divID);
        $result = getQueryResult($this->config, $myq, $debug = false);
        if ($result->num_rows > 0) {
            echo '<select name="shiftTime"';
            if ($onchangeSubmit)
                echo ' onchange="this.form.submit()" ';
            echo '>';

            while ($row = $result->fetch_assoc()) {
                if ($shiftTimeID == $row['IDNUM'])
                    echo '<option value="' . $row['IDNUM'] . '" SELECTED>' . $row['NAME'] . ' (' . $row['BEG_TIME'] . '-' . $row['END_TIME'] . ')</option>';
                else
                    echo '<option value="' . $row['IDNUM'] . '">' . $row['NAME'] . ' (' . $row['BEG_TIME'] . '-' . $row['END_TIME'] . ')</option>';
            }
            if ($shiftTimeID == "0")
                echo '<option value="0" SELECTED>All</option>';
            else
                echo '<option value="0">All</option>';

            echo '</select>';
        }
        $this->shiftTimeID = $shiftTimeID;
        $this->getShiftTimes();
    }

    private function getShiftTimes() {
        if(!empty($this->shiftTimeID)) {
            $myq = getShiftsByID($this->config, $this->shiftTimeID);
            $result = getQueryResult($this->config, $myq, $debug = false);
            $row = $result->fetch_assoc();
            $this->shiftStart = $row['BEG_TIME'];
            $this->shiftEnd = $row['END_TIME'];
            
            if (strtotime($this->shiftStart) > strtotime($this->shiftEnd)){
                //This time crosses past midnight
                $this->filters .= getReqestsPastMidightTimes($this->shiftStart, $this->shiftEnd);
            }else{
                $this->filters .= getReqestsBetweenTimes($this->shiftStart, $this->shiftEnd);
            }
        }
    }

}
?>
