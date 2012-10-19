<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of wts_employee
 *
 * @author awturner
 */
class wts_employee {
    public $mysqli, $IDNum, $username, $munisNum, $radioNum, $fName, $lName, $rank, $address, $city,
            $state, $zip, $homePhone, $cellPhone, $workPhone, $email, $DOB, $emergencyContact,
            $divisionID, $supervisorID, $shift, $hireDate, $PTISBEG, $PTISEND, $adminLvl,
            $isLDAP, $isMCO, $isActive, $isVerified, $lastLogin, $auditID, $auditTime, $auditIP;
    
    function wts_employee(){
        $this->mysqli=''; $this->IDNum=''; $this->username=''; $this->munisNum=''; $this->radioNum=''; $this->fName=''; $this->lName=''; $this->rank=''; $this->address=''; $this->city='';
            $this->state=''; $this->zip=''; $this->homePhone=''; $this->cellPhone=''; $this->workPhone=''; $this->email=''; $this->DOB=''; $this->emergencyContact='';
            $this->divisionID=''; $this->supervisorID=''; $this->shift=''; $this->hireDate=''; $this->PTISBEG=''; $this->PTISEND=''; $this->adminLvl='';
            $this->isLDAP=''; $this->isMCO=''; $this->isActive=''; $this->isVerified=''; $this->lastLogin=''; $this->auditID=''; $this->auditTime=''; $this->auditIP='';
       
    }
    function setMySQLI($config){
        $this->mysqli = $config->mysqli;
    }
    function addEmployee(){
        $myq = "INSERT INTO EMPLOYEE VALUES (
            NULL , ".$this->username."', '".$this->munisNum."','".$this->radioNum."', '".$this->fName."',
            '".$this->lName."', '".$this->rank."', '".$this->address."', '".$this->city."', '".$this->state."',
            '".$this->zip."', '".$this->homePhone."', '".$this->cellPhone."', '".$this->workPhone."',
            '".$this->email."', '".$this->DOB."', '".$this->emergencyContact."','".$this->divisionID."',
            '".$this->supervisorID."', '".$this->shift."', '".$this->hireDate."', '".$this->PTISBEG."',
            '".$this->PTISEND."', '".$this->adminLvl."','".$this->isLDAP."', '".$this->isMCO."',
            '".$this->isActive."', '".$this->isVerified."', '".$this->lastLogin."', '".$this->auditID."',
            '".$this->auditTime."', '".$this->auditIP;
        $result = $this->mysqli->query($myq);
        if(!SQLerrorCatch($this->mysqli, $result))
            $error = "Successfully Added Employee";
        else
            $error = "Failed to Add Employee";
            
        return $error;
    }
    function updateEmployee(){
        $this->auditID = $_SESSION['userIDnum'];
        $myq = "UPDATE `EMPLOYEE` SET 
                `MUNIS` = '".$this->munisNum."',
                `LNAME` = '".$this->lName."',
                `FNAME` = '".$this->fName."',
                `GRADE` = '".$this->rank."',
                `DIVISIONID` = '".$this->divisionID."',
                `SUPV` = '".$this->supervisorID."',
                `ASSIGN` = '".$this->shift."',
                `TIS` = '".Date('Y-m-d', strtotime($this->hireDate))."',    
                `RADIO` = '".$this->radioNum."',
                ADDRESS = '".$this->address."',
                HOMEPH = '".$this->homePhone."',
                CELLPH = '".$this->cellPhone."',
                WORKPH = '".$this->workPhone."',
                EMAIL = '".$this->email."',
                DOB = '".Date('Y-m-d', strtotime($this->DOB))."',
                EMERGCON = '".$this->emergencyContact."',
                ADMINLVL = '".$this->adminLvl."',
                IS_VERIFY = 1,
                AUDITID = '".$this->auditID."',
                AUDIT_TIME = NOW(),
                AUDIT_IP = INET_ATON('".$_SERVER['REMOTE_ADDR']."')
                WHERE IDNUM = '".$this->IDNum."'";
        $result = $this->mysqli->query($myq);
        if(!SQLerrorCatch($this->mysqli, $result))
            $error = "Successfully Updated Employee";
        else
            $error = "Failed to Update Employee Record";
        return $error;
        
    }
    function disableEmployee(){
        $myq = "UPDATE EMPLOYEE SET IS_ACTIVE = '0' WHERE IDNUM=".$this->IDNum;
        $result = $this->mysqli->query($myq);
        if(!SQLerrorCatch($this->mysqli, $result))
            $error = "Successfully Disabled Employee";
        else
            $error = "Failed to Disable Employee";
        
        return $error;
        
    }
    function getEmpByID($id){
        $empFound = new wts_employee();
        $myq = "SELECT * FROM EMPLOYEE WHERE IDNUM=".$id;
        $result = $this->mysqli->query($myq);
        if(!SQLerrorCatch($this->mysqli, $result)){
            //successfully got list of employees
            $AllCol = array();
            $i=0;
            $rows = $result->fetch_fields();
            foreach ($rows as $col) {
                $colName = $col->name;
                $AllCol[$i] = $colName;
                $i++;
            }
            $x=0;
            while($row = $result->fetch_assoc()) {
                for ($y=0;$y<sizeof($AllCol);$y++){
                     if($AllCol[$i] == 'IDNUM')$this->IDNum = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'ID')$this->username = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'MUNIS')$this->munisNum = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'RADIO')$this->radioNum = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'LNAME')$this->lName = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'FNAME')$this->fName = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'GRADE')$this->radioNum = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'ADDRESS')$this->address = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'CITY')$this->city = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'ST')$this->state = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'ZIP')$this->zip = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'HOMEPH')$this->homePhone = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'CELLPH')$this->cellPhone = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'WORKPH')$this->workPhone = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'EMAIL')$this->email = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'DOB')$this->DOB = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'EMERGCON')$this->emergencyContact = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'DIVISIONID')$this->divisionID = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'SUPV')$this->supervisorID = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'ASSIGN')$this->shift = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'TIS')$this->hireDate = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'PTISBEG')$this->PTISBEG = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'PTISEND')$this->PTISEND = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'ADMINLVL')$this->adminLvl = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'isLDAP')$this->isLDAP = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'IS_ACTIVE')$this->isActive = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'IS_VERIFY')$this->isVerified = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'LASTLOGIN')$this->lastLogin = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'AUDITID')$this->auditID = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'AUDIT_TIME')$this->auditTime = $row[$AllCol[$i]];
                    else if($AllCol[$i] == 'AUDIT_IP') $this->auditIP = $row[$AllCol[$i]];
                }
                $x++;
            }
        }
        else
            $empFound = "Failed to Find Employee";
        return $empFound;
        
    }
    function getEmpByName($lName){
        $listOfResults = array(array());
        $myq = "SELECT IDNUM FROM EMPLOYEE WHERE LNAME='".$lName."';";
        $result = $this->mysqli->query($myq);
        if(!SQLerrorCatch($this->mysqli, $result)){
            //$error = "Successfully Disabled Employee";
            $x = 0;
            while($row = $result->fetch_assoc()) {
                $listOfResults[$x] = wts_employee::getEmpByID($row['IDNUM']);
                $x++;
            }
        }
        else
            $error = "Failed to Find Employee with Last Name of ".$lName;
        
        return $listOfResults;
    }
}
?>
