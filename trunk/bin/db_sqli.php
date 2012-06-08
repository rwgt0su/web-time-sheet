<?php
function connectToSQL(){
    //establish connetcion to DB
    $mysqli = new mysqli("localhost", "web", "10paper", "PAYROLL");
    if ($mysqli->connect_errno) {
        $error = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        echo $error;
    }


    return $mysqli;
}

?>
