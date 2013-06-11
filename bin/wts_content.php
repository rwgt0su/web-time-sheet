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
    if(isValidUser($config)){       
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
        if($wts_content->isTimeRequestForm){
            ?>
            <div class="post"><?php displayNewTimeRequestForm($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        } 
        if($wts_content->isSubmittedRequests){
            ?>
            <div class="post"><?php displaySubmittedRequests($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isSubmittedRequestsNEW){
            ?>
            <div class="post"><?php displaySubmittedRequestsNEW($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isLeaveApproval){
            ?>
            <div class="post"><?php displayLeaveApprovalNEW($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isUserMenu){
            ?>
            <div class="post"><?php displayUserMenu($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isLogout()){
            logoutUser($config, "You have logged out");
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
            <div class="post"><?php displayMySubmittedRequestsNEW($config); ?><div class="clear"></div></div><div class="divider"></div>
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
        if($wts_content->hrEmpRep){
            ?>
            <div class="post"><?php hrPayrolReportByEmployee($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isSickRep){
            ?>
            <div class="post"><?php sickReport($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isEventLogs){
            ?>
            <div class="post"><?php displayLogs($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isOTRep){
            ?>
            <div class="post"><?php overtimeReport($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isRadioLog){
            ?>
            <div class="post"><?php displayRadioLog($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isMyInv){
            ?>
            <div class="post"><?php showMyInventory($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isSecLogRep){
            ?>
            <div class="post"><?php displaySecLogReport($config); ?><div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        if($wts_content->isPrintRequestNo){
            ?>
            <div class="post"><?php $requests = new request_class(); $requests->config = $config; $requests->showPrintFriendlyRequest(); ?>
                <div class="clear"></div></div><div class="divider"></div>
            <?php
        }
        $reqURI = dirname($_SERVER['REQUEST_URI']);
        //if(!empty($reqURI))
            $reqURI = $reqURI."/";
        popupmessage(str_replace($reqURI, "", $_SERVER['PHP_SELF']));
        if((str_replace($reqURI, "", $_SERVER['PHP_SELF']) != "printFriendly.php" || str_replace($reqURI, "", $_SERVER['PHP_SELF']) != "/printFriendly.php") && $config->showPrinterFriendly){
            echo '<a target="_blank" href="printFriendly.php?' . str_replace($_SERVER['PHP_SELF']."?", "", $_SERVER['REQUEST_URI']) . '"> Print Tables</a>';
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
    <div class="thumbnail"><img src="style/SheriffBadgeGreene.gif" alt="" /></div>
    <h3><?php echo $config->getTitle(); ?></h3> 
    <p>Welcome to the Mahoning County Sheriff's Office Web Portal<br /><br />
        One you login you will have access to items such as Time Request Forms and Secondary Employment Logs</p>
    <?php
}
function displayAbout($config){
    ?>
    <div class="thumbnail"><img src="style/SheriffBadgeGreene.gif" alt="" /></div>
    <h3><?php echo $config->getTitle(); ?></h3> 
    <p>Welcome to the Mahoning County Sheriff's Office Web Portal</p><br />
    <p>This website is developed and maintained by the Mahoning County Sheriff's Office</p><br /><br /><br />
    <p>This is a Sheriff's Office Computer System and is the property of the Mahoning County Sheriff's Office / Mahoning County Board of Commissioners. 
        It is for authorized use only. Users (authorized or unauthorized) have no explicit or implicit expectation of privacy. Any or all uses of this
        system and all files on this system may be intercepted, monitored, recorded, copied, audited, inspected, and disclosed to authorized site and
        law enforcement personnel, as well as authorized officials of other agencies.</p><Br/>
    
    <?php
    $sub_req_url="http://code.google.com/p/web-time-sheet/source/list";
    $ch = curl_init($sub_req_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

    $content = curl_exec($ch);
    curl_close($ch);
    $temp = explode('id="resultstable"', $content);
    echo '<div class="divider"></div><Br/><table id="resultstable"';
    $display = strip_tags($temp[1], '<table><tr><td><th>');
    $display = explode('</table>', $display);
    echo $display[0].'</table>';
    
    echo '<a href="http://code.google.com/p/web-time-sheet/source/list">See full list of revisions</a>';
}
?>
