<?php
Class wts_content {
    public $isWelcome;
    public $isLogout;
    public $isDBTest;
    public $isLeaveForm;
    public $isPending;
    public $isLeaveApproval;
    public $isHome;
    public $isUserMenu;
    public $isAnounceAdmin;
    public $isSearching;
    
    public function wts_content(){
        //get passed variables based on URL
        $this->isWelcome = isset($_GET['welcome']) ? $_GET['welcome'] : false;
        $this->isLogout = isset($_GET['logout']) ? $_GET['logout'] : false;
        $this->isDBTest = isset($_GET['dbtest']) ? $_GET['dbtest'] : false;
        $this->isLeaveForm = isset($_GET['leave']) ? $_GET['leave'] : false;
        $this->isPending = isset($_GET['pending']) ? $_GET['pending'] : false;
        $this->isLeaveApproval = isset($_GET['approve']) ? $_GET['approve'] : false;
        $this->isInsertUser = isset($_GET['newuser']) ? $_GET['newuser'] : false;
        $this->isUserMenu = isset($_GET['usermenu']) ? $_GET['usermenu'] : false;
        $this->isAnounceAdmin = isset($_GET['isAnounceAdmin']) ? $_GET['isAnounceAdmin'] : false;
        $this->isSearching = isset($_POST['searchInput']) ? $_POST['searchInput'] : false;
        
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