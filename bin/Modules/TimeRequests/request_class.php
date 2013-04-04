<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of request_db
 *
 * @author aturner
 */
class request_class {

//db related
    public $config;
    private $currentRow;
    private $currentQuery;
    private $currentFilters;
    private $btnPushed;
    private $refNo;
    private $hrReason;
    private $supReason;
    private $toSendToPendingIndex;
    private $toSendToPendingTotalRows;
    private $isSendToPending;
    private $toExpungeRefNo;
    private $toExpungeIndex;
    private $toExpungeTotalRows;
    private $toExpunge;
    private $toUnExpunge;
    public $debug;
    private $hiddenInput;
    private $prevNum;
    private $nextNum;
    private $limit;
    private $currentLimit;
    private $currentTable;
    private $isShowTable;

    public function request_class() {
        $this->config = '';
        $this->currentRow = 0;
        $this->currentQuery = '';
        $this->currentFilters = '';

        $this->btnPushed = FALSE;
        $this->refNo = '';
        $this->hrReason = '';
        $this->supReason = '';
        $this->isSendToPending = false;
        $this->toExpungeRefNo = '';
        $this->toExpungeIndex = '';
        $this->toSendToPendingTotalRows = '';
        $this->toExpunge = FALSE;
        $this->toUnExpunge = FALSE;
        $this->debug = false;
        $this->isShowTable = true;
    }

    public function showTimeRequestTable($config, $filters, $orderBy = "ORDER BY REFER DESC", $hiddenInput = '') {
        echo '<input type="hidden" name="formName" value="submittedRequests" />';
        $this->config = $config;
        $this->currentFilters = $filters;
        $this->hiddenInput = $hiddenInput;
        $this->handlePOSTVariables();
        if($this->isShowTable){
            if ($this->config->adminLvl < 25) {
                //users only allowed to view own reference numbers
                $this->currentFilters = getFilterLoggedInUserRequestID($this->config);
            }
            $this->currentLimit = $this->getCurrentPageLimits();
            $this->currentQuery = getTimeRequestTable($this->config, $this->currentFilters, $orderBy, $this->currentLimit);
            $this->prepareTimeTable();
            $this->showPageLimitOptions();
            $this->showSortingOptions();
            $this->showTable();

            if ($this->isSendToPending) {
                echo '</form>';
                $this->hiddenInput .= '<input type="hidden" name="timeRequestTableRows" value="2" />
                        <input type="hidden" name="pendingBtn1" value="true" />
                        <input type="hidden" name="refNo1" value="' . $this->refNo . '" />
                        ';
                $this->sendRequestToPending($this->hiddenInput);
            }

            if ($this->toExpunge) {
                echo '</form>';
                $this->hiddenInput .= '<input type="hidden" name="timeRequestTableRows" value="2" />
                        <input type="hidden" name="expungeBtn1" value="true" />
                        <input type="hidden" name="refNo1" value="' . $this->toExpungeRefNo . '" />
                        ';
                $this->expungeRequest($this->hiddenInput);
            }
        }
    }

