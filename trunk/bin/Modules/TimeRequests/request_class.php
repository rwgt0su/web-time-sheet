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
    
    public function request_class(){
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
    }

    public function showTimeRequestTable($config, $filters, $orderBy = "ORDER BY REFER DESC", $hiddenInput = '') {
        $this->config = $config;
        $this->currentFilters = $filters;
        $this->handlePOSTVariables();
        if ($this->config->adminLvl < 25) {
            //users only allowed to search own reference numbers
            $this->currentFilters = getFilterLoggedInUserRequestID();
        }
        $this->currentQuery = getTimeRequestTable($this->config, $this->currentFilters, $orderBy);
        $this->prepareTimeTable();

        if($this->isSendToPending){
            echo '</form>';
            $hiddenInput .= '<input type="hidden" name="timeRequestTableRows" value="2" />
                    <input type="hidden" name="pendingBtn1" value="true" />
                    <input type="hidden" name="refNo1" value="' . $this->refNo . '" />
                    ';
            $this->sendRequestToPending($hiddenInput);
        }

        if ($this->toExpunge) {
            echo '</form>';
            $hiddenInput .= '<input type="hidden" name="timeRequestTableRows" value="2" />
                    <input type="hidden" name="expungeBtn1" value="true" />
                    <input type="hidden" name="refNo1" value="' . $this->toExpungeRefNo . '" />
                    ';
            $this->expungeRequest($hiddenInput);
        }
    }
    private function handlePOSTVariables(){
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
                if ($this->btnPushed) {
                    $this->config->anchorID = "editBtn" . $i;
                    //goToAnchor("editBtn" . $i);
                    break;
                } 
            }
        }
    }
    private function prepareTimeTable(){
        $result = getQueryResult($this->config, $this->currentQuery, $this->debug);
        $theTable = array(array());
        $this->currentRow = 0;
        $y = 0;

        $theTable[$this->currentRow][$y] = "Actions";$y++;
        $theTable[$this->currentRow][$y] = "Ref#";$y++;
        $theTable[$this->currentRow][$y] = "Employee";$y++;
        $theTable[$this->currentRow][$y] = "Date_of_Use";$y++;
        $theTable[$this->currentRow][$y] = "Start Time";$y++;
        $theTable[$this->currentRow][$y] = "End Time";$y++;
        $theTable[$this->currentRow][$y] = "Hours";$y++;
        $theTable[$this->currentRow][$y] = "Type";$y++;
        $theTable[$this->currentRow][$y] = "Subtype";$y++;
        $theTable[$this->currentRow][$y] = "Call Off";$y++;
        $theTable[$this->currentRow][$y] = "Comment";$y++;
        $theTable[$this->currentRow][$y] = 'Status';$y++;
        $theTable[$this->currentRow][$y] = 'Approved By';$y++;
        $theTable[$this->currentRow][$y] = 'Approved Time';$y++;
        $theTable[$this->currentRow][$y] = 'Reason';$y++;
        $theTable[$this->currentRow][$y] = 'HR Approval';$y++;
        $theTable[$this->currentRow][$y] = 'HR Notes';$y++;
        $this->currentRow++;

        while ($row = $result->fetch_assoc()) {
            $y = 0;

            $theTable[$this->currentRow][$y] = '<input type="submit" id="editBtn' . $this->currentRow . '" name="editBtn' . $this->currentRow . '" value="Edit/View" onClick="this.form.action=' . "'?leave=true'" . '; this.form.submit()" />' .
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
            $theTable[$this->currentRow][$y] = '<input type="hidden" name="refNo' . $this->currentRow . '" value="' . $row['RefNo'] . '" />' . $row['RefNo'];$y++;
            $theTable[$this->currentRow][$y] = $row['Name'];$y++;
            $theTable[$this->currentRow][$y] = $row['Used'];$y++;
            $theTable[$this->currentRow][$y] = $row['Start'];$y++;
            $theTable[$this->currentRow][$y] = $row['End'];$y++;
            $theTable[$this->currentRow][$y] = $row['Hrs'];$y++;
            $theTable[$this->currentRow][$y] = $row['Type'];$y++;
            $theTable[$this->currentRow][$y] = $row['Subtype'];$y++;
            $theTable[$this->currentRow][$y] = $row['Calloff'];$y++;
            $theTable[$this->currentRow][$y] = $row['Comment'];$y++;
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
                $theTable[$this->currentRow][$y] = $row['Status'];
                $theTable[$this->currentRow][$y] .= "<br/><input type='submit' name='approve$this->currentRow' value='APPROVED' size='15'/> ";
                $theTable[$this->currentRow][$y] .= "<input type='submit' name='deny$this->currentRow' value='DENIED' size='15'><br/>";
                $theTable[$this->currentRow][$y] .= 'Reason:<br/><textarea rows="2" cols="21" name="reason' . $this->currentRow . '" ></textarea>';
            } else {
                $theTable[$this->currentRow][$y] = $row['Status'] . '</br><font color="darkred">' . $row['Reason'] . '</font>';
            }
            $y++;
            $theTable[$this->currentRow][$y] = $row['ApprovedBy'];$y++;
            $theTable[$this->currentRow][$y] = $row['approveTS'];$y++;
            $theTable[$this->currentRow][$y] = $row['Reason'];$y++;
            if (!$row['HR_Approved'] && $row['Status'] != "DENIED") {
                $theTable[$this->currentRow][$y] = 'Pending';
                if ($row['Status'] == "APPROVED" && $this->config->adminLvl >= 50 && $this->config->adminLvl != 75) {
                    $theTable[$this->currentRow][$y] = '<font color="darkred">Pending</font>';
                    $theTable[$this->currentRow][$y] .= '<input type="submit" name="hrApproveBtn' . $this->currentRow . '" value="HR Approve" />';$y++;
                    $theTable[$this->currentRow][$y] = '<textarea rows="2" cols="21" name="hrReason' . $this->currentRow . '" ></textarea>';
                }elseif ($row['Status'] == "EXPUNGED") {
                    $y++;
                    $theTable[$this->currentRow][$y] = '<font color="darkred"> '.$row['EXPUNGE_NOTES'] . '</font>';
                } 
                else {
                    $y++;
                    $theTable[$this->currentRow][$y] = '<font color="darkred">
                        <input type="hidden" name="hrOldNotes' . $this->currentRow . '" value="' . $row['HRNOTES'] . '" />' . $row['HRNOTES'] . '</font>';
                }
            } elseif ($row['Status'] == "DENIED") {
                $theTable[$this->currentRow][$y] = 'No Action Required';$y++;
                $theTable[$this->currentRow][$y] = '<font color="darkred">
                    <input type="hidden" name="hrOldNotes' . $this->currentRow . '" value="' . $row['HRNOTES'] . '" />' . $row['HRNOTES'] . '</font>';
            } else {
                $theTable[$this->currentRow][$y] = '<div align="center"><h3><font color="darkred">Approved</font></h3></div>';$y++;
                $theTable[$this->currentRow][$y] = '<font color="darkred">
                    <input type="hidden" name="hrOldNotes' . $this->currentRow . '" value="' . $row['HRNOTES'] . '" />' . $row['HRNOTES'] . '</font>';
            }
            $y++;
            $this->currentRow++;
        }
        
        if ($this->config->adminLvl >= 50 && $this->config->adminLvl != 75)
            showSortableTable($theTable, 2, "timeRequestTable");
        else
            showSortableTable($theTable, 2, "timeRequestTable");
        echo '<input type="hidden" name="timeRequestTableRows" value="' . $this->currentRow . '" />';
    }

    public function sendRequestToPending($extraInputs = '') {
        $confirmBtn = isset($_POST['confirmBtn']) ? true : false;
        if ($confirmBtn && !empty($_POST['hrNotes']) && $_SESSION['admin']) {
            $this->hrNotes = $_POST['hrNotes'];
            if (!empty($this->hrNotes)){
                $myq = getSendToPending($this->config, $this->refNo, $this->hrNotes);
                $result = getQueryResult($this->config, $myq, $debug=false);
                if($result){
                    addLog($this->config, 'Ref# ' . $this->refNo . ' status was changed to pending');

                    popUpMessage('Request ' . $this->refNo . ' is now Pending Status. 
                                <div align="center"><form method="POST" action="' . $_SERVER['REQUEST_URI'] . '">
                                ' . $extraInputs . '                     
                                <input type="submit" name="okBtn" value="OK" />
                                </form></div>');
                }
                else{popupmessage('Failed to add');}
            }
            else {popupmessage('Notes Requested');}
            
        } else {
            if (!isset($_POST['okBtn'])) {
                $result = "";
                
                if (empty($this->hrNotes))
                    $result = '<font color="red">Requires a Reason</font><br/>';
                $echo = '<div align="center"><form method="POST" name="confirmBackToPending">
                <input name="deleteBtn' . $this->toSendToPendingIndexx . '" type="hidden" value="' . $this->refNo . '" />
                <input type="hidden" name="totalRows" value="' . $this->toSendToPendingIndex . '" />
                Request ' . $this->refNo . ' will be sent back to pending<br/>   ' . $result . '
                Reason:<textarea name="hrNotes">'.$this->hrNotes.'</textarea><br/>
                <input type="submit" name="confirmBtn" value="CONFIRM" />
                <input type="submit" name="okBtn" value="CANCEL" />
                ' . $extraInputs . ' 
                </form></div>';
                popUpMessage($echo, "Confirm Back To Pending");
            }
        }
        
        
       
    }

    public function hrApproveLeaveRequest() {
        $myq= getHRApprovalByRef($this->config, $this->refNo, $this->supReason);
        $result = getQueryResult($this->config, $myq, $debug=false);
        if (!$result) {
            $logMsg = 'Approved Time Request with Ref# ' . $this->refNo;
            addLog($this->config, $logMsg);
            echo '<h6>HR Approval for Reference ' . $this->refNo . "</h6>";
        }
    }

    public function selectTimeType($inputName, $selected = false, $onChangeSubmit = false) {
        //assumes to be part of a form
        //provides a drop down selection for time type.
        if ($onChangeSubmit)
            echo '<select name="' . $inputName . '" onchange="this.form.submit()">';
        else
            echo '<select name="' . $inputName . '" >';

        //$myq = getTimeTypes();
        $result = getQueryResult($this->config, $myq, $debug=false);

        while ($row = $result->fetch_assoc()) {
            if ($row['TIMETYPEID'] == $selected)
                echo '<option value="' . $row['TIMETYPEID'] . '" SELECTED>' . $row['DESCR'] . '</option>';
            else
                echo '<option value="' . $row['TIMETYPEID'] . '">' . $row['DESCR'] . '</option>';
        }

        echo '</select>';
    }

    public function approveLeaveRequest($status) {
        $myq = getApproveRequest($this->config, $this->refNo, $status, $this->supReason);
        $result = getQueryResult($this->config, $myq, $this->debug);
        if ($result) {
            $logMsg = 'Approved Time Request with Ref# ' . $this->refNo;
            addLog($this->config, $logMsg);
            echo '<h6>' . $status . " Reference " . $this->refNo . "</h6>";
        }
    }

    public function expungeRequest($extraInputs = '') {
        $confirmBtn = isset($_POST['confirmBtn']) ? true : false;
    
        if ($this->unExpunge) {
            if (!isset($_POST['okBtn'])) {
                $myq = getSendToPending($this->config, $this->toExpungeRefNo, $this->hrNotes);
                $result = getQueryResult($this->config, $myq, $debug=false);

                if (!$result) {
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
                $myq = getExpungeRequest($this->config, $this->toExpungeRefNo, $_POST['expungedReason']);
                $result = getQueryResult($this->config, $myq, $debug=false);

                if ($result) {
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
                    $echo = '<div align="center"><form method="POST" action="' . $_SERVER['REQUEST_URI'] . '">
                    <input name="deleteBtn' . $this->delBtnIndex . '" type="hidden" value="' . $this->toExpungeRefNo . '" />
                    <input type="hidden" name="totalRows" value="' . $this->toExpungeTotalRows . '" />
                    Request ' . $this->toExpungeRefNo . ' will be expunged<br/>   ' . $result . '
                    Reason:<br/><textarea name="expungedReason"></textarea><br/>
                    <input type="submit" name="confirmBtn" value="CONFIRM EXPUNGE" />
                    <input type="submit" name="okBtn" value="CANCEL" />
                    ' . $extraInputs . ' 
                    </form></div>';
                    popUpMessage($echo);
                }
            }
        }
    }

}

?>
