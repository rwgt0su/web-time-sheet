<?php

function searchPage($config){
    $searchInput = isset($_POST['searchInput']) ? $_POST['searchInput'] : false;
    if($searchInput){
        echo '<h3>Results for: '.$searchInput.'</h3>';
        $rowCount1 = selectUserSearch($config, $searchInput);
        $rowCount2 = searchDatabase($config, $searchInput, $rowCount1,true,false);
        $rowCount3 = $rowCount1 + $rowCount2;
        $rowCount3 = searchReserves($config, $searchInput, $rowCount3, false);
        $rowCount3 = $rowCount1 + $rowCount2 + $rowCount3;
        echo "Total Number of entries found is " . $rowCount3 . "<br /><br /><hr />";
    }
    else {
        echo 'No information provided';
    }
}

function selectUserSearch($config, $userToFind, $select = false){
    //LDAP Search
    $cnx = ldap_connect($config->ldap_server);
    $user = $config->ldapUser;
    $pass = $config->ldapPass;
    $ldaprdn = $user . '@' . $config->domain;
    ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
    ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
    $i=0;
    if($ldapbind = ldap_bind($cnx, $ldaprdn, $pass)){ 
        //Split given domain into LDAP Base DN
        $temp = explode(".", $config->domain);
        $i = 0;
        $dn = null;
        foreach ($temp as $dc){ 
            if(empty($dn))
                $dn = "DC=".$dc;
            else
                $dn = $dn.",DC=".$dc;
            $i++;
        }
        error_reporting (E_ALL ^ E_NOTICE);   //Suppress some unnecessary messages
        $filter="(&(objectCategory=person)(objectClass=user)";
        $filter.="(|(samaccountname=*".$userToFind."*)(sn=*".$userToFind."*)(displayname=*".$userToFind."*)";
        $filter.="(mail=*".$userToFind."*)(department=*".$userToFind."*)(title=*".$userToFind."*)))";  //Search fields
        $res=ldap_search($cnx, $dn, $filter);
        
        $totalRows = ldap_count_entries($cnx, $res);
        $info = ldap_get_entries($cnx, $res);
        $i=0;
        echo "Number of entries in Active Directory returned is " . $totalRows . "<br /><br /><hr />";
        for ($i; $i<$info["count"]; $i++) {
            //echo "dn is: " . $info[$i]["dn"] . "<br />";
            echo '<div align="center"><table width="400"><tr><td>';
            if($select)
                echo '<input name="foundUser'.$i.'" type="radio" onclick="this.form.submit();" />Select</td><td>';
            echo "Display Name: " . $info[$i]["displayname"][0] . "<br />";
            echo "First name: " . $info[$i]["givenname"][0] . "<br />";
            echo "Last Name: " . $info[$i]["sn"][0] . "<br />";
            echo '<input type="hidden" name="foundUserName'.$i.'" value="'.$info[$i]["samaccountname"][0].'" /> Username: ' . $info[$i]["samaccountname"][0] . '<br />';
            //Check user in Employee Database and output IDNUM if found
            $result = searchDatabase($config, $info[$i]["samaccountname"][0], $i, false);
            if($result < 1){
                //User not in database, so register the user
                registerUser($info[$i]["samaccountname"][0], "temp01", "temp01", 0, 1);
            }
            //Get user's IDNUM
            $mysqli = $config->mysqli;
            $myq = "SELECT *
                FROM `EMPLOYEE`
                WHERE `ID` =  '".strtoupper($info[$i]["samaccountname"][0])."'";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            $row = $result->fetch_assoc();
            echo "Rank: " . $row['GRADE'] . "<br />";
            echo "Department: " . $row['DESCR'] . "<br />";
            
            if($result < 1){    
                //Update newly created user's information with their Active Directory Info
                $myq = "UPDATE `PAYROLL`.`EMPLOYEE` SET 
                    `LNAME` = '".$info[$i]["sn"][0]."',
                    `FNAME` = '".$info[$i]["givenname"][0]."'
                    WHERE EMPLOYEE.IDNUM = '".$row['IDNUM']."'";
                //Perform SQL Query
                $result = $mysqli->query($myq);

                //show SQL error msg if query failed
                if (!SQLerrorCatch($mysqli, $result)) 
                    $result = "Successfully Updated Profile";
            }
            echo "Title: " . $info[$i]["title"][0] . "<br />";
            echo "Department: " . $info[$i]["department"][0] . "<br />";
            echo "Email: " . $info[$i]["mail"][0] . "<br />";
            echo '<input type="hidden" name="foundUserID'.$i.'" value="'.$row['IDNUM'].'" />';
            echo "</td></tr></table></div><br /><hr />";
        } 
    }
    else
        popUpMessage ("Could Not Bind to LDAP to perform search");
    
    return $totalRows;
}
function searchDatabase($config, $userToFind, $rowCount, $isSearching=true, $isSelect=true){
    
    $mysqli = $config->mysqli;
    if($isSearching)
        $myq = "SELECT * FROM `EMPLOYEE` WHERE `ID` LIKE '%".strtoupper($userToFind)."%' AND `isLDAP` !=1";
    else
        $myq = "SELECT * FROM `EMPLOYEE` WHERE `ID` LIKE '%".strtoupper($userToFind)."%'";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $begin = $rowCount;
    $echo = "";
    
    while($row = $result->fetch_assoc()) {
        $rowCount++;
        if(!$row['isLDAP'] || !isset($_POST['fullTime'])){
            if($isSearching){
                $echo .= '<div align="center"><table width="400"><tr><td>';
                if($isSelect)
                    $echo .= '<input name="foundUser'.$rowCount.'" type="radio" onclick="this.form.submit();" />Select</td><td>';
                $echo .= '<input type="hidden" name="foundUserFNAME'.$rowCount.'" value="'.$row['FNAME'].'" /> First name: ' . $row['FNAME'] . "<br />";
                $echo .= '<input type="hidden" name="foundUserLNAME'.$rowCount.'" value="'.$row['LNAME'].'" /> Last Name: ' . $row['LNAME'] . "<br />";
                $echo .= '<input type="hidden" name="foundUserName'.$rowCount.'" value="'.$row['ID'].'" /> Username: ' . $row['ID'] . '<br />';
            }
            $echo .= '<input type="hidden" name="foundUserID'.$rowCount.'" value="'.$row['IDNUM'].'" />';
            $echo .= "Rank: " . $row['GRADE'] . "<br />";
            $echo .= "Department: " . $row['DESCR'] . "<br />";
            if($isSearching)
                $echo .= "</td></tr></table></div><br /><hr />";
        }//end is in LDAP
    }//end While Loop
    $rowsAdded = $rowCount - $begin;
    if($rowsAdded > 0){
        if($isSearching)
            echo "Number of entries found in the Full Time Employee database is " . $rowsAdded. "<br /><br /><hr />";
        echo $echo;
    }
    
    return $rowsAdded;
}
function searchReserves($config, $userToFind, $rowCount, $isSelect=true){
    $mysqli = connectToSQL($reserveDB = TRUE);
    if($config->adminLvl < 75)
        $myq = "SELECT *  FROM `RESERVE` WHERE `GRP` != 5 AND `LNAME` LIKE CONVERT(_utf8 '%".$userToFind."%' USING latin1) COLLATE latin1_swedish_ci ";
    else
        $myq = "SELECT *  FROM `RESERVE` WHERE `LNAME` LIKE CONVERT(_utf8 '%".$userToFind."%' USING latin1) COLLATE latin1_swedish_ci ";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $begin = $rowCount;
    $echo = "";
    
    while($row = $result->fetch_assoc()) {
        $rowCount++;
        $echo .= '<div align="center"><table width="400"><tr><td>';
        if($isSelect)
            $echo .= '<input name="foundUser'.$rowCount.'" type="radio" onclick="this.form.submit();" />Select</td><td>';
        $echo .= '<input type="hidden" name="foundUserFNAME'.$rowCount.'" value="'.$row['FNAME'].'" /> First name: ' . $row['FNAME'] . "<br />";
        $echo .= '<input type="hidden" name="foundUserLNAME'.$rowCount.'" value="'.$row['LNAME'].'" /> Last Name: ' . $row['LNAME'] . "<br />";
        $echo .= '<input type="hidden" name="foundUserID'.$rowCount.'" value="'.$row['IDNUM'].'" /> Username: ' . $row['FNAME'].".".$row['LNAME'] . '<br />';
        $echo .= '<input type="hidden" name="foundUserName'.$rowCount.'" value="'.$row['FNAME'].".".$row['LNAME'].'" />';
        $echo .= "Rank: Reserve Group " . $row['GRP'] . "<br />";
        $echo .= "</td></tr></table></div><br /><hr />";
    }//end While Loop
    $rowsAdded = $rowCount - $begin;
    if($rowsAdded > 0){
        echo "Number of entries found in the reserve database is " . $rowsAdded. "<br /><br /><hr />";
        echo $echo;
    }
    
    return $rowsAdded;
}
?>
