<?php

/*
 * Form used to inert a new employee
 * 
 */
?>

<html><body>
<form name="insert" method="post" action="insert_sql.php">
	<h1>Insert new employee</h1>
        <h2>Currently <b>ALL</b> fields must be filled for proper operation!</h2>
	<p>Login ID:<input type="text" name="ID"></p>
	<p>Password:<input type="password" name="PASSWD"></p>
        <p>MUNIS #:<input type="text" name="MUNIS"></p>
        <p>Last name:<input type="text" name="LNAME"></p>
        <p>First name:<input type="text" name="FNAME"></p>
        <p>Rank:<input type="text" name="GRADE"></p>
        <p>Division:<input type="text" name="DIVISION"></p>
        <p>Supervisor:<input type="text" name="SUPV"></p>
        <p>Assignment:<input type="text" name="ASSIGN"></p>
        <p>Time in service:<input type="text" name="TIS"></p>
        <p>Begin prior time in service:<input type="text" name="PTISBEG"></p>
        <p>End prior time in service:<input type="text" name="PTISEND"></p>
        <p>Radio #:<input type="text" name="RADIO"></p>
        <p>Badge:<input type="text" name="BADGE"></p>
        
	<p><input type="submit" name="Submit" value="Submit"></p>
</form>
</body></html>
