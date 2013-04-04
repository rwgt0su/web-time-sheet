<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of request_form
 *
 * @author aturner
 */
class time_request_form {

    //put your code here
    public $config;
    private $db;
    private $isShowWhyRequestForm;
    private $isShowHowRequestForm;
    private $isShowMainRequestForm;
    private $isShowAreYouSureMessage;
    private $isShowEmpSelectionForm;
    //POST Var
    public $reqID;
    public $empID;
    private $empName;
    public $subTypeID;
    private $subTypeInfo;
    private $maxCalDays;
    public $typeID;
    public $useDate;
    public $endDate;
    private $daysOff;
    public $begTime1;
    public $begTime2;
    public $endTime1;
    public $endTime2;
    public $empComment;
    private $reason;
    private $shiftHourRadio;
    public $hrNotes;
    private $status;
    //other Var
    private $isEditing;
    private $lastPayStart;
    private $lastPayEnd;

    public function time_request_form($config) {
        $this->config = $config;
        $this->db = new request_db($this->config);
        
        $this->empID = $_SESSION['userIDnum'];

        $this->subTypeID = '';
        $this->maxCalDays = 0;
        $this->TypeID = '';
        $this->useDate = '';
        $this->endDate = 0;
        $this->daysOff = 0;
        $this->begTime1 = '';
        $this->begTime2 = '';
        $this->endTime1 = '';
        $this->endTime2 = '';
        $this->empComment = '';
        $this->reason = '';
        $this->hrNotes = '';
        $this->status = '';
        $this->isEditing = false;
        $this->isShowEmpSelectionForm = true;
    }

    public function showTimeRequestForm($reqID = '') {
        echo '<h1><center>EMPLOYEE TIME REQUEST FORM</center></h1>';
       
        if (!empty($reqID)) {
            $this->reqID = $reqID;
            $this->getRequestByID();
            $this->isEditing = true;
            if (!isset($_POST['duplicateReqBtn']))
                echo 'Editing Request #' . $this->reqID . '<br/>';
        }
        $this->getRequestFormPOSTS();

        if ($this->isShowEmpSelectionForm) {
            $this->showEmpSelectionForm();
        }
        if ($this->isShowWhyRequestForm) {
            $this->showWhyRequestForm();
        }
        if ($this->isShowHowRequestForm) {
            $this->showHowRequestForm();
        }
        if ($this->isShowMainRequestForm) {
            $this->showMainRequestForm();
        }
        echo '</form>';
        $this->showAreYouSureMessage();
    }

    private function showWhyRequestForm() {
        //Why are you requesting time?
        $this->config->showPrinterFriendly = false;
        echo '<h2>Why are you requesting time?</h2>';
        $this->showTimeTypeDropDown();
        echo '<br/><br/><br/>';
    }

    private function showHowRequestForm() {
        //How would you like to use this time
        $this->config->showPrinterFriendly = false;
        echo '<h2>How would you like to use this time?</h2>';
        $this->showSubTimeTypeDropDown();
        echo '<input type="hidden" name="maxCalDays" value="' . $this->maxCalDays . '" />';
        echo '<br/><br/><br/>';
    }

