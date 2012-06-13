<?php

/*
 * A form to collect data from end user 
 * to begin the leave request process
 * 
 */
?>

<?php
//query to get currently available types of time 
$mysqli = connectToSQL();
$myq="SELECT TIMETYPEID, DESCR FROM TIMETYPE";
$result = $mysqli->query($myq);

//show SQL error msg if query failed
if (!$result) {
throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
}
?>

<html><body>
<form name="leave" method="post" action="leave_sql.php">
	<h1>Employee Leave Request</h1>
        
	<p>User ID:<input type="text" name="ID" value="<?php echo $_SESSION['userName']; ?>"></p>
        <p>Date of use/accumulation<input type="text" name="usedate" value="YYYY-MM-DD"></p>
        <p>Number of hours:<input type="text" name="hours"></p>
        <p>Time type: <select name="type">
        <?php
            //build a drop-down from query result
            $result->data_seek(0);  
            while ($row = $result->fetch_assoc()) 
               {
                 echo '<option value="' . $row['TIMETYPEID'] . '">' . $row['DESCR'] . '</option>';
               }
        ?>
               </select></p>
        <p>Comment:<input type="text" name="comment"></p>

	<p><input type="submit" name="Submit" value="Submit"></p>
</form>
</body></html>
