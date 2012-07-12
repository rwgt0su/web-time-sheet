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
function resultTable($mysqli, $result){

    //get the current page name to use as form action
    $action = $_SERVER['REQUEST_URI'];
    
    $numOfCols = $mysqli->field_count; //get number of columns
    $isEditBtn = isset($_POST['editBtn']);
    echo '<table border="1" ><tr>';
    //fetch and write field names
    $i = 0;
    $fieldNameArray = array(); //to store original column names as in SQL
    $fieldNameAliasArray = array(); //to store column aliases applied in a query
    $tableNameArray = array(); //to store original table name
    $result->data_seek(0);
    while($finfo = mysqli_fetch_field($result)) {
        echo "<th>" . $finfo->name . "</th>"; 
        $fieldNameArray[$i] = $finfo->orgname;
        $fieldNameAliasArray[$i] = $finfo->name;
        $tableNameArray[$i] = $finfo->orgtable; 
        $fieldTypeArray[$i] = $finfo->type;
        $i++;
    }

    echo '</tr>'; //end the table heading record

    $result->data_seek(0);  
    while ($row = $result->fetch_assoc())
    {   
        echo "<tr>";
        if ($isEditBtn) 
            echo "<form action='" . $action . "' method='post' name='saveBtn'>"; //begin data record and form
        
        for($fieldCounter=0; $numOfCols > $fieldCounter; $fieldCounter++)
        {
            if ($isEditBtn) {
                if(!dropDownMenu($mysqli, $fieldNameArray[$fieldCounter], $tableNameArray[$fieldCounter], $row["$fieldNameAliasArray[$fieldCounter]"],$fieldNameAliasArray[$fieldCounter]))
                    echo "<td><input type='text' name='$fieldNameAliasArray[$fieldCounter]' value='${row["$fieldNameAliasArray[$fieldCounter]"]}'></td>";
            }
            else { //one cell of data
                echo "<td style='white-space: nowrap'>${row["$fieldNameAliasArray[$fieldCounter]"]}</td>";
                //echo "<td><a href='".$_SERVER['REQUEST_URI']."&editRecord=true'>${row["$fieldNameAliasArray[$fieldCounter]"]}</a></td>";
            }
        } //loop through fields
        if ($isEditBtn){
            echo '<td><input type="submit" name="saveBtn" value="Save" /></td>';
            echo '</tr></form>'; //end data record and form
        }
                      
        echo '</tr>'; //end data record
       
    } //loop through records

    echo '</tr></table>';
    ?>
    <form action="<?php echo $action; ?>" method="post" name="editBtn">
    <?php 
    //only let supervisors or higher edit requests
    if ($_SESSION['admin'] > 0)
        echo "<p><input type='submit' name='editBtn' value='Edit'></p></form>";
    
    //write any updates to DB when Save is pressed
    if (isset($_POST['saveBtn'])) { 
        //$result = $mysqli->query($myq);
        
        //construct assoc array of user provided values in a format useful for SQL
        $values = array();
        //print_r($fieldNameArray); //DEBUG
       for($i=0; $i < $numOfCols; $i++) {
           //fields that are not allowed to be edited
          if( !($fieldNameArray[$i] == 'AUDITID' || $fieldNameArray[$i] == 'IP' || $fieldNameArray[$i] == 'STATUS' || $fieldNameArray[$i] == 'APPROVEDBY' || $fieldNameArray[$i] == 'REQDATE' || $fieldNameArray[$i] == 'TSTAMP') ) {
            if($fieldNameArray[$i] == 'DESCR')
                //append ID to the table name to get correct fieldname (this requires a DB naming convention to be followed
                $values["$fieldNameArray[$i]"] = $tableNameArray[$i] . "ID="."'". $mysqli->real_escape_string($_POST["$fieldNameAliasArray[$i]"])."'";
            else if (/*type is date*/)
                //assign it as a datetime obj
            else
                $values["$fieldNameArray[$i]"] = $fieldNameArray[$i] ."="."'". $mysqli->real_escape_string($_POST["$fieldNameAliasArray[$i]"])."'";
          }            
        }
        
        $csvValues = implode(',' , $values);
     
        $updateQuery = "UPDATE ".$tableNameArray[0]." SET ".$csvValues." 
                    WHERE " . $values["$fieldNameArray[0]"];
        echo "<br>" . $updateQuery;  //DEBUG
        //send the update
        $updateResult = $mysqli->query($updateQuery);
        SQLerrorCatch($mysqli, $updateResult);
        
    } 
}

/*  
 * This functionwill insert a drop down menu where needed
 * after the edit button is pressed.
 * Each foreign key field will need its own query.
 * The query must return at least 2 columns.
 *  The first is the value of the select 
 *  and the second gets displayed
 */
function dropDownMenu($mysqli, $fieldName, $tableName, $value, $formName) {
    if (!strcmp($fieldName, 'DESCR') && !strcmp($tableName, 'TIMETYPE')) {
        $myq="SELECT TIMETYPEID, DESCR FROM TIMETYPE";
        
        $result = $mysqli->query($myq);

        //show SQL error msg if query failed
        SQLerrorCatch($mysqli, $result);
    
    //build a drop-down from query result
    ?>
     <td> <select name="<?php echo $formName; ?>" onchange="window.location=window.location.href + '&type=' + this.value">
    <?php
    }
    else if (!strcmp($fieldName, 'FULLNAME') && !strcmp($tableName, 'EMPLOYEE')) {
        
        $myq = "SELECT ID, CONCAT_WS(', ',LNAME,FNAME) FULLNAME FROM EMPLOYEE ORDER BY LNAME";
        $result = $mysqli->query($myq);

        //show SQL error msg if query failed
        SQLerrorCatch($mysqli, $result);
        
        echo "<td> <select name=".$formName.">";
    }
    else 
        return false;
    
    //store the original column name, and an alias if it was used
    $fieldNameArray = array();
    for($i=0;$finfo = $result->fetch_field();$i++) {
        $fieldNameArray[$i] = $finfo->orgname;
        $fieldNameAliasArray[$i] = $finfo->name;
    }
    
    $isBlankOption = false; //haven't written a blank option yet
    $result->data_seek(0);  
    while ($row = $result->fetch_assoc()) 
    {        // only write blank option once
            if ($value == FALSE && !$isBlankOption) {
                echo '<option value="" style="display:none;" selected="selected"></option>';
                $isBlankOption = true;
            }
            
            //store the original name or an alias (using an alias makes orgname empty)
            $name = array();
            $name[0] = empty($fieldNameArray[0]) ? $fieldNameAliasArray[0] : $fieldNameArray[0];
            $name[1] = empty($fieldNameArray[1]) ? $fieldNameAliasArray[1] : $fieldNameArray[1];
            //$type  = isset($_GET['type']) ? $_GET['type'] : '';
            
            echo '<option value="' . $row["$name[0]"] . '"';
            //pre-select the right value
            if (!strcasecmp($value, $row["$name[1]"]) || !strcasecmp($value, $row["$name[0]"]))
                echo ' selected="selected"';
            
            echo '>' . $row["$name[1]"] . '</option>';
    }
    ?>
    </select> </td>
    <?php
    
    return true;

   
}

//try/catch providing db errors in a pop-up window
//returns true if an error is caught, false if not
function SQLerrorCatch($mysqli, $result) {
    try {
            if (!$result) 
            throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
        }
        catch (Exception $e) {
            $message = $e->getMessage();
            popUpMessage($message);
            return true;
        }
    return false;
}
?>