    private function handlePOSTVariables() {
        if (isset($_POST['timeRequestTableRows'])) {
            $totalRows = $_POST['timeRequestTableRows'];
            $this->btnPushed = false;
            for ($i = 0; $i <= $totalRows; $i++) {
                if (isset($_POST['pendingBtn' . $i])) {
                    $this->refNo = $_POST['refNo' . $i];
                    $this->hrNotes = isset($_POST['hrReason' . $i]) ? $_POST['hrReason' . $i] : '';
                    $this->toSendToPendingIndex = $i;
                    $this->toSendToPendingTotalRows = $totalRows;
                    $this->isSendToPending = true;
                    $this->btnPushed = true;
                } elseif (isset($_POST['approve' . $i])) {
                    $this->supReason = isset($_POST['reason' . $i]) ? $_POST['reason' . $i] : '';
                    $this->refNo = $_POST['refNo' . $i];
                    $this->approveLeaveRequest("APPROVED");
                    $this->showPrintFriendlyRedirect();
                    $this->btnPushed = true;
                } elseif (isset($_POST['deny' . $i])) {
                    $this->refNo = $_POST['refNo' . $i];
                    $this->supReason = $_POST['reason' . $i];
                    $this->approveLeaveRequest("DENIED");
                    $this->btnPushed = true;
                } elseif (isset($_POST['hrApproveBtn' . $i])) {
                    $this->hrNotes = isset($_POST['hrReason' . $i]) ? $_POST['hrReason' . $i] : isset($_POST['hrOldNotes' . $i]) ? $_POST['hrOldNotes' . $i] : '';
                    $this->refNo = $_POST['refNo' . $i];
                    $this->hrApproveLeaveRequest();
                    $this->btnPushed = true;
                } elseif (isset($_POST['expungeBtn' . $i]) || isset($_POST['unExpungeBtn' . $i])) {
                    $this->toExpungeRefNo = $_POST['refNo' . $i];
                    $this->toExpungeIndex = $i;
                    $this->toExpungeTotalRows = $totalRows;
                    $this->toExpunge = true;
                    $this->toUnExpunge = false;
                    if (isset($_POST['unExpungeBtn' . $i]))
                        $this->toUnExpunge = true;
                    $this->btnPushed = true;
                }
                elseif(isset($_POST['editBtn'.$i]) && !isset($_POST['cancelReqForm'])){
                    echo '<br/><center><input type="hidden" name="editBtn'.$i.'" value="true" />';
                    echo '<input type="hidden" name="timeRequestTableRows" value="'.$totalRows.'" />';
                    echo '<input type="hidden" name="requestID'.$i.'" value="'.$_POST['requestID'.$i].'" />';
                    echo '<input type="submit" name="cancelReqForm" value="Cancel Editing" /></center>';
                    $requestForm = new time_request_form($this->config);
                    $requestForm->showTimeRequestForm($_POST['requestID'.$i]);
                    $this->isShowTable = false;
                }
                if ($this->btnPushed) {
                    $this->config->anchorID = "editBtn" . $i;
//goToAnchor("editBtn" . $i);
                    break;
                }
            }
        }
    }

    private function getCurrentPageLimits() {
//Page Breaks Setup
        $this->prevNum = isset($_POST['prevNum']) ? $_POST['prevNum'] : "0";
        $this->nextNum = isset($_POST['nextNum']) ? $_POST['nextNum'] : "25";
        $this->limit = isset($_POST['limit']) ? $_POST['limit'] : "25";

        if (isset($_POST['prevLimitBtn'])) {
//$this->prevNum = $this->prevNum - $this->limit;
            $this->nextNum = $this->nextNum - $this->limit;
        }
        if (isset($_POST['nextLimitBtn'])) {
            $this->prevNum = $this->prevNum + $this->limit;
            $this->nextNum = $this->nextNum + $this->limit;
        }

        $this->hiddenInput .= '<input type="hidden" name="prevNum" value="' . $this->prevNum . '" />';
        $this->nextNum .= '<input type="hidden" name="nextNum" value="' . $this->nextNum . '" />';
        $this->hiddenInput .= '<input type="hidden" name="limit" value="' . $this->limit . '" />';

        return getLimitFilter($this->config, $this->prevNum, $this->limit);
    }

