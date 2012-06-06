<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//establish connetcion to DB
$mysqli = new mysqli("localhost", "web", "10paper", "PAYROLL");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$ID=$_POST['ID'];

$myq = "SELECT * FROM EMPLOYEE WHERE ID=$ID";
$result = $mysqli->query($myq);
?>

Contents of employee table</br>
<table border="1">
<tr>
<?php
$numOfCols = mysql_num_fields($result);
//$numOfRows = mysql_num_rows($result);

for($i=0; $numOfCols > $i+1; $i++)
{
echo "<th>" . mysql_field_name($result, $i) . "</th>";
}
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