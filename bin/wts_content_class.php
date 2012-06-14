<?php
Class wts_content {
    public $isWelcome;
    public $isLogout;
    public $isDBTest;
    public $isLeaveForm;
    public $isPending;
    
    public function wts_content(){
        //get passed variables based on URL
        $this->isWelcome = isset($_GET['welcome']) ? $_GET['welcome'] : false;
        $this->isLogout = isset($_GET['logout']) ? $_GET['logout'] : false;
        $this->isDBTest = isset($_GET['dbtest']) ? $_GET['dbtest'] : false;
        $this->isLeaveForm = isset($_GET['leave']) ? $_GET['leave'] : false;
        $this->isPending = isset($_GET['pending']) ? $_GET['pending'] : false;
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
