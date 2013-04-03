<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Employee
 *
 * @author aturner
 */
class Employee {

//put your code here
    private $config;
    private $selectedUser;
    private $searchReserves;
    private $searchUser;
    private $findUserBtn;
    private $rowCount;
    private $showLookup;
    public $FName;
    public $LNAME;
    public $userName;

    public function Employee($config) {
        $this->config = $config;
        $this->selectedUser = false;
        $this->showLookup = true;
    }

    public function displayUserLookup() {
        $this->userLookupPOSTS();
        if ($this->showLookup) {
            echo '<h3>Search for Employees by Last Name: </h3>';            

            //show check boxes and keep checked box checked after results return
            if ($this->searchReserves) {
                echo '<input type="checkbox" name="searchReserves" CHECKED />Reserves';
            } else {
                echo '<input type="checkbox" name="searchReserves" />Reserves';
            }

            echo '<br /><input type="text" name="searchUser" value="' . $this->searchUser . '" />
            <input type="submit" name="findUserBtn" value="Search" /> 
            <input type="submit" name="cancelUserLookupBtn" value="Cancel Search" /><br />';
            echo '(Enter all or part of an employee\'s last name)</br></br>';
            $this->showSearchResults();
        }

        return $this->selectedUser;
    }

    private function userLookupPOSTS() {
//Variables
        $this->findUserBtn = isset($_POST['findUserBtn']) ? true : false;
        $findUserTotalRows = isset($_POST['findUserTotalRows']) ? $_POST['findUserTotalRows'] : 0;
        $this->searchUser = isset($_POST['searchUser']) ? $_POST['searchUser'] : '';
        $this->searchReserves = isset($_POST['searchReserves']) ? true : false;
        $cancelBtn = isset($_POST['cancelUserLookupBtn']) ? true : false;

        if ($findUserTotalRows) {
            for ($i = 0; $i <= $findUserTotalRows; $i++) {
                if (isset($_POST['foundUser' . $i])) {
                    $this->selectedUser = $this->config->mysqli->real_escape_string($_POST['foundUserID' . $i]);
                    $this->FName = $this->config->mysqli->real_escape_string($_POST['foundUserFNAME' . $i]);
                    $this->LNAME = $this->config->mysqli->real_escape_string($_POST['foundUserLNAME' . $i]);
                    $this->userName = $this->config->mysqli->real_escape_string($_POST['foundUserName' . $i]);
                    $this->showLookup = false;
                    break;
                }
            }
        }  
        if ($cancelBtn) {
            $this->selectedUser = $_SESSION['userIDnum'];
            $this->showLookup = false;
        }
    }

    private function showSearchResults(){
        if ($this->findUserBtn && !empty($this->searchUser)) {
            //Perform the search
            $this->rowCount = 0;
            $this->searchDatabase();
            if ($this->searchReserves)
                $this->searchReserves();

            echo '<input type="hidden" name="findUserTotalRows" value="' . $this->rowCount . '" />';
        }
    }
    private function searchDatabase() {
        $myq = "SELECT FNAME,LNAME,GRADE,E.ID,E.IDNUM, D.DESCR, isLDAP 
                FROM EMPLOYEE E
                LEFT JOIN DIVISION AS D ON E.DIVISIONID=D.DIVISIONID
                WHERE `ID` LIKE '%" . strtoupper($this->config->mysqli->real_escape_string($this->searchUser)) . "%'
                     OR `LNAME` LIKE '%" . strtoupper($this->config->mysqli->real_escape_string($this->searchUser)) . "%'";
        $result = $this->config->mysqli->query($myq);
        SQLerrorCatch($this->config->mysqli, $result, $myq);
        $begin = $this->rowCount;
        $echo = "";

        while ($row = $result->fetch_assoc()) {
            $this->rowCount++;
            $echo .= '<div align="center"><table width="400"><tr><td>';
            $echo .= '<input name="foundUser' . $this->rowCount . '" type="submit" value="Select" /></td><td>';
            $echo .= '<input type="hidden" name="foundUserFNAME' . $this->rowCount . '" value="' . $row['FNAME'] . '" /> First name: ' . $row['FNAME'] . "<br />";
            $echo .= '<input type="hidden" name="foundUserLNAME' . $this->rowCount . '" value="' . $row['LNAME'] . '" /> Last Name: ' . $row['LNAME'] . "<br />";
            $echo .= '<input type="hidden" name="foundUserName' . $this->rowCount . '" value="' . $row['ID'] . '" /> Username: ' . $row['ID'] . '<br />';
            $echo .= '<input type="hidden" name="foundUserID' . $this->rowCount . '" value="' . $row['IDNUM'] . '" />';
            $echo .= "Rank: " . $row['GRADE'] . "<br />";
            $echo .= "Department: " . $row['DESCR'] . "<br />";
            $echo .= "</td></tr></table></div><br /><hr />";
        }//end While Loop
        $rowsAdded = $this->rowCount - $begin;
        echo "Number of entries found in the Full Time Employee database is " . $rowsAdded . "<br /><br /><hr />";
        if ($rowsAdded > 0) {
            echo $echo;
        }
    }

    private function searchReserves() {
        $mysqli = connectToSQL($reserveDB = TRUE);
        if ($this->config->adminLvl < 75)
            $myq = "SELECT *  FROM `RESERVE` WHERE `GRP` != 5 AND 
                `LNAME` LIKE CONVERT(_utf8 '%" . $this->config->mysqli->real_escape_string($this->searchUser) . "%' USING latin1) 
                    COLLATE latin1_swedish_ci ";
        else
            $myq = "SELECT *  FROM `RESERVE` WHERE 
                `LNAME` LIKE CONVERT(_utf8 '%" . $this->config->mysqli->real_escape_string($this->searchUser) . "%' USING latin1) 
                    COLLATE latin1_swedish_ci ";
        $result = $mysqli->query($myq);
        SQLerrorCatch($mysqli, $result, $myq);
        $begin = $this->rowCount;
        $echo = "";

        while ($row = $result->fetch_assoc()) {
            $this->rowCount++;
            $echo .= '<div align="center"><table width="400"><tr><td>';
            $echo .= '<input name="foundUser' . $this->rowCount . '" type="submit"  value="Select" /></td><td>';
            $echo .= '<input type="hidden" name="foundUserFNAME' . $this->rowCount . '" value="' . $row['FNAME'] . '" /> First name: ' . $row['FNAME'] . "<br />";
            $echo .= '<input type="hidden" name="foundUserLNAME' . $this->rowCount . '" value="' . $row['LNAME'] . '" /> Last Name: ' . $row['LNAME'] . "<br />";
            $echo .= '<input type="hidden" name="foundUserID' . $this->rowCount . '" value="' . $row['IDNUM'] . '" /> Username: ' . $row['FNAME'] . "." . $row['LNAME'] . '<br />';
            $echo .= '<input type="hidden" name="foundUserName' . $this->rowCount . '" value="' . $row['FNAME'] . "." . $row['LNAME'] . '" />';
            $echo .= "Rank: Reserve Group " . $row['GRP'] . "<br />";
            $echo .= '<input type="hidden" name="isReserve' . $this->rowCount . '" value="true" />"';
            $echo .= "</td></tr></table></div><br /><hr />";
        }//end While Loop
        $rowsAdded = $this->rowCount - $begin;
        echo "Number of entries found in the reserve database is " . $rowsAdded . "<br /><br /><hr />";
        if ($rowsAdded > 0) {
            echo $echo;
        }
    }

}

?>
