<?php

  /* script to authenticate using ldap bind */

$ldaprdn  = $_POST['user'] . '@sheriff.mahoning.local';     // ldap rdn or dn
$ldappass = $_POST['pass'];  // associated password

if (isset($_POST['Submit']))
    echo "Submit button pressed!";

if (!isset($ldaprdn) || !isset($ldappass))
{
  echo "One or more fields blank. Bound anonymously</br>";
}

// connect to ldap server
$ldapconn = ldap_connect("10.1.35.110")
    or die("Could not connect to LDAP server.");

  echo $ldaprdn . "</br>";

if ($ldapconn)
  {
    // binding to ldap server
    $ldapbind = ldap_bind($ldapconn, $ldaprdn, $ldappass);
    //$ldapbind = ldap_bind($ldapconn); //anonymous

    // verify binding
    if ($ldapbind) {
        echo "LDAP bind successful...";
    } else {
        echo "LDAP bind failed...";
    }

  }

?>
