<?php

function searchPage($config) {
    $searchInput = isset($_POST['searchInput']) ? $_POST['searchInput'] : false;
    if ($searchInput) {
        echo '<h3>Results for: ' . $searchInput . '</h3>';
        $rowCount1 = selectUserSearch($config, $searchInput);
        $rowCount2 = searchDatabase($config, $searchInput, $rowCount1, true, false);
        $rowCount3 = $rowCount1 + $rowCount2;
        $rowCount3 = searchReserves($config, $searchInput, $rowCount3, false);
        $rowCount3 = $rowCount1 + $rowCount2 + $rowCount3;
        echo "Total Number of entries found is " . $rowCount3 . "<br /><br /><hr />";
    } else {
        echo 'No information provided';
    }
}

function selectUserSearch($config, $userToFind, $select = false) {
    //LDAP Search
    $cnx = ldap_connect($config->ldap_server);
    $user = $config->ldapUser;
    $pass = $config->ldapPass;
    $ldaprdn = $user . '@' . $config->domain;
    ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
    ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
    if ($ldapbind = ldap_bind($cnx, $ldaprdn, $pass)) {
        //Split given domain into LDAP Base DN
        $temp = explode(".", $config->domain);
        $dn = null;
        foreach ($temp as $dc) {
            if (empty($dn))
                $dn = "DC=" . $dc;
            else
                $dn = $dn . ",DC=" . $dc;
        }
        error_reporting(E_ALL ^ E_NOTICE);   //Suppress some unnecessary messages
        $filter = "(&(objectCategory=person)(objectClass=user)";
        $filter.="(|(samaccountname=*" . $userToFind . "*)(sn=*" . $userToFind . "*)(displayname=*" . $userToFind . "*)";
        $filter.="(mail=*" . $userToFind . "*)(department=*" . $userToFind . "*)(title=*" . $userToFind . "*)))";  //Search fields
        $res = ldap_search($cnx, $dn, $filter);

        $totalRows = ldap_count_entries($cnx, $res);
        $info = ldap_get_entries($cnx, $res);
        echo "Number of entries in Active Directory returned is " . $totalRows . "<br /><br /><hr />";
        $rowCount = 1;
        for ($i=0; $i < $info["count"]; $i++) {
            //echo "dn is: " . $info[$i]["dn"] . "<br />";
            echo '<div align="center"><table width="400"><tr><td>';
            if ($select)
                echo '<input name="foundUser' . $rowCount . '" type="radio" onClick="this.form.action=\'?' . $_POST['formName'] . "=true'" . ';this.form.submit()" />Select</td><td>';
            echo "Display Name: " . $info[$i]["displayname"][0] . "<br />";
            echo '<input type="hidden" name="foundUserFNAME'.$rowCount.'" value="'.$info[$i]["givenname"][0].'" />First name: ' . $info[$i]["givenname"][0] . "<br />";
            echo '<input type="hidden" name="foundUserLNAME'.$rowCount.'" value="'.$info[$i]["sn"][0].'" /> Last Name: ' . $info[$i]["sn"][0] . "<br />";
            echo '<input type="hidden" name="foundUserName' . $rowCount . '" value="' . $info[$i]["samaccountname"][0] . '" /> Username: ' . $info[$i]["samaccountname"][0] . '<br />';
            //Check user in Employee Database and output IDNUM if found
            $result = searchDatabase($config, $info[$i]["samaccountname"][0], $i, false);
            if ($result < 1) {
                //User not in database, so register the user
                registerUser($info[$i]["samaccountname"][0], "temp01", "temp01", 0, 1);
            }
            //Get user's IDNUM
            $mysqli = $config->mysqli;
            $myq = "SELECT *
                FROM `EMPLOYEE`
                WHERE `ID` =  '" . strtoupper($info[$i]["samaccountname"][0]) . "'";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            $row = $result->fetch_assoc();
            echo "Rank: " . $row['GRADE'] . "<br />";
            echo "Department: " . $row['DESCR'] . "<br />";

            if ($result < 1) {
                //Update newly created user's information with their Active Directory Info
                $myq = "UPDATE `PAYROLL`.`EMPLOYEE` SET 
                    `LNAME` = '" . $info[$i]["sn"][0] . "',
                    `FNAME` = '" . $info[$i]["givenname"][0] . "'
                    WHERE EMPLOYEE.IDNUM = '" . $row['IDNUM'] . "'";
                //Perform SQL Query
                $result = $mysqli->query($myq);

                //show SQL error msg if query failed
                if (!SQLerrorCatch($mysqli, $result))
                    $result = "Successfully Updated Profile";
            }
            echo "Title: " . $info[$i]["title"][0] . "<br />";
            echo "Department: " . $info[$i]["department"][0] . "<br />";
            echo "Email: " . $info[$i]["mail"][0] . "<br />";
            echo '<input type="hidden" name="foundUserID' . $rowCount . '" value="' . $row['IDNUM'] . '" />';
            echo "</td></tr></table></div><br /><hr />";
            $rowCount++;
        }
    }
    else
        popUpMessage("Could Not Bind to LDAP to perform search");

    return $totalRows;
}

