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
//build an HTML table of results, or a form if edit button is pressed
function resultTable($mysqli, $result, $action){

    $numOfCols = $mysqli->field_count; //get number of columns
    $isEditBtn = isset($_POST['editBtn']);
    echo '<table border="1" id="pending"><tr>';
    //fetch and write field names
    $i = 0;
    $fieldNameArray = array();
    while($finfo = mysqli_fetch_field($result)) {
        echo "<th>" . $finfo->name . "</th>"; 
        $fieldNameArray[$i] = $finfo->name;
        $i++;
    }

    echo '</tr>'; //end the table heading record

    $result->data_seek(0);  
    while ($row = $result->fetch_array(MYSQLI_NUM))
    {   
        echo "<tr>";
        if ($isEditBtn) 
            echo "<form action='" . $action . "' method='post' name='saveBtn'>"; //begin data record and form
        for($fieldCounter=0; $numOfCols > $fieldCounter; $fieldCounter++)
        {
            if ($isEditBtn) {
                echo "<td><input type='text' name='$fieldCounter' value='${row["$fieldCounter"]}'></td>";
            }
            else {
                echo "<td>${row["$fieldCounter"]}</td>";
            }
        } //loop through fields
        if ($isEditBtn)
            echo '<td><input type="submit" name="saveBtn" value="Save" /></td>';
            
        echo '</tr></form>'; //end data record and form
       
       
        echo '</tr>'; //end data record
       
    } //loop through records

    echo '</tr></table>';
    
    //if($isEditBtn)
    //showDynamicTable('pending',$fieldNameArray); 

    }
