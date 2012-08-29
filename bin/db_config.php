<?php
Class Config {
	public $webTitle;
	public $version;
	public $adminLvl;
	public $mysqli;
        public $domain;
        public $ldapUser;
        public $ldapPass;
        public $ldap_server;
        public $ldap_MCO_server;
        public $ldap_MCO_domain;
        public $ldap_MCO_OU;
        public $installYear;

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
                        if (strcmp($row['Variable'], "Domain") == 0){
				$this->domain = $row['Value'];
			}
                        if (strcmp($row['Variable'], "ldap_server") == 0){
				$this->ldap_server = $row['Value'];
			}
                        if (strcmp($row['Variable'], "ldap_user") == 0){
				$this->ldapUser = $row['Value'];
			}
                        if (strcmp($row['Variable'], "ldap_user_pass") == 0){
				$this->ldapPass = $row['Value'];
			}
                        if (strcmp($row['Variable'], "install_year") == 0){
				$this->installYear = $row['Value'];
			}
		}
                //Prepare for Mahoning County Domain Migration
                $this->ldap_MCO_domain = "mahoningcountyoh.gov";
                $this->ldap_MCO_server = "10.2.35.25";
                $this->ldap_MCO_OU = "OU=Sheriff,OU=Departments,";
	}
	public function getTitle(){
		return $this->webTitle;
	}
	public function getVersion(){
		return $this->version;
	}
	public function setAdmin($var){
		$this->adminLvl = $var;
	}
}

function showInputBoxError(){
    echo 'style="background:#FFFFFF;border:1px solid #FF0000"';
}


?>
