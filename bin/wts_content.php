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
    if($wts_content->isAnounceAdmin){
        ?>
        <div class="post"><?php displayAdminAnnounce($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isLeaveForm){
        ?>
        <div class="post"><?php displayLeaveForm(); ?><div class="clear"></div></div><div class="divider"></div>
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
        <div class="post"><?php searchPage(); ?><div class="clear"></div></div><div class="divider"></div>
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
         
}

function displayWelcome($config){
    ?>
    <div class="thumbnail"><img src="/style/WellingtonBadge.gif" alt="" /></div>
    <h3><?php echo $config->getTitle(); ?></h3> 
    <p>Welcome to the Mahoning County Sheriff's Office Web Portal</p>
    <?php
}
?>