function searchDatabase($config, $userToFind, $rowCount, $isSearching = true, $isSelect = true) {

    $mysqli = $config->mysqli;
    //find a user ID to be used in a form
    if ($isSearching){
        //if full time box checked, LDAP has been searched. would be redundant results
        if(isset($_POST['fullTime']))
            $myq = "SELECT FNAME,LNAME,GRADE,E.ID,E.IDNUM, D.DESCR, isLDAP 
                FROM EMPLOYEE E
                LEFT JOIN DIVISION AS D ON E.DIVISIONID=D.DIVISIONID
                WHERE `ID` LIKE '%" . strtoupper($userToFind) . "%' 
                AND `isLDAP` !=1";
        else
            $myq = "SELECT FNAME,LNAME,GRADE,E.ID,E.IDNUM, D.DESCR, isLDAP 
                FROM EMPLOYEE E
                LEFT JOIN DIVISION AS D ON E.DIVISIONID=D.DIVISIONID
                WHERE `ID` LIKE '%" . strtoupper($userToFind) . "%'" ;
    }
    else
        $myq = "SELECT * FROM `EMPLOYEE` WHERE `ID` LIKE '%" . strtoupper($userToFind) . "%'";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $begin = $rowCount;
    $echo = "";

    while ($row = $result->fetch_assoc()) {
        $rowCount++;
        if (!$row['isLDAP'] || !isset($_POST['fullTime'])) {
            if ($isSearching) {
                $echo .= '<div align="center"><table width="400"><tr><td>';
                if ($isSelect)
                    $echo .= '<input name="foundUser' . $rowCount . '" type="radio" onClick="this.form.action=\'?' . $_POST['formName'] . "=true'" . ';this.form.submit()" />Select</td><td>';
                $echo .= '<input type="hidden" name="foundUserFNAME' . $rowCount . '" value="' . $row['FNAME'] . '" /> First name: ' . $row['FNAME'] . "<br />";
                $echo .= '<input type="hidden" name="foundUserLNAME' . $rowCount . '" value="' . $row['LNAME'] . '" /> Last Name: ' . $row['LNAME'] . "<br />";
                $echo .= '<input type="hidden" name="foundUserName' . $rowCount . '" value="' . $row['ID'] . '" /> Username: ' . $row['ID'] . '<br />';
            }
            $echo .= '<input type="hidden" name="foundUserID' . $rowCount . '" value="' . $row['IDNUM'] . '" />';
            $echo .= "Rank: " . $row['GRADE'] . "<br />";
            $echo .= "Department: " . $row['DESCR'] . "<br />";
            if ($isSearching)
                $echo .= "</td></tr></table></div><br /><hr />";
        }//end is in LDAP
    }//end While Loop
    $rowsAdded = $rowCount - $begin;
    if ($rowsAdded > 0) {
        if ($isSearching)
            echo "Number of entries found in the Full Time Employee database is " . $rowsAdded . "<br /><br /><hr />";
        echo $echo;
    }

    return $rowsAdded;
}

