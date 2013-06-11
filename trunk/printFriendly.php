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
        <link type="text/css" href="templetes/DarkTemp/styles/print.css" rel="stylesheet" media="print" />
        <link rel="stylesheet" href="templetes/DarkTemp/styles/tableSort.css" />
        <script type="text/javascript" src="bin/jQuery/js/tableSort.js"></script>
        <script language="Javascript1.2">
            <!--
            function printpage() {
            window.print();
            }
            //-->
        </script>

    </head>
    <body onload="printpage()"> 
        <div class="header_cotainer">
            <div class="container_12">
                <div class="grid_3">
                    <a href="/" ><h1 class="logo">MCSO</h1></a>
                </div>
            </div>
        </div>
        <div class="content_container">
            <div class="container_12 content_highlight">
                <div id="Loading">
                <?php displayContent($wts_content, $config); ?>

                </div><!--End Loading Section -->
            </div>
        </div>