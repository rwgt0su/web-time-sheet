<?php

function displayContent($wts_content, $config){
    if($wts_content->isHome){
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
        displayAnnounce($config);
    }
    if($wts_content->isWelcome()){
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isAbout){
        ?>
        <div class="post"><?php displayAbout($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if(isValidUser()){
        if($wts_content->isAnounceAdmin){
            ?>
            <div class="post"><?php displayAdminAnnounce($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isLeaveForm){
            ?>
            <div class="post"><?php displayLeaveForm($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isSubmittedRequests){
            ?>
            <div class="post"><?php displaySubmittedRequests(); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isLeaveApproval){
            ?>
            <div class="post"><?php displayLeaveApproval(); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isUserMenu){
            ?>
            <div class="post"><?php displayUserMenu($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isLogout()){
            logoutUser("You have logged out");
        }
        if($wts_content->isSearching){
            ?>
            <div class="post"><?php searchPage($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isUpdateProfile){
            ?>
            <div class="post"><?php displayUpdateProfile($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isLookup){
            ?>
            <div class="post"><?php displayRequestLookup($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isUseReport){
            ?>
            <div class="post"><?php displayTimeUseReport($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isPhpMyEdit){
            ?>
            <div class="post"><?php displayPhpMyEditMenu(); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isMUNIS){
            ?>
            <div class="post"><?php MUNISreport($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isSecLog){
            ?>
            <div class="post"><?php displaySecondaryLog($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isUserLookup){
            ?>
            <div class="post"><?php displayUserLookup($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isSecApprove){
            ?>
            <div class="post"><?php displaySecondaryLog($config, $approve=true) ; ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isResManage){
            ?>
            <div class="post"><?php displayReserves($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isUserVerify){
            ?>
            <div class="post"><?php displayUserVerify($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isMySubmitReq){
            ?>
            <div class="post"><?php displayMySubmittedRequests($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isReports){
            ?>
            <div class="post"><?php displayReportMenu($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isApprovedUseReport){
            ?>
            <div class="post"><?php approvedTimeUseReport ($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->subReqCal){
            ?>
            <div class="post"><?php reportsCal($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        myAlerts($config, $wts_content); 
    }
    else{
        if($wts_content->isSearching){
            ?>
            <div class="post"><h3>Search Results</h3>Must Login First<div class="clear"></div></div><div class="divider"></div>
            <?php
        }
    }
    
         
}

function displayWelcome($config){
    ?>
    <div class="thumbnail"><img src="style/WellingtonBadge.gif" alt="" /></div>
    <h3><?php echo $config->getTitle(); ?></h3> 
    <p>Welcome to the Mahoning County Sheriff's Office Web Portal<br /><br />
        One you login you will have access to items such as Time Request Forms and Secondary Employment Logs</p>
    <?php
}
function displayAbout($config){
    ?>
    <div class="thumbnail"><img src="style/WellingtonBadge.gif" alt="" /></div>
    <h3><?php echo $config->getTitle(); ?></h3> 
    <p>Welcome to the Mahoning County Sheriff's Office Web Portal</p><br />
    <p>This website is developed and maintained by the Mahoning County Sheriff's Office</p><br /><br /><br />
    <p>This is a Sheriff's Office Computer System and is the property of the Mahoning County Sheriff's Office / Mahoning County Board of Commissioners. 
        It is for authorized use only. Users (authorized or unauthorized) have no explicit or implicit expectation of privacy. Any or all uses of this
        system and all files on this system may be intercepted, monitored, recorded, copied, audited, inspected, and disclosed to authorized site and
        law enforcement personnel, as well as authorized officials of other agencies.</p>
    <?php
}
?>
