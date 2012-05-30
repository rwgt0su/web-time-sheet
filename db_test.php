<?php
$mysqli = new mysqli("localhost", "web", "10paper", "test");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
echo $mysqli->host_info . "\n";

$myq = "SELECT * FROM EMP;";
$result = $mysqli->query($myq);
?>

Contents of employee table</br>
<table border="1">
<tr>
<th>ID</th>
<th>First</th>
<th>Last</th>
</tr>

<?php
$result->data_seek(0);  //moves internal pointer to 0, fetch starts here
while ($row = $result->fetch_assoc()) //fetch assoc array && pointer++
   {
	 echo "<tr><td>" . $row['EID'] . "</td><td>" . $row['FNAME'] . "</td><td>" . $row['LNAME'] . "</td></tr>";
   }
?>

</table>
