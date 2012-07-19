<?php

function searchPage($config){
    $searchInput = isset($_POST['searchInput']) ? $_POST['searchInput'] : false;
    if($searchInput){
        echo '<h3>Results for: '.$searchInput.'</h3>';
        selectUserSearch($config, $searchInput);
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
            //get User's IDNUM from the database
            $result = searchDatabase($config, $info[$i]["samaccountname"][0], 0);
            if($result < 1){
                registerUser($info[$i]["samaccountname"][0], "temp01", "temp01", 0, 1);
            }
            $mysqli = $config->mysqli;
            $myq = "SELECT *
                FROM `EMPLOYEE`
                WHERE `ID` =  '".$info[$i]["samaccountname"][0]."'";
            $result = $mysqli->query($myq);
            SQLerrorCatch($mysqli, $result);
            $row = $result->fetch_assoc();
            echo '<input type="hidden" name="foundUserID" value="'.$row['IDNUM'].'" />';
            echo "Title: " . $info[$i]["title"][0] . "<br />";
            echo "Department: " . $info[$i]["department"][0] . "<br />";
            echo "Email: " . $info[$i]["mail"][0] . "<br />";
            echo "</td></tr></table></div><br /><hr />";
        } 
    }
    else
        popUpMessage ("Could Not Bind to LDAP to perform search");
    return $totalRows;
}
function searchDatabase($config, $userToFind, $rowCount){
    
    $mysqli = $config->mysqli;
    $myq = "SELECT * , DIVISION.DESCR
        FROM `EMPLOYEE`
        LEFT JOIN DIVISION ON EMPLOYEE.DIVISIONID = DIVISION.DIVISIONID
        WHERE `ID` LIKE '%".strtoupper($userToFind)."%'";
    $result = $mysqli->query($myq);
    SQLerrorCatch($mysqli, $result);
    $begin = $rowCount;
    $echo = "";
    
    while($row = $result->fetch_assoc()) {
        if(!$row['isLDAP'] || !isset($_POST['fullTime'])){
            $rowCount++;
            $echo .= '<div align="center"><table><tr><td><input name="foundUser'.$rowCount.'" type="radio" onclick="this.form.submit();" />Select</td><td>';
            $echo .= "First name: " . $row['FNAME'] . "<br />";
            $echo .= "Last Name: " . $row['LNAME'] . "<br />";
            $echo .= '<input type="hidden" name="foundUserID'.$rowCount.'" value="'.$row['IDNUM'].'" /> Username: ' . $row['ID'] . '<br />';
            $echo .= '<input type="hidden" name="foundUserName'.$rowCount.'" value="'.$row['ID'].'" />';
            $echo .= "Rank: " . $row['GRADE'] . "<br />";
            $echo .= "Department: " . $row['DESCR'] . "<br />";
            $echo .= "</td></tr></table></div><br /><hr />";
        }//end is in LDAP
    }//end While Loop
    $rowsAdded = $rowCount - $begin;
    echo "Number of entries already in the database returned is " . $rowsAdded. "<br /><br /><hr />";
    echo $echo;
    
    return $result->num_rows;
}
?>