    public function showMainRequestForm() {
        //Show all available remaining options
        $this->config->showPrinterFriendly = true;
        echo '<h2>Complete additional fields</h2>';
        echo 'Starting Date: ';
        displayDateSelect('useDate', 'date_1', $this->useDate, true, true);
        if(!$this->isEditing){
            echo ' Through date (optional): ';
            displayDateSelect('endDate', 'date_2', $this->endDate);
        } else{
            echo '<input type="hidden" name="endDate" value="" />';
        }
        echo '<br/><br/>';
        echo 'Start time: ';
        showTimeSelector("begTime", $this->begTime1, $this->begTime2);
        if ($this->subTypeInfo['LIMIT_8_12'] == '1') {
            if (!empty($this->shiftHours)) {
                if ($this->shiftHourRadio == "8" || $this->shiftHours == "8") {
                    echo " How long is your shift? <input type='radio' name='shiftHour' value='8' CHECKED>8 Hours";
                    echo "<input type='radio' name='shiftHour' value='12'>12 Hours<br/>";
                } elseif ($this->shiftHourRadio == "12" || $this->shiftHours == "12") {
                    echo " How long is your shift? <input type='radio' name='shiftHour' value='8'>8 Hours";
                    echo "<input type='radio' name='shiftHour' value='12' CHECKED>12 Hours<br/>";
                } else {
                    echo " How long is your shift? <input type='radio' name='shiftHour' value='8'>8 Hours";
                    echo "<input type='radio' name='shiftHour' value='12'>12 Hours";
                    echo ' <font color="red">Error in shift selection! </font><br/>';
                }
            } else {
                echo " How long is your shift? <input type='radio' name='shiftHour' value='8'>8 Hours";
                echo "<input type='radio' name='shiftHour' value='12'>12 Hours<br/>";
            }
        } else {
            echo ' End time: ';
            showTimeSelector("endTime", $this->endTime1, $this->endTime2);
        }
        if (!empty($this->shiftHours))
            echo ' Total Hours: ' . $this->shiftHours;

        echo '<br/><br/>';
        echo 'Comment: <textarea rows="3" cols="40" name="empComment" >' . $this->empComment . '</textarea>';
        echo '<br/><br/>';

        if (!$this->isEditing) {
            echo '<input type="submit" name="submitBtn" value="Submit for Approval">';
        } else {
            if($this->status != "APPROVED")
                echo '<input type="submit" name="updateReqBtn" value="Update Request ' . $this->reqID . '">';
            echo '<input type="submit" name="duplicateReqBtn" value="Duplicate Request" />';
        }
    }

    private function getRequestByID() {
        $this->db->filters = '';
        $result = $this->db->getTimeRequestByID($this->reqID);
        $reqInfo = $result->fetch_assoc();
        $this->subTypeID = $reqInfo['TIMETYPES_ID'];
        $this->typeID = $reqInfo['SUBTYPE_ID'];
        $this->useDate = $reqInfo['UsedReqForm'];
        $this->begTime1 = $reqInfo['Start1'];
        $this->begTime2 = $reqInfo['Start2'];
        $this->endTime1 = $reqInfo['End1'];
        $this->endTime2 = $reqInfo['End2'];
        $this->shiftHours = $this->calculateHours($this->begTime1, $this->begTime2, $this->endTime1, $this->endTime2);
        $this->empComment = $reqInfo['Comment'];
        $this->status = $reqInfo['Status'];
        $this->db->filters = " AND IDNUM = '" . $this->subTypeID . "' ";
        $subTypeInfo = $this->db->getTimeTypes();
        $this->subTypeInfo = $subTypeInfo->fetch_assoc();
        $this->maxCalDays = $this->subTypeInfo['CALENDAR_LIMIT_DAYS'];
    }

    private function getRequestFormPOSTS() {
        //forms
        $this->isShowWhyRequestForm = true;
        $this->isShowHowRequestForm = false;
        $this->isShowMainRequestForm = false;

        //variables
        $this->empID = isset($_POST['empID']) ? $_POST['empID'] : $this->empID;
        $this->subTypeID = isset($_POST['subTypeID']) ? $_POST['subTypeID'] : $this->subTypeID;
        $this->typeID = isset($_POST['typeID']) ? $_POST['typeID'] : $this->typeID;
        $this->useDate = isset($_POST['useDate']) ? $_POST['useDate'] : $this->useDate;
        $this->endDate = isset($_POST['endDate']) ? $_POST['endDate'] : $this->endDate;
        $this->begTime1 = isset($_POST['begTime1']) ? $_POST['begTime1'] : $this->begTime1;
        $this->begTime2 = isset($_POST['begTime2']) ? $_POST['begTime2'] : $this->begTime2;
        $this->endTime1 = isset($_POST['endTime1']) ? $_POST['endTime1'] : $this->endTime1;
        $this->endTime2 = isset($_POST['endTime2']) ? $_POST['endTime2'] : $this->endTime2;
        $this->empComment = isset($_POST['empComment']) ? $_POST['empComment'] : $this->empComment;
        $this->shiftHourRadio = isset($_POST['shiftHour']) ? $_POST['shiftHour'] : $this->shiftHourRadio;
        $oldSubTypeID = isset($_POST['$oldSubTypeID']) ? $_POST['$oldSubTypeID'] : '';
        $this->maxCalDays = isset($_POST['maxCalDays']) ? $_POST['maxCalDays'] : $this->maxCalDays;


        //buttons
        $submitBtn = isset($_POST['submitBtn']) ? true : false;
        $updateReqBtn = isset($_POST['updateReqBtn']) ? true : false;
        $duplicateReqBtn = isset($_POST['duplicateReqBtn']) ? true : false;
        $reqFormEmpSearch = isset($_POST['reqFormEmpSearch']) ? true : false;

        //Multiple Day submitions        
        if (isset($_POST['endDate'])) {
            if (!empty($_POST['endDate'])) {
                //number days in given range
                $daysOffInterval = abs(strtotime($this->endDate) - strtotime($this->useDate));
                $this->daysOff = date('j', $daysOffInterval);
            }
        }

        //Hours calculation
        if (isset($_POST['shiftHour'])) {
            if ($_POST['shiftHour'] == "8") {
                //add 8 hours to begTime1            
                $this->endTime1 = $this->begTime1 + 8;
                $this->endTime2 = $this->begTime2;
            } elseif ($_POST['shiftHour'] == "12") {
                //add 8 hours to begTime1
                $this->endTime1 = $this->begTime1 + 12;
                $this->endTime2 = $this->begTime2;
            }
        }
        //Get SubType Info
        if (!empty($this->subTypeID)) {
            $this->db->filters = " AND IDNUM = '" . $this->subTypeID . "'";
            $subTypeInfo = $this->db->getTimeTypes();
            $this->subTypeInfo = $subTypeInfo->fetch_assoc();
            $this->maxCalDays = $this->subTypeInfo['CALENDAR_LIMIT_DAYS'];
        }

        if ($submitBtn || $updateReqBtn || $duplicateReqBtn)
            $this->shiftHours = $this->calculateHours($this->begTime1, $this->begTime2, $this->endTime1, $this->endTime2);
        if ($reqFormEmpSearch) {
            //Lookup Employee
            $Employees = new Employee($this->config);
            $this->empID = false;
            $this->empID = $Employees->displayUserLookup();
            if (!$this->empID) {
                echo '<input type="hidden" name="reqFormEmpSearch" value="true" />';
                $this->isShowEmpSelectionForm = false;
                $this->isShowWhyRequestForm = false;
                $this->isShowHowRequestForm = false;
                $this->isShowMainRequestForm = false;
            } else {
                //user selected.  Continue on normally 
            }
        }
        if (!empty($this->subTypeID)) {
            echo '<input type="hidden" name="oldSubTypeID" value ="' . $this->subTypeID . '" />';
            $this->isShowWhyRequestForm = true;
            if (isset($_POST['$oldSubTypeID']) && $this->subTypeID == $oldSubTypeID) {
                $this->isShowHowRequestForm = true;
            } elseif (!isset($_POST['$oldSubTypeID'])) {
                $this->isShowHowRequestForm = true;
            } else {
                $this->isShowMainRequestForm = false;
            }
        }
        if (!empty($this->typeID)) {
            $this->isShowWhyRequestForm = true;
            $this->isShowHowRequestForm = true;
            $this->isShowMainRequestForm = true;
        }
        if ($submitBtn) {
            $this->isEditing = $this->showSubmitNewRequest();
        }
        if ($updateReqBtn) {
            $this->isEditing = $this->updateRequest();
            $this->isEditing = true;
        }
        if ($duplicateReqBtn) {
            $this->isEditing = false;
        }
    }

