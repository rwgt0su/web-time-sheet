<?php

function searchPage($config){
    $searchInput = isset($_POST['searchInput']) ? $_POST['searchInput'] : false;
    if($searchInput){
        echo '<h3>Results for: '.$searchInput.'</h3>';
        searchLDAP($config, $searchInput);
    }
    else {
        echo 'No information provided';
    }
}
function searchLDAP($config, $userToFind){
    $cnx = ldap_connect($config->ldap_server);
    $user = $config->ldapUser;
    $pass = $config->ldapPass;
    $ldaprdn = $user . '@' . $config->domain;
    ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
    ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
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
        $filter="(|(samaccountname=*".$userToFind."*)(sn=*".$userToFind."*)(displayname=*".$userToFind."*)";
        $filter.="(mail=*".$userToFind."*)(department=*".$userToFind."*)(title=*".$userToFind."*))";  //Search fields
        $res=ldap_search($cnx, $dn, $filter);
        
        echo "Number of entries returned is " . ldap_count_entries($cnx, $res) . "<br /><br /><hr />";
        $info = ldap_get_entries($cnx, $res);
        for ($i=0; $i<$info["count"]; $i++) {
            //echo "dn is: " . $info[$i]["dn"] . "<br />";
            echo "Display Name: " . $info[$i]["displayname"][0] . "<br />";
            echo "First name: " . $info[$i]["givenname"][0] . "<br />";
            echo "Last Name: " . $info[$i]["sn"][0] . "<br />";
            echo "Username: " . $info[$i]["samaccountname"][0] . "<br />";
            echo "Title: " . $info[$i]["title"][0] . "<br />";
            echo "Department: " . $info[$i]["department"][0] . "<br />";
            echo "Email: " . $info[$i]["mail"][0] . "<br />";
            echo "<br /><hr />";
        }
       
    }
    else
        $result = "Could Not Bind to LDAP to perform search";
}
function selectUserSearch($config, $userToFind){
    //LDAP Search
    $cnx = ldap_connect($config->ldap_server);
    $user = $config->ldapUser;
    $pass = $config->ldapPass;
    $ldaprdn = $user . '@' . $config->domain;
    ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);  //Set the LDAP Protocol used by your AD service
    ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);         //This was necessary for my AD to do anything
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
        $filter="(|(samaccountname=*".$userToFind."*)(sn=*".$userToFind."*)(displayname=*".$userToFind."*)";
        $filter.="(mail=*".$userToFind."*)(department=*".$userToFind."*)(title=*".$userToFind."*))";  //Search fields
        $res=ldap_search($cnx, $dn, $filter);
        
        $totalRows = ldap_count_entries($cnx, $res);
        echo "Number of entries returned is " . $totalRows . "<br /><br /><hr />";
        echo '<input type="hidden" name="totalRows" value="'.$totalRows.'" />';
        $info = ldap_get_entries($cnx, $res);
        for ($i=0; $i<$info["count"]; $i++) {
            //echo "dn is: " . $info[$i]["dn"] . "<br />";
            echo '<div align="center"><table><tr><td><input name="foundUser'.$i.'" type="radio" onclick="this.form.submit();" />Select</td><td>';
            echo "Display Name: " . $info[$i]["displayname"][0] . "<br />";
            echo "First name: " . $info[$i]["givenname"][0] . "<br />";
            echo "Last Name: " . $info[$i]["sn"][0] . "<br />";
            echo '<input type="hidden" name="foundUserName'.$i.'" value="'.$info[$i]["samaccountname"][0].'" /> Username: ' . $info[$i]["samaccountname"][0] . '<br />';
            echo "Title: " . $info[$i]["title"][0] . "<br />";
            echo "Department: " . $info[$i]["department"][0] . "<br />";
            echo "Email: " . $info[$i]["mail"][0] . "<br />";
            echo "</td></tr></table></div><br /><hr />";
        }
        
       
    }
    else
        $result = "Could Not Bind to LDAP to perform search";
}
?>
