<?php

function displayContent($wts_content, $config){
    if($wts_content->iswelcome()){
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    else{
        ?>
        <div class="post"><?php displayWelcome($config); ?><div class="clear"></div></div><div class="divider"></div>
        <?php
    }
    if($wts_content->isLogout()){
        logoutUser();
        echo '<meta http-equiv="refresh" content="3;url=/"/>';
        echo '<div class="post">You have logged out<div class="clear"></div></div><div class="divider"></div>';
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