    private function showEmpSelectionForm() {
        if (empty($this->empID))
            $this->empID = $_SESSION['userIDnum'];
        $empq = getEmployeeInfo($this->config, $this->empID);
        $empInfo = getQueryResult($this->config, $empq, $debug = false);
        $emp = $empInfo->fetch_assoc();
        $this->empName = $emp['Name'];
        echo '<h3>Submission for Employee: <font color="yellow">' . $this->empName . '</font>';
        if ($this->config->adminLvl >= 25) {
            //echo ' Lookup Employee... work in progress<br/>';
            if (empty($this->subTypeID))
                echo ' <br/><br/><input type="submit" name="reqFormEmpSearch" value="Submit For Employee" />';
            echo '<input type="hidden" name="empID" value="' . $this->empID . '" /><br/><br/>';
        }
        echo '</h3>';
    }

    private function showTimeTypeDropDown() {
        $this->db->filters = '';
        $results = $this->db->getTimeTypes();
        echo '<select name="subTypeID" onChange="this.form.submit();">';
        echo '<option value=""></option>';

        while ($row = $results->fetch_assoc()) {
            if ($row['ELEVATE_SUPERVISORS'] == "1" && $this->config->adminLvl >= 25)
                $friendlyName = $row['DESCR'] . ' (Admin Approves)';
            else
                $friendlyName = $row['DESCR'];
            if ($row['ELEVATE_SUPERVISORS'] != "1" || ($row['ELEVATE_SUPERVISORS'] == "1" && $this->config->adminLvl >= 25)) {
                if ($this->subTypeID == $row['IDNUM']) {
                    echo '<option value="' . $row['IDNUM'] . '" SELECTED>' . $friendlyName . '</option>';
                } else {
                    echo '<option value="' . $row['IDNUM'] . '">' . $friendlyName . '</option>';
                }
            }
        }
        echo '</select>';
    }

    private function showSubTimeTypeDropDown() {
        if (!empty($this->subTypeID))
            $this->db->filters = " AND IDNUM = '" . $this->subTypeID . "' ";
        $subTypeInfo = $this->db->getTimeTypes();
        $this->subTypeInfo = $subTypeInfo->fetch_assoc();
        $results = $this->db->getSubTimeTypes($this->subTypeInfo);

        $echo = '';
        $selectedID = '';
        $foundSelected = false;

        while ($row = $results->fetch_assoc()) {
            if ($this->subTypeID == "8" || $this->subTypeID == "19") {
                //Military or Injured on Duty
                if ($row['DESCR'] == "STRAIGHT") {
                    $friendlyName = $row['DESCR'] . ' (Max ' . $this->subTypeInfo['CALENDAR_LIMIT_DAYS'] . ' Days)';
                    $this->maxCalDays = $this->subTypeInfo['CALENDAR_LIMIT_DAYS'];
                }
                else
                    $friendlyName = $row['DESCR'];
            }
            elseif ($this->subTypeInfo['CALENDAR_LIMIT_DAYS'] > 0) {
                $friendlyName = $row['DESCR'] . ' (Max ' . $this->subTypeInfo['CALENDAR_LIMIT_DAYS'] . ' Days)';
                $this->maxCalDays = $this->subTypeInfo['CALENDAR_LIMIT_DAYS'];
            }
            else
                $friendlyName = $row['DESCR'];

            if ($this->typeID == $row['IDNUM'] || $results->num_rows == 1) {
                $selectedID = $row['IDNUM'];
                $echo .= '<option value="' . $selectedID . '" SELECTED>' . $friendlyName . '</option>';
                $foundSelected = true;
            } else {
                $echo .= '<option value="' . $row['IDNUM'] . '">' . $friendlyName . '</option>';
            }
        }


        if ($results->num_rows == 1) {
            echo '<input type="hidden" name="typeID" value="' . $selectedID . '" />';
            echo '<select name="typeID" disabled>';
            $this->isShowMainRequestForm = true;
        } else {
            if (!$foundSelected) {
                $this->isShowMainRequestForm = false;
            }
            echo '<select name="typeID" onChange="this.form.submit();">';
            echo '<option value=""></option>';
        }
        echo $echo;
        echo '</select>';
    }