    private function showPageLimitOptions() {
        echo '<hr />';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $lastRec = $this->prevNum + $this->limit;
        echo '<br/>';
        echo 'Showing Records ' . $this->prevNum . ' to ' . $lastRec;
//Spacing characters
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        if (!$this->prevNum > 0) {
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        echo 'Records: <select name="limit" onChange="this.form.submit()" >
            <option value="25"';
        if (strcmp($this->limit, "25") == 0)
            echo ' SELECTED';
        echo '>25</option>
            <option value="50"';
        if (strcmp($this->limit, "50") == 0)
            echo ' SELECTED';
        echo '>50</option>
            </select>';
        if ($this->prevNum > 0)
            echo '<input type="submit" name="prevLimitBtn" value="Previous" />';
        if ($this->limit <= $this->currentRow)
            echo '<input type="submit" name="nextLimitBtn" value="Next" />';
        echo '<br/>';
    }

    private function showSortingOptions() {
        echo '<br/><h3><center>Ordered from newest request to oldest request</center></h3>';
    }

    private function prepareTimeTable() {
        $result = getQueryResult($this->config, $this->currentQuery, $this->debug);
        $theTable = array(array());
        $this->currentRow = 0;
        $y = 0;

        $theTable[$this->currentRow][$y] = "Actions";
        $y++;
        $theTable[$this->currentRow][$y] = "Ref#";
        $y++;
        $theTable[$this->currentRow][$y] = "Employee";
        $y++;
        $theTable[$this->currentRow][$y] = "Date_of_Use";
        $y++;
        $theTable[$this->currentRow][$y] = "Start Time";
        $y++;
        $theTable[$this->currentRow][$y] = "End Time";
        $y++;
        $theTable[$this->currentRow][$y] = "Hours";
        $y++;
        $theTable[$this->currentRow][$y] = "Type";
        $y++;
        $theTable[$this->currentRow][$y] = "Subtype";
        $y++;
//        $theTable[$this->currentRow][$y] = "Call Off";
//        $y++;
        $theTable[$this->currentRow][$y] = "Comment";
        $y++;
        $theTable[$this->currentRow][$y] = "Submit Date";
        $y++;
        $theTable[$this->currentRow][$y] = 'Status';
        $y++;
        $theTable[$this->currentRow][$y] = 'Approved By';
        $y++;
        $theTable[$this->currentRow][$y] = 'Approved Time';
        $y++;
        $theTable[$this->currentRow][$y] = 'Reason';
        $y++;
        $theTable[$this->currentRow][$y] = 'HR Approval';
        $y++;
        $theTable[$this->currentRow][$y] = 'HR Notes';
        $y++;
        $this->currentRow++;

        while ($row = $result->fetch_assoc()) {
            $y = 0;

            $theTable[$this->currentRow][$y] = '<input type="submit" id="editBtn' . $this->currentRow . '" name="editBtn' . $this->currentRow . '" value="Edit/View" />' .
                    '<input type="submit" id="printBtn' . $this->currentRow . '" name="printBtn' . $this->currentRow . '" value="Print" onClick="window.open(\'printFriendly.php?printRequestNo=' . $row['RefNo'] . '\');" />' .
                    '<input type="hidden" name="requestID' . $this->currentRow . '" value="' . $row['RefNo'] . '" />';
            if ($row['Status'] == "EXPUNGED")
                $theTable[$this->currentRow][$y] .= '';
            else {
                if (!$row['HR_Approved'])
                    $theTable[$this->currentRow][$y] .= '<input type="submit" name="expungeBtn' . $this->currentRow . '" value="Delete" />';
                if ($row['HR_Approved'] && $this->config->adminLvl >= 50 && $this->config->adminLvl != 75)
                    $theTable[$this->currentRow][$y] .= '<input type="submit" name="expungeBtn' . $this->currentRow . '" value="Delete" />';
            }
            $y++;
            $theTable[$this->currentRow][$y] = '<input type="hidden" name="refNo' . $this->currentRow . '" value="' . $row['RefNo'] . '" />' . $row['RefNo'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['Name'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['Used'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['Start'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['End'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['Hrs'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['Type'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['Subtype'];
            $y++;
//            $theTable[$this->currentRow][$y] = $row['Calloff'];
//            $y++;
            $theTable[$this->currentRow][$y] = $row['Comment'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['Request_Date'];
            $y++;
            if ($row['Status'] != 'PENDING' && $this->config->adminLvl >= 25) {
                $theTable[$this->currentRow][$y] = $row['Status'];
                if (!empty($row['Reason']))
                    $theTable[$this->currentRow][$y] .= '<br/><font color="darkred">' . $row['Reason'] . '</font>';
                if (!$row['HR_Approved'])
                    $theTable[$this->currentRow][$y] .= '<Br/><input type="submit" name="pendingBtn' . $this->currentRow . '" value="Send to Pending" />';
                elseif ($row['HR_Approved'] && $this->config->adminLvl >= 50 && $this->config->adminLvl != 75)
                    $theTable[$this->currentRow][$y] .= '<Br/><input type="submit" name="pendingBtn' . $this->currentRow . '" value="Send to Pending" />';
            }
            elseif ($row['Status'] == 'PENDING' && $this->config->adminLvl >= 25) {
                
                if ($row['ELEVATE_SUPERVISORS'] != "1" || ($row['ELEVATE_SUPERVISORS'] == "1" && $this->config->adminLvl > 25)) {
                    $theTable[$this->currentRow][$y] = $row['Status'];
                    $theTable[$this->currentRow][$y] .= "<br/><input type='submit' name='approve$this->currentRow' value='APPROVED' size='15'/> ";
                    $theTable[$this->currentRow][$y] .= "<input type='submit' name='deny$this->currentRow' value='DENIED' size='15'><br/>";
                    $theTable[$this->currentRow][$y] .= 'Reason:<br/><textarea rows="2" cols="21" name="reason' . $this->currentRow . '" ></textarea>';
                }
                else{
                    $theTable[$this->currentRow][$y] = $row['Status'];
                }
            } else {
                $theTable[$this->currentRow][$y] = $row['Status'] . '</br><font color="darkred">' . $row['Reason'] . '</font>';
            }
            $y++;
            $theTable[$this->currentRow][$y] = $row['ApprovedBy'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['approveTS'];
            $y++;
            $theTable[$this->currentRow][$y] = $row['Reason'];
            $y++;
            if (!$row['HR_Approved'] && $row['Status'] != "DENIED") {
                $theTable[$this->currentRow][$y] = 'Pending';
                if ($row['Status'] == "APPROVED" && $this->config->adminLvl >= 50 && $this->config->adminLvl != 75) {
                    $theTable[$this->currentRow][$y] = '<font color="darkred">Pending</font>';
                    $theTable[$this->currentRow][$y] .= '<input type="submit" name="hrApproveBtn' . $this->currentRow . '" value="HR Approve" />';
                    $y++;
                    $theTable[$this->currentRow][$y] = '<textarea rows="2" cols="21" name="hrReason' . $this->currentRow . '" ></textarea>';
                } elseif ($row['Status'] == "EXPUNGED") {
                    $y++;
                    $theTable[$this->currentRow][$y] = '<font color="darkred"> ' . $row['EXPUNGE_NOTES'] . '</font>';
                } else {
                    $y++;
                    $theTable[$this->currentRow][$y] = '<font color="darkred">
                        <input type="hidden" name="hrOldNotes' . $this->currentRow . '" value="' . $row['HRNOTES'] . '" />' . $row['HRNOTES'] . '</font>';
                }
            } elseif ($row['Status'] == "DENIED") {
                $theTable[$this->currentRow][$y] = 'No Action Required';
                $y++;
                $theTable[$this->currentRow][$y] = '<font color="darkred">
                    <input type="hidden" name="hrOldNotes' . $this->currentRow . '" value="' . $row['HRNOTES'] . '" />' . $row['HRNOTES'] . '</font>';
            } else {
                $theTable[$this->currentRow][$y] = '<div align="center"><h3><font color="darkred">Approved</font></h3></div>';
                $y++;
                $theTable[$this->currentRow][$y] = '<font color="darkred">
                    <input type="hidden" name="hrOldNotes' . $this->currentRow . '" value="' . $row['HRNOTES'] . '" />' . $row['HRNOTES'] . '</font>';
            }
            $y++;
            $this->currentRow++;
        }

        $this->currentTable = $theTable;
    }

    private function showTable() {
        if ($this->config->adminLvl >= 50 && $this->config->adminLvl != 75)
            showSortableTable($this->currentTable, 2, "timeRequestTable");
        else
            showSortableTable($this->currentTable, 2, "timeRequestTable");
        echo '<input type="hidden" name="timeRequestTableRows" value="' . $this->currentRow . '" />';
    }

    public function sendRequestToPending($extraInputs = '') {
        $confirmBtn = isset($_POST['confirmBtn']) ? true : false;
        if ($confirmBtn && !empty($_POST['hrNotes']) && $_SESSION['admin']) {
            $this->hrNotes = $_POST['hrNotes'];
            if (!empty($this->hrNotes)) {
                $myq = getSendToPending($this->config, $this->refNo, $this->hrNotes);
                $result = getQueryResult($this->config, $myq, $debug = false);
                if ($result) {
                    addLog($this->config, 'Ref# ' . $this->refNo . ' status was changed to pending');

                    popUpMessage('Request ' . $this->refNo . ' is now Pending Status. 
                                <div align="center"><form method="POST" action="' . $_SERVER['REQUEST_URI'] . '">
                                ' . $extraInputs . '                     
                                <input type="submit" name="okBtn" value="OK" />
                                </form></div>');
                } else {
                    popupmessage('Failed to add');
                }
            } else {
                popupmessage('Notes Requested');
            }
        } else {
            if (!isset($_POST['okBtn'])) {
                $result = "";

                if (empty($this->hrNotes))
                    $result = '<font color="red">Requires a Reason</font><br/>';
                $echo = '<div align="center"><form method="POST" name="confirmBackToPending">
                <input name="deleteBtn' . $this->toSendToPendingIndex . '" type="hidden" value="' . $this->refNo . '" />
                <input type="hidden" name="totalRows" value="' . $this->toSendToPendingIndex . '" />
                Request ' . $this->refNo . ' will be sent back to pending<br/>   ' . $result . '
                Reason:<textarea name="hrNotes">' . $this->hrNotes . '</textarea><br/>
                <input type="submit" name="confirmBtn" value="CONFIRM" />
                <input type="submit" name="okBtn" value="CANCEL" />
                ' . $extraInputs . ' 
                </form></div>';
                popUpMessage($echo, "Confirm Back To Pending");
            }
        }
    }

    public function hrApproveLeaveRequest() {
        $myq = getHRApprovalByRef($this->config, $this->refNo, $this->supReason);
        $result = getQueryResult($this->config, $myq, $debug = false);
        if (!$result) {
            
        }
        $logMsg = 'HR Approval for Time Request with Ref# ' . $this->refNo;
        addLog($this->config, $logMsg);
        echo '<h6>HR Approval for Reference ' . $this->refNo . "</h6>";
    }

    public function approveLeaveRequest($status) {
        $myq = getApproveRequest($this->config, $this->refNo, $status, $this->supReason);
        $result = getQueryResult($this->config, $myq, $this->debug);
        if ($result) {
            $logMsg = $status.' Time Request with Ref# ' . $this->refNo;
            addLog($this->config, $logMsg);
            echo '<h6>' . $status . " Reference " . $this->refNo . "</h6>";
        }
    }

    public function expungeRequest($extraInputs = '') {
        $confirmBtn = isset($_POST['confirmBtn']) ? true : false;

        if ($this->toUnExpunge) {
            if (!isset($_POST['okBtn'])) {
                $myq = "UPDATE REQUEST 
                SET STATUS='PENDING'
                WHERE REFER=" . $this->config->mysqli->real_escape_string($this->toExpungeRefNo);
                $result = $this->mysqli->query($myq);

                if (!SQLerrorCatch($this->config->mysqli, $result, $myq, $debug = false)) {
                    popUpMessage('Request ' . $this->toExpungeRefNo . ' Has been placed back into PENDING State. 
                        <div align="center"><form method="POST">
                        ' . $extraInputs . '                    
                        <input type="submit" name="okBtn" value="OK" />
                        </form></div>');
                    addLog($this->config, 'UnExpunged Time Request with Ref# ' . $this->toExpungeRefNo);
                }
            }
        } else {

            if ($confirmBtn && !empty($_POST['expungedReason']) && $_SESSION['admin']) {
                $myq = "UPDATE REQUEST 
                    SET STATUS='EXPUNGED',
                    HRAPP_ID='0',
                    EX_REASON='" . $this->config->mysqli->real_escape_string($_POST['expungedReason']) . "',
                    AUDITID='" . $this->config->mysqli->real_escape_string($_SESSION['userIDnum']) . "',
                    IP= INET_ATON('" . $this->config->mysqli->real_escape_string($_SERVER['REMOTE_ADDR']) . "')
                    WHERE REFER='" . $this->config->mysqli->real_escape_string($this->toExpungeRefNo) . "'";
                $result = $this->config->mysqli->query($myq);

                if (!SQLerrorCatch($this->config->mysqli, $result, $myq, $debug = false)) {
                    addLog($this->config, 'Expunged Time Request with Ref# ' . $this->toExpungeRefNo);
                    popUpMessage('Request ' . $this->toExpungeRefNo . ' expunged. 
                                <div align="center"><form method="POST" action="' . $_SERVER['REQUEST_URI'] . '">
                                ' . $extraInputs . '                     
                                <input type="submit" name="okBtn" value="OK" />
                                </form></div>');
                }
            } else {
                if (!isset($_POST['okBtn'])) {
                    $result = "";
                    if (isset($_POST['expungedReason'])) {
                        if (empty($_POST['expungedReason']))
                            $result = '<font color="red">Requires a Reason</font><br/>';
                    }
                    $echo = '<div align="center"><form method="POST">
                    <input name="deleteBtn' . $this->toExpungeIndex . '" type="hidden" value="' . $this->toExpungeRefNo . '" />
                    <input type="hidden" name="totalRows" value="' . $this->toExpungeTotalRows . '" />
                    Request ' . $this->toExpungeRefNo . ' to be expunged<br/>   ' . $result . '
                    Reason:<textarea name="expungedReason"></textarea><br/>
                    <input type="submit" name="confirmBtn" value="CONFIRM EXPUNGE" />
                    <input type="submit" name="okBtn" value="CANCEL" />
                    ' . $extraInputs . ' 
                    </form></div>';
                    popUpMessage($echo);
                }
            }
        }
    }

    private function showPrintFriendlyRedirect() {
        echo '<script type="text/javascript" language="javascript">
                window.open("printFriendly.php?printRequestNo=' . $this->refNo . '");
                </script>';
    }

    public function showPrintFriendlyRequest() {
        if ($this->config->adminLvl >= 0) {
            //Valid user
            $this->refNo = isset($_GET['printRequestNo']) ? $_GET['printRequestNo'] : '';
            $this->refNo = isset($_POST['requestID']) ? $_POST['requestID'] : $this->refNo;
            if (!empty($this->refNo)) {
                echo '<div style="font-size:16px"> <h1><center>MAHONING COUNTY SHERIFF\'S OFFICE <br/>
                    EMPLOYEE REQUEST FORM<br/></center></h1>
                    <h2><center>Request reference #' . $this->refNo . '</center></h2>';
                $this->filters = getFilterByRefNo($this->config, $this->refNo);
                $this->currentQuery = getTimeRequestTable($this->config, $this->filters, $orderBy = '', $limit = ' LIMIT 1');
                $result = getQueryResult($this->config, $this->currentQuery, $this->debug);
                $req = $result->fetch_assoc();
                $mydivq = getEmployeeInfo($this->config, $_SESSION['userIDnum']);
                $myDivResult = getQueryResult($this->config, $mydivq, $debug = false);
                $empInfo = $myDivResult->fetch_assoc();

                if ($this->config->adminLvl >= 25 || ($req['Requester'] == $_SESSION['userIDnum'])) {
                    //Must be admin or viewing own requests
                    echo '<div align="right"> DATE FILED: ' . $req['Request_Date'] . '</div>';
                    echo '<h1>Employee Information</h1>';
                    echo 'EMPLOYEE: <div style="display: inline;font-size:24px">' . $req['Name'] . '</div><br/>';
                    echo 'Dvision: <div style="display: inline;font-size:20px">' . $empInfo['DESCR'] . '</div><br/>';
                    echo 'RANK: <div style="display: inline;font-size:20px">' . $empInfo['RANK'] . '</div><br/>';
                    echo '<br/><h2>Requested Information</h2>';
                    echo '<br/>';
                    echo 'TYPE OF REQUEST: <div style="display: inline;font-size:20px">' . $req['Type'] . '</div><br/>';
                    echo 'SUBTYPE: <div style="display: inline;font-size:20px">' . $req['Subtype'] . '</div><br/>';
                    echo 'DATE OF USE: <div style="display: inline;font-size:20px">' . $req['Used'] . '</div><br/>';
                    echo 'START TIME: <div style="display: inline;font-size:20px">' . $req['Start'] . '</div><br/>';
                    echo 'END TIME: <div style="display: inline;font-size:20px">' . $req['End'] . '</div><br/>';
                    echo 'CALCULATED HOURS: <div style="display: inline;font-size:20px">' . $req['Hrs'] . '</div><br/>';
                    echo 'EMPLOYEE NOTES: <div style="display: inline;font-size:20px">' . $req['Comment'] . '</div><br/><br/><br/>';

                    if ($this->config->adminLvl >= 25) {
                        echo '<h2>Employee Signature: _______________________________________  Date: _____________________</h2>';
                        echo '<div class="divider"></div>';
                    }
                    echo '<h1>O.I.C.</h1>';
                    echo 'REQUEST STATUS: <div style="display: inline;font-size:20px">' . $req['Status'] . '</div><br/>';
                    echo 'SUPERVISOR NOTES: <div style="display: inline;font-size:20px">' . $req['Reason'] . '</div><br/>';
                    echo 'SUPERVISOR FILED DATE: <div style="display: inline;font-size:20px">' . $req['approveTS'] . '</div><br/>';
                    echo 'SUPERVISOR: <div style="display: inline;font-size:20px">' . $req['ApprovedBy'] . '</div><br/><br/><br/>';
                    if ($this->config->adminLvl >= 25) {
                        echo '<h2>Supervisor Signature: _____________________________________  Date: _____________________</h2>';
                        echo '<br/><br/><h1>OFFICE USE ONLY</h1><br/><br/>';
                        echo '<h2>Office Signature: _________________________________________  Date: _____________________</h2>';
                        echo '</div>';
                    }
                } else {
                    echo 'You can only view your own requests';
                }
            } else {
                echo 'Error Getting Reference Number to Print!';
            }
        } else {
            echo 'You must login first';
        }
    }
    public function showTimeRequestEmpCounts($config, $filters, $orderBy = "ORDER BY REFER DESC", $hiddenInput = ''){
        echo '<input type="hidden" name="formName" value="submittedRequests" />';
        $this->config = $config;
        $this->currentFilters = $filters;
        $this->hiddenInput = $hiddenInput;
        $this->handlePOSTVariables();
        if($this->isShowTable){
            if ($this->config->adminLvl < 25) {
                //users only allowed to view own reference numbers
                $this->currentFilters = getFilterLoggedInUserRequestID($this->config);
            }
            $this->currentLimit = $this->getCurrentPageLimits();
            $this->currentQuery = getTimeRequestTable($this->config, $this->currentFilters, $orderBy, $this->currentLimit);
            $this->showPageLimitOptions();
            $this->showSortingOptions();
        }
    
        $result = getQueryResult($this->config, $this->currentQuery, $this->debug);
        return $result;
    }

}

//End of Request Class
?>