function searchReserves($config, $userToFind, $rowCount, $isSelect = true) {
    $mysqli = connectToSQL($reserveDB = TRUE);
    if ($config->adminLvl < 75)
        $myq = "SELECT *  FROM `RESERVE` WHERE `GRP` != 5 AND `LNAME` LIKE CONVERT(_utf8 '%" . $userToFind . "%' USING latin1) COLLATE latin1_swedish_ci ";
    else
        $myq = "SELECT *  FROM `RESERVE` WHERE `LNAME` LIKE CONVERT(_utf8 '%" . $userToFind . "%' USING latin1) COLLATE latin1_swedish_ci ";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $begin = $rowCount;
    $echo = "";

    while ($row = $result->fetch_assoc()) {
        $rowCount++;
        $echo .= '<div align="center"><table width="400"><tr><td>';
        if ($isSelect)
            $echo .= '<input name="foundUser' . $rowCount . '" type="radio" onClick="this.form.action=\'?' . $_POST['formName'] . "=true'" . ';this.form.submit()" />Select</td><td>';
        $echo .= '<input type="hidden" name="foundUserFNAME' . $rowCount . '" value="' . $row['FNAME'] . '" /> First name: ' . $row['FNAME'] . "<br />";
        $echo .= '<input type="hidden" name="foundUserLNAME' . $rowCount . '" value="' . $row['LNAME'] . '" /> Last Name: ' . $row['LNAME'] . "<br />";
        $echo .= '<input type="hidden" name="foundUserID' . $rowCount . '" value="' . $row['IDNUM'] . '" /> Username: ' . $row['FNAME'] . "." . $row['LNAME'] . '<br />';
        $echo .= '<input type="hidden" name="foundUserName' . $rowCount . '" value="' . $row['FNAME'] . "." . $row['LNAME'] . '" />';
        $echo .= "Rank: Reserve Group " . $row['GRP'] . "<br />";
        $echo .= '<input type="hidden" name="isReserve' . $rowCount . '" value="true" />"';
        $echo .= "</td></tr></table></div><br /><hr />";
    }//end While Loop
    $rowsAdded = $rowCount - $begin;
    if ($rowsAdded > 0) {
        echo "Number of entries found in the reserve database is " . $rowsAdded . "<br /><br /><hr />";
        echo $echo;
    }

    return $rowsAdded;
}

