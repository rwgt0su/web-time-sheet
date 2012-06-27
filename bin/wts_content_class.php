<?php
Class wts_content {
    public $isWelcome;
    public $isLogout;
    public $isDBTest;
    public $isLeaveForm;
    public $isSubmittedRequests;
    public $isLeaveApproval;
    public $isHome;
    public $isUserMenu;
    public $isAnounceAdmin;
    public $isSearching;
    public $isUpdateProfile; 
    
    public function wts_content(){
        //get passed variables based on URL
        $this->isWelcome = isset($_GET['welcome']) ? $_GET['welcome'] : false;
        $this->isLogout = isset($_GET['logout']) ? $_GET['logout'] : false;
        $this->isDBTest = isset($_GET['dbtest']) ? $_GET['dbtest'] : false;
        $this->isLeaveForm = isset($_GET['leave']) ? $_GET['leave'] : false;
        $this->isSubmittedRequests = isset($_GET['submittedRequests']) ? $_GET['submittedRequests'] : false;
        $this->isLeaveApproval = isset($_GET['approve']) ? $_GET['approve'] : false;
        $this->isUserMenu = isset($_GET['usermenu']) ? $_GET['usermenu'] : false;
        $this->isAnounceAdmin = isset($_GET['isAnounceAdmin']) ? $_GET['isAnounceAdmin'] : false;
        $this->isSearching = isset($_POST['searchInput']) ? $_POST['searchInput'] : false;
        $this->isUpdateProfile = isset($_GET['updateProfile']) ? $_GET['updateProfile'] : false;
        
        if(empty($_GET)){
            $this->isHome = true;
        }
        else
            $this->isHome = false;
        //popUpMessage("isHome: ".$this->isHome);
    }
    
    public function isWelcome(){
        return $this->isWelcome;
    }
    
    public function isLogout(){
        return $this->isLogout;
    }
    
    public function isDBTest(){
        return $this->isDBTest;
    }
}
?>
