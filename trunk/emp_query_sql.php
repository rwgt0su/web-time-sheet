<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//show errors
error_reporting(E_ALL);
ini_set("display_errors", 1);

//establish connetcion to DB
$mysqli = new mysqli("localhost", "web", "10paper", "PAYROLL");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$ID=$_POST['ID'];

$myq = "SELECT * FROM EMPLOYEE WHERE ID='" . $ID . "';";
echo $myq . "</br>";
$result = $mysqli->query($myq);
?>

Contents of employee table</br>
<table border="1">
<tr>
<?php
$numOfCols = $mysqli->field_count;
echo "Columns:" . $numOfCols . "/<br>";
//$numOfRows = mysql_num_rows($result);

while($finfo = mysqli_fetch_field($result))
    echo "<th>" . $finfo->name . "</th>";

/*for($i=0; $numOfCols > $i; $i++)
{
echo "<th>" . mysql_field_name($result, $i) . "</th>";
}*/
?>
    
</tr>

<tr>
<?php

$fieldCounter=0;

$result->data_seek(0);  //moves internal pointer to 0, fetch starts here
while ($row = $result->fetch_assoc()) //fetch assoc array && pointer++
   {
    $fieldName=mysql_field_name($result, $fieldCounter);
	 echo "<td>${row["$fieldName"]}</td>";
         $fieldCounter++;
   }
?>
</tr>
</table>
