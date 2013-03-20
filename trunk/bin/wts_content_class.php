<?php
Class wts_content {
    public $isWelcome;
    public $isLogout;
    public $isLeaveForm;
    public $isSubmittedRequests;
    public $submittedRequestsNEW;
    public $isLeaveApproval;
    public $isHome;
    public $isUserMenu;
    public $isAnounceAdmin;
    public $isSearching;
    public $isUpdateProfile; 
    public $isLookup; 
    public $isUseReport;
    public $isAbout;
    public $isPhpMyEdit;
    public $isMUNIS;
    public $isSecLog;
    public $isUserLookup;
    public $isResManage;
    public $isUserVerify;
    public $isMySubmitReq;
    public $isReports;
    public $isApprovedUseReport;
    public $subReqCal;
    public $hrEmpRep;
    public $isSickRep;
    public $isEventLogs;
    public $isOTRep;
    public $isRadioLog;
    public $isMyInv;
    public $isSecLogRep;
    
    public function wts_content(){
        //get passed variables based on URL
        $this->isWelcome = isset($_GET['welcome']) ? $_GET['welcome'] : false;
        $this->isLogout = isset($_GET['logout']) ? $_GET['logout'] : false;
        $this->isLeaveForm = isset($_GET['leave']) ? $_GET['leave'] : false;
        $this->isSubmittedRequests = isset($_GET['submittedRequests']) ? $_GET['submittedRequests'] : false;
        $this->isSubmittedRequestsNEW = isset($_GET['submittedRequestsNEW']) ? $_GET['submittedRequestsNEW'] : false;
        $this->isLeaveApproval = isset($_GET['approve']) ? $_GET['approve'] : false;
        $this->isUserMenu = isset($_GET['usermenu']) ? $_GET['usermenu'] : false;
        $this->isAnounceAdmin = isset($_GET['isAnounceAdmin']) ? $_GET['isAnounceAdmin'] : false;
        $this->isSearching = isset($_POST['searchInput']) ? $_POST['searchInput'] : false;
        $this->isUpdateProfile = isset($_GET['updateProfile']) ? $_GET['updateProfile'] : false;
        $this->isLookup = isset($_GET['lookup']) ? $_GET['lookup'] : false;
        $this->isUseReport = isset($_GET['usereport']) ? $_GET['usereport'] : false;
        $this->isAbout = isset($_GET['about']) ? $_GET['about'] : false;
        $this->isPhpMyEdit = isset($_GET['phpMyEdit']) ? $_GET['phpMyEdit'] : false;
        $this->isMUNIS = isset($_GET['munis']) ? $_GET['munis'] : false;
        $this->isSecLog = isset($_GET['secLog']) ? $_GET['secLog'] : false;
        $this->isUserLookup = isset($_GET['userLookup']) ? $_GET['userLookup'] : false;
        $this->isSecApprove = isset($_GET['secApprove']) ? $_GET['secApprove'] : false;
        $this->isResManage = isset($_GET['resManage']) ? $_GET['resManage'] : false;
        $this->isUserVerify = isset($_GET['userVerify']) ? $_GET['userVerify'] : false;
        $this->isMySubmitReq = isset($_GET['myReq']) ? $_GET['myReq'] : false;
        $this->isReports = isset($_GET['reports']) ? $_GET['reports'] : false;
        $this->isApprovedUseReport = isset($_GET['approvedUse']) ? $_GET['approvedUse'] : false;
        $this->subReqCal = isset($_GET['subReqCal']) ? $_GET['subReqCal'] : false;
        $this->hrEmpRep = isset($_GET['hrEmpRep']) ? $_GET['hrEmpRep'] : false;
        $this->isSickRep = isset($_GET['sickEmpRep']) ? $_GET['sickEmpRep'] : false;
        $this->isEventLogs = isset($_GET['eventLogs']) ? $_GET['eventLogs'] : false;
        $this->isOTRep = isset($_GET['OTRep']) ? $_GET['OTRep'] : false;
        $this->isRadioLog = isset($_GET['radioLog']) ? $_GET['radioLog'] : false;
        $this->isMyInv = isset($_GET['myInv']) ? true : false; 
        $this->isSecLogRep = isset($_GET['SecLogRep']) ? true : false;
        
        
        if(empty($_GET)){
            if(empty($this->isSearching))
                $this->isHome = true;
            else
                $this->isHome = false;
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