    private function calculateHours($startHour, $startMin, $endHour, $endMin) {
        if (empty($startHour) || empty($startMin) || empty($endHour) || empty($endMin)) {
            //Not accepting empty inputs
            $hours = -1;
        } else {
            $startH = date("H", strtotime($startHour . "0000"));
            $startM = date("i", strtotime("00" . $startMin . "00"));
            $endH = date("H", strtotime($endHour . "0000"));
            $endM = date("i", strtotime("00" . $endMin . "00"));

            $hours = 0;
            $tempH = 0;
            $tempM = 0;

            if ($startH > $endH) {
                //crosses midnight 
                //Hours accumilated to midnight
                $tempH = 23 - $startH;
                $tempM = 60 - $startM;

                //Add to ending hours
                $tempH = $tempH + $endH;
                $tempM = $tempM + $endM;
                //convert min to hours
                $tempM = $tempM / 60;
                $hours = $tempH + $tempM;
            } else {
                $tempH = $endH - $startH;
                $tempM = $endM - $startM;
                //convert min to hours
                $tempM = $tempM / 60;
                $hours = $tempH + $tempM;
            }
        }
        return $hours;
    }

    private function showSubmitNewRequest() {
        $submitForDate = $this->useDate;
        $didSubmit = false;
        for ($i = 0; $i <= $this->daysOff; $i++) {
            $noErrors = true;
            $submitForDate = strtotime($submitForDate);
            $noErrors = $this->checkSubmissionErrors($submitForDate);
            if ($noErrors) {
                $this->reqID = $this->submitNewTimeRequest($submitForDate);
                addLog($this->config, 'Request ' . $this->reqID . ' Submitted');
                echo '<h3>Request accepted. The reference number for this request is <font color="red"><b>'
                . $this->reqID . '</b></font>.</h3>';
                $didSubmit = true;
            }
            //add one more day for the next iteration if multiple days off
            $oldDay = date('Y-m-d', $submitForDate);
            $submitForDate = date('Y-m-d', strtotime('+1 day', strtotime($oldDay)));
        }
        if ($didSubmit) {
            //Get results of last submission
            $this->getRequestByID();
        }
        return $didSubmit;
    }

