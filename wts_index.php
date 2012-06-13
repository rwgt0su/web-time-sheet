<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php

require_once('bin/common.php');

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $config->getTitle(); ?></title>
    <link type="text/css" href="templetes/DarkTemp/styles/reset.css" rel="stylesheet" media="all" />
    <link type="text/css" href="templetes/DarkTemp/styles/text.css" rel="stylesheet" media="all" />
    <link type="text/css" href="templetes/DarkTemp/styles/960.css" rel="stylesheet" media="all" />
    <link type="text/css" href="templetes/DarkTemp/styles/style.css" rel="stylesheet" media="all" />
    
</head>
<body>
    <div class="header_cotainer">
        <div class="container_12">
            <div class="grid_3">
                <a href="/" ><h1 class="logo">MCSO</h1></a>
            </div>
            <div class="grid_9">
                <div class="menu_items">                
                    <a href="/" class="button_link" title="Home">Home</a>
                    <a href="/" class="button_link" title="Email">Email</a>
                    <a href="/" class="button_link" title="About">About</a>

                    <div class="search">
                        <input type="text" name="search" />
                        <input type="submit" name="submit" value="Search" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Content Section -->
    <div class="content_container">
        <div class="container_12 content_highlight">
            <!--Left Menu Section -->
            <div class="grid_4">
                <?php displayMenu($config); ?>
                <div class="divider"></div>
                <?php displayAdmin($config); ?>
            </div>
            
            <!--Center Content Section -->
            <div class="grid_8">
                <!--Content Login Section -->
                <div class="post">
                    <?php displayLogin($config); ?>
                    <div class="clear"></div>
                </div> 
             
                <div class="divider"></div>
                
                <!--Content Section -->
                <?php displayContent($wts_content, $config); ?>
                
                <!--Content Post 2 Section -->
                <!-- <div class="post">
                    <div class="thumbnail"><a href="#"><img src="templetes/DarkTemp/images/image.jpg" alt="" /></a></div>
                    <h3><a href="#">Sample Header</a></h3>
                    <p>Sample content wouuld be placed within this paragraph</p>
                    <div class="post_footer">
                        <div class="comments">left column</div>
                        <div class="more"><a href="#">right column</a></div>
                    </div>
                    <div class="clear"></div>
                </div>
                
                <div class="divider"></div> -->
                

                
               <!--End Content Post Section --> 
            </div>
            <div class="clear"></div>
        </div>
    </div>
    
    <!--Footer Section -->
    <div class="footer_container">
        <div class="container_12">
            <div class="grid_4">© Mahoning County Sheriff's Office 2012</div>
            <div class="grid_8">
                <a href="https://www.facebook.com/pages/Mahoning-County-Sheriffs-Office/211950208818335" target="_blank"><img src="templetes/DarkTemp/images/facebook_icon.jpg" alt="" /></a>
            </div>
        </div>
    </div>
</body>
</html>