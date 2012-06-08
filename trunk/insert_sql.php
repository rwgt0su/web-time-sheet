<?php

/*
 * SQL statement that accepts POST data from
 * insert_user.php to inesrt a new employee record
 * 
 */
?>

<?php
$ID=$_POST['ID'];
$pass1=$_POST['pass1'];
$pass2=$_POST['pass1'];
$adminLvl=$_POST['sdminLvl'];

$msg = registerUser($ID,$pass1,$pass2,$adminLvl);
echo $msg;
?>

<?php
/*
//establish connetcion to DB
$mysqli = new mysqli("localhost", "web", "10paper", "PAYROLL");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

//echo $mysqli->host_info . "\n";


$ID=$_POST['ID'];
$PASSWD=$_POST['PASSWD'];
$MUNIS=$_POST['MUNIS'];
$LNAME=$_POST['LNAME'];
$FNAME=$_POST['FNAME'];
$GRADE=$_POST['GRADE'];
$DIVISION=$_POST['DIVISION'];
$SUPV=$_POST['SUPV'];
$ASSIGN=$_POST['ASSIGN'];
$TIS=$_POST['TIS'];
$PTISBEG=$_POST['PTISBEG'];
$PTISEND=$_POST['PTISEND'];
$RADIO=$_POST['RADIO'];
$BADGE=$_POST['BADGE'];

//build the insert statement
$myq = "INSERT INTO EMPLOYEE VALUES ('$ID', '$PASSWD', '$MUNIS', 
        '$LNAME', '$FNAME', '$GRADE', '$DIVISION', '$SUPV', '$ASSIGN',
        '$TIS', '$PTISBEG', '$PTISEND', '$RADIO', '$BADGE')";

//send the query
$result = $mysqli->query($myq);

//if query successful, say so
if ($result)
    echo "New record successfully inserted.";*/
?>