    private function showAreYouSureMessage() {
        if ($this->isShowAreYouSureMessage) {
            popUpMessage('<div align="center"><form method="POST" name="areYouSure">                    
                           ' . $this->reason . '<br/><br/><h4>Are you sure you want to submit another?</h4>
                                <input type="submit" name="confirmBtn" value="Yes" /> 
                                <input type="submit" name="noBtn" value="No" />
                                <input type="hidden" name="typeID" value="' . $this->typeID . '" />
                                <input type="hidden" name="subTypeID" value="' . $this->subTypeID . '" />
                                <input type="hidden" name="empID" value="' . $this->empID . '" />
                                <input type="hidden" name="useDate" value="' . $this->useDate . '" />
                                <input type="hidden" name="endDate" value="' . $this->endDate . '" />
                                <input type="hidden" name="begTime1" value="' . $this->begTime1 . '" />
                                <input type="hidden" name="begTime2" value="' . $this->begTime2 . '" />
                                <input type="hidden" name="endTime1" value="' . $this->endTime1 . '" />
                                <input type="hidden" name="endTime2" value="' . $this->endTime2 . '" />
                                <input type="hidden" name="empComment" value="' . $this->empComment . '" />
                                    <input type="hidden" name="shiftHour" value="' . $this->shiftHourRadio . '" />
                                <input type="hidden" name="submitBtn" value="true" />
                                </form></div>');
        }
    }

    private function checkForInvalidRequests() {
        $noErrors = true;

        //prep work for any checks
        $this->getLastPayPeriodEnd();
        //popupmessage('cutoff '.$this->lastPayEnd.' '.date('Y-m-d', strtotime($this->lastPayEnd)));
        if (strtotime($this->useDate) <= strtotime($this->lastPayEnd)) {
            //Date requesting is older than last pay period
            //popupmessage('Date is older than last pay period');
            if(strtotime($this->lastPayStart) <= strtotime($this->useDate) && strtotime($this->useDate) <= strtotime($this->lastPayEnd)){
                //Date requesting is part of last pay period
                //popupmessage('Date requesting is part of last pay period');
                $today = strtotime(date('Y-m-d'));
                $cutOff = date('Y-m-d', strtotime('+5 days', strtotime($this->lastPayEnd)));
                if($today > strtotime($cutOff)){
                    //Date is past cut off period
                    //popupmessage('Date is Not Allowed past cut off period');
                    if ($this->config->adminLvl >= 25) {
                        //supervisors allowed to submit
                        popupmessage('<font color="red">Requested Date is past the cut off period<Br/>
                            Please contact Laura after submitting request</font>');
                    } else {
                        popupmessage('<font color="red">Requested Date is past the cut off period<Br/>
                            Please see your supervisor to submit this request</font>');
                        $noErrors = false;
                    }
                }
            } else{
                //Date is much older than last payperiod
                if ($this->config->adminLvl >= 25) {
                    popupmessage('<div align="center"><font color="red">Requested Date is much older than last pay period<Br/>
                        Please contact Laura after submitting request</font></div>');
                        //supervisors allowed to submit
                } else {
                    popupmessage('<font color="red">Requested Date is much older than last pay period<Br/>
                        Please see your supervisor to submit this request</font>');
                    $noErrors = false;
                }
            }
        }

        if(!$noErrors){
            //An error was found
            //Do not continue any more checks
        }
        elseif (empty($this->useDate)) {
            //use date required
            echo '<font color="red">Must have a Starting Date</font>';
            $noErrors = false;
        }
        elseif (empty($this->begTime1) || empty($this->begTime2)) {
            //start time required
            echo '<font color="red">Must have a start time</font>';
            $noErrors = false;
        } elseif (empty($this->endTime1) || empty($this->endTime2)) {
            //No end time
            if (!isset($_POST['shift8Hour']) && !isset($_POST['shift12Hour'])) {
                //No shift Length
                echo '<font color="red">Must have an end time</font>';
                $noErrors = false;
            }
        } elseif (($this->typeID == '5' || $this->typeID == '6')) {
            //Is Overtime Pay or AT Gain
            if (strtotime(date('Y-m-d', strtotime($this->useDate))) > strtotime(date('Y-m-d'))) {
                //Current Date must be less than the submitted date
                echo '<font color="red">Can not submit for Overtime Pay or AT Gain unless it is on or after the date of use</font>';
                $noErrors = false;
            }
        } elseif ($this->maxCalDays > 0) {
            //Check for Max Calendar Days for this type of request
            $yearBegin = date('Y-m-d', strtotime('01/01/' . date('Y')));
            $yearEnd = date('Y-m-d', strtotime('12/31/' . date('Y')));
            $this->db->filters = $this->db->getTimeRequestFiltersBetweenDates($yearBegin, $yearEnd);
            $this->db->filters .= $this->db->getTimeRequestFiltersByEmpID($this->empID);
            if ($this->subTypeID == '8' || $this->subTypeID == '19') {
                //Military and injured on duty max limit is on straight time only
                $this->db->filters .= $this->db->getTimeRequestFiltersBySubType($this->typeID);
            } else {
                $this->db->filters .= $this->db->getTimeRequestFiltersByType($this->subTypeID);
            }
            $results = $this->db->getTimeRequestByID();
            $this->db->filters = '';
            if ($results->num_rows > $this->maxCalDays) {
                $noErrors = false;
                $dupRefNo = '';
                while ($dupRef = $results->fetch_assoc()) {
                    $dupRefNo .= '<br/>' . $dupRef['RefNo'];
                }
                if (isset($_POST['noBtn'])) {
                    //Do Nothing, leave all left behind info
                } elseif (!isset($_POST['confirmBtn'])) {
                    $this->isShowAreYouSureMessage = true;
                    $this->reason .= '<font color="red"> You\'ve exceeded the ' . $this->maxCalDays . ' day limit
                        for this type of request this year with ' . $results->num_rows . ' request</font><br/>
                            Please see Reference Numbers: <br/>' . $dupRefNo;
                } elseif (isset($_POST['confirmBtn'])) {
                    $this->hrNotes = 'User exceeded the ' . $this->maxCalDays . ' day limit
                        for this type of request this year with ' . $results->num_rows . ' request';
                    $noErrors = true;
                }
            }
        }

        return $noErrors;
    }

    private function submitNewTimeRequest($submitForDate) {
        $refInsert = false;
        //popupmessage('empid'.$this->empID);
        $result = $this->db->getAddNewRequest($this, $submitForDate);
        //echo $myq; //DEBUG
        //show SQL error msg if query failed
        if (!$result) {
            echo 'Request not accepted.';
        } else {
            $refInsert = $this->config->mysqli->insert_id;
            addLog($this->config, 'New Time Request Submitted with Ref# ' . $refInsert);
        }
        return $refInsert;
    }

    private function updateRequest() {
        $submitForDate = strtotime($this->useDate);
        $noErrors = $this->checkSubmissionErrors($submitForDate);
        if($noErrors){
            $result = $this->db->getUpdateRequestByID($this->reqID, $this->useDate,
                    $this->begTime1 . $this->begTime2, $this->endTime1 . $this->endTime2,
                    $this->shiftHours, $this->subTypeID, $this->typeID, $this->empComment);
            if (!$result)
                echo '<br/>Unable to Update Request, please try again!<br/>';
            else{
                echo '<br/>Request #' . $this->reqID . ' has been updated<br/><br/>';
                addLog($this->config, 'Request #' . $this->reqID . ' Updated');
            }
        }
        $this->getRequestByID();
    }

    private function checkSubmissionErrors($submitForDate) {
        $noErrors = true;
        //check other invalid entries
        if ($noErrors) {
            $noErrors = $this->checkForInvalidRequests();
        }
        if ($noErrors) {
            //Check for duplicate submission
            $this->db->filters = $this->db->getFilterRequestTypeForDateByEmp($this->empID, $this->subTypeID, date('Y-m-d', $submitForDate));
            $results = $this->db->getTimeRequestByID();
            if ($results->num_rows > 0) {
                $noErrors = false;
                $noDuplicatesFound = false;
                $dupRefNo = '';
                while ($dupRef = $results->fetch_assoc()) {
                    if($this->reqID == $dupRef['RefNo']){
                        //Attempting to Update current request for same day
                        $noDuplicatesFound = true;
                        break;
                    } else{                        
                        $dupRefNo .= '<br/>' . $dupRef['RefNo'];
                    }
                }
                if($noDuplicatesFound){
                    $noErrors = true;
                }
                elseif (isset($_POST['noBtn'])) {
                    //Do Nothing, leave all left behind info
                } elseif (!isset($_POST['confirmBtn'])) {
                    $this->isShowAreYouSureMessage = true;
                    $this->reason .= '<font color="red"> You already submitted for this type of request for
                            ' . date('Y-m-d', $submitForDate) . '</font><br/>
                                Please see Reference Numbers: <br/>' . $dupRefNo;
                } elseif (isset($_POST['confirmBtn'])) {
                    $noErrors = true;
                }
            }
        }
        return $noErrors;
    }
    private function getLastPayPeriodEnd(){
        //what pay period are we currently in?
        $myq = getCurrentPayPeriod();
        $result = getQueryResult($this->config, $myq, $debug = false);
        $ppArray = $result->fetch_assoc();
        $startDate = $ppArray['PPBEG'];
        $this->lastPayStart = date('Y-m-d', strtotime('-14 days', strtotime($startDate)));
        $endDate = $ppArray['PPEND'];
        $this->lastPayEnd = date('Y-m-d', strtotime('-14 days', strtotime($endDate)));

        return $endDate;
    }

}

//end of class
?>
