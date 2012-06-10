<?php
Class Config {
	public $webTitle;
	public $version;
	public $admin;
	private $mysqli;

	public function Config(){
		$this->mysqli = connectToSQL();
		$myq = "SELECT * FROM CONFIG;";
		$result = $this->mysqli->query($myq);
		$result->data_seek(0);  //moves internal pointer to 0, fetch starts here
		while ($row = $result->fetch_assoc()) //fetch assoc array && pointer++
		{
			if (strcmp($row['Variable'], "WebTitle") == 0){
				$this->webTitle = $row['Value'];
			}
			if (strcmp($row['Variable'], "Ver") == 0){
				$this->version = $row['Value'];
			}
		}	
	}
	public function getTitle(){
		return $this->webTitle;
	}
	public function getVersion(){
		return $this->version;
	}
	public function setAdmin($var){
		$this->admin = $var;
	}
	public function getAdmin(){
		return $this->admin;
	}
}


?>