function displayUserLookup($config) {
    //var_dump($_POST); //DEBUG
    //
    //Lookup Users button has been pressed
    if (isset($_GET['userLookup'])) {
        //save the calling form name to return to, unless it's the userLookup form
        /* if( strcmp($_POST['formName'], 'formName') )
          $_SESSION['callingForm']=$_POST['formName']; */
        $formName = isset($_POST['formName']) ? $_POST['formName']: '' ;
        echo '<form name="userLookup" action="' . $_SERVER['REQUEST_URI'] . '" method="POST" >';
        echo '<input type="hidden" name="formName" value="' . $formName . '" />';
        //from hidden value in calling form. this is where we want to return to

        //echo $formName; //DEBUG
        //Save any inputted values
        if ($formName == 'leave') {
            echo '<input type="hidden" name="subtype" value="' . $subtype . '" />';
            echo '<input type="hidden" name="ID" value="' . $postID . '" />';
            echo '<input type="hidden" name="usedate" value="' . $postUseDate . '" />';
            echo '<input type="hidden" name="thrudate" value="' . $postThruDate . '" />';
            echo '<input type="hidden" name="beg1" value="' . $postBeg1 . '" />';
            echo '<input type="hidden" name="beg2" value="' . $postBeg2 . '" />';
            echo '<input type="hidden" name="end1" value="' . $postEnd1 . '" />';
            echo '<input type="hidden" name="end2" value="' . $postEnd2 . '" />';
            echo '<input type="hidden" name="comment" value="' . $comment . '" />';
            echo '<input type="hidden" name="calloff" value="' . $_POST['calloff'] . '" />';
            echo '<input type="hidden" name="type" value="' . $_POST['type'] . '" />';
        } else if ($formName == 'secLog') {
            $secLogID = isset($_POST['secLogID']) ? $_POST['secLogID'] :'' ;
            $deputy = isset($_POST['deputy']) ? $_POST['deputy']:'';
            $radioNum = isset($_POST['radioNum']) ? $_POST['radioNum']:'';
            $address = isset($_POST['address']) ? $_POST['address'] : '';
            $city = isset($_POST['city']) ? $_POST['city'] : '';
            $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
            $shiftStart1 = isset($_POST['shiftStart1']) ? $_POST['shiftStart1'] : '';
            $shiftStart2 = isset($_POST['shiftStart2']) ? $_POST['shiftStart2']:'';
            $shiftEnd1 = isset($_POST['shiftEnd1']) ? $_POST['shiftEnd1'] : ''; 
            $shiftEnd2 = isset($_POST['shiftEnd2']) ? $_POST['shiftEnd2'] : '';
            $dress = isset($_POST['dress']) ? $_POST['dress']: '';
            $dateSelect = isset($_POST['dateSelect']) ? $_POST['dateSelect'] : '' ;
           
            echo '<input type="hidden" name="secLogID" value="'.$secLogID.'" />';
            echo '<input type="hidden" name="deputy" value="' . $deputy . '" />';
            echo '<input type="hidden" name="radioNum" value="' . $radioNum . '" />';
            echo '<input type="hidden" name="address" value="' . $address . '" />';
            echo '<input type="hidden" name="city" value="' . $city . '" />';
            echo '<input type="hidden" name="phone" value="' . $phone . '" />';
            echo '<input type="hidden" name="shiftStart1" value="' . $shiftStart1 . '" />';
            echo '<input type="hidden" name="shiftStart2" value="' . $shiftStart2 . '" />';
            echo '<input type="hidden" name="shiftEnd1" value="' . $shiftEnd1 . '" />';
            echo '<input type="hidden" name="shiftEnd2" value="' . $shiftEnd2 . '" />';
            echo '<input type="hidden" name="dress" value="' . $dress . '" />';
            echo '<input type="hidden" name="dateSelect" value="' . $dateSelect . '" />';
            //echo '<input type="hidden" name="goBtn" value="true" />';
            echo '<input type="hidden" name="addBtn" value="true" />';
        } 
        $mysqli = $config->mysqli;
        //Get additional search inputs
        $searchUser = isset($_POST['searchUser']) ? $mysqli->real_escape_string($_POST['searchUser']) : '';
        $isFullTime = isset($_POST['fullTime']) ? true : false;
        $isReserve = isset($_POST['reserve']) ? true : false;
        $searchFullTime = isset($_POST['searchFullTime']) ? $_POST['searchFullTime'] : true;
        $searchReserves = isset($_POST['searchReserves']) ? $_POST['searchReserves'] : true;
        if(strcmp($searchReserves,"false")== 0 )
            $searchReserves = false;
        if(strcmp($searchFullTime,"false") == 0)
            $searchFullTime = false;

        if($searchFullTime){
            echo '<input type="checkbox" name="fullTime" ';
            if ($isFullTime)
                echo 'CHECKED';
            echo ' />Full Time Employee&nbsp;&nbsp;  ';
        }
        if($searchReserves){
            echo '<input type="checkbox" name="reserve" ';
            if ($isReserve)
                echo 'CHECKED';
            echo ' />Reserves';
        }

        echo '<br /><input type="text" name="searchUser" value="' . $searchUser . '" />
            <input type="submit" name="findBtn" value="Search" /><br /><br />';
        //echo '</form>';

        if (isset($_POST['findBtn']) && !empty($searchUser)) {
            //echo '<form method="POST">';
            $rowCount = 0;
            if (!empty($searchUser) && $isFullTime)
                $rowCount = selectUserSearch($config, $searchUser, true);
            if ($isReserve)
                $rowCount2 = searchReserves($config, $searchUser, $rowCount);
            else
                $rowCount2 = $rowCount;
            $rowCount3 = searchDatabase($config, $searchUser, $rowCount2);
            $totalRowsFound = $rowCount + $rowCount2 + $rowCount3;

            echo '<input type="hidden" name="totalRows" value="' . $totalRowsFound . '" />';
            echo '</form>';
        }//end lookup button pressed     
    }//end search or lookup button pressed
    else //lookup button not pressed, show button to get to lookup page
        echo '<button type="button"  name="searchBtn" value="Lookup Users" onClick="this.form.action=' . "'?userLookup=true'" . ';this.form.submit()" >Lookup User</button>';
}

?>
