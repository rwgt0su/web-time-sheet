<?php
function connectToSQL(){
    //establish connetcion to 'PAYROLL' DB and return resource
    $mysqli = new mysqli("localhost", "web", "10paper", "PAYROLL");
    if ($mysqli->connect_errno) {
        $error = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        echo $error;
    }


    return $mysqli;
}

/* Build an HTML table from a mysqli result
 * Pass the link indentifier and the result
 */
function resultTable($mysqli, $result){

$numOfCols = $mysqli->field_count; //get number of columns

echo '<table border="1"><tr>';
//fetch and write field names
while($finfo = mysqli_fetch_field($result))
    echo "<th>" . $finfo->name . "</th>"; 

echo '</tr>'; //end the table heading record

$result->data_seek(0);  
while ($row = $result->fetch_array(MYSQLI_NUM))
   {
    echo '<tr>'; //begin data record
        for($fieldCounter=0; $numOfCols > $fieldCounter; $fieldCounter++)
        {
	 echo "<td>${row["$fieldCounter"]}</td>";
        } //loop through fields
    echo '</tr>'; //end data record
   } //loop through records

echo '</tr></table>';
}

?>
