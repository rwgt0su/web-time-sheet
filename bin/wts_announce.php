<?php
function displayAnnounce($config){
    //display published announcements
    $mysqli = connectToSQL();
    $myq = "SELECT `SHORTNAME` , `TITLE` , `BODY` , `PUBLISH` FROM `NEWS` WHERE `PUBLISH` =1 LIMIT 0 , 30 ";
    $result = $mysqli->query($myq);
    if (!$result) 
        throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
    
    $result->data_seek(0);  
    while ($row = $result->fetch_assoc()) {
            echo '<div class="post"><h3>' . $row['TITLE'] . '</h3>' . $row['BODY'] . '<div class="clear"></div></div><div class="divider"></div>';
    }
}
function displayAdminAnnounce($config){
    if($config->adminLvl >= 50 || strcmp(strtoupper($_SESSION['userName']), "SSZEKELY") == 0){
        $editorDisplay = isset($_GET['editAnnounce']) ? $_GET['editAnnounce'] : false;
        
        if(!$editorDisplay && !isset($_POST['addAnnounce'])){
            //Show available announcements to edit (or add new)
            $mysqli = connectToSQL();
            $myq = "SELECT * FROM `NEWS` WHERE 1";
            $result = $mysqli->query($myq);
            if (!$result) 
                throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");

            $result->data_seek(0);  
            while ($row = $result->fetch_assoc()) {
                    echo '<a href="'.$_SERVER['REQUEST_URI'].'&editAnnounce='. $row['SHORTNAME'] . '" >' . $row['TITLE'] . '</a><br /> 
                        Published: '.$row['TSTAMP'].' <br />by '.$row['AUDITID'].'<br /><br />';
            }
            ?>
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" name="registerform">
                <input type="submit" name="addAnnounce" value="Add Announcement" />
            </form>
            <?php
        }
        if(isset($_GET['editAnnounce'])){
            //User attempting to edit, get passed form fields
            $editorTitle = isset($_POST['editorTitle']) ? $_POST['editorTitle'] : '';
            $editorShort = isset($_POST['editorShort']) ? $_POST['editorShort'] : '';
            $editorOldShort = isset($_POST['editorOldShort']) ? $_POST['editorOldShort'] : '';
            $editorPublish = isset($_POST['editorPublish']) ? $_POST['editorPublish'] : '1';
            $editorData = isset($_POST['editor110']) ? $_POST['editor110'] : '';
            if (isset($_POST['editor110']) && !isset($_POST['editorPublish'])) 
                $editorPublish = 0;
            
            if (!isset($_POST['editorOldShort'])) {
                //no valid announcement was passed so get data within SQL
                $mysqli = connectToSQL();
                $myq = "SELECT `SHORTNAME` , `TITLE` , `BODY` , `PUBLISH` FROM `NEWS` WHERE `SHORTNAME` = 
                    CONVERT( _utf8 '".$editorDisplay."' USING latin1 ) COLLATE latin1_swedish_ci";
                $result = $mysqli->query($myq);
                if (!$result) 
                    throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");

                $result->data_seek(0);  
                while ($row = $result->fetch_assoc()) {
                        $editorTitle = $row['TITLE'];
                        $editorShort = $row['SHORTNAME'];
                        $editorPublish = $row['PUBLISH'];
                        $editorData = $row['BODY'];
                }
            }
            ?>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?isAnounceAdmin=true" >Back</a>
            <script type="text/javascript" src="ckeditor/ckeditor.js"></script>
            <form action ="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                <p>
                Announcement Title: <input type="text" name="editorTitle" value="<?php if (isset($editorTitle)) echo $editorTitle; ?>"/><br /><br />
                Short Name: <?php if (isset($editorShort)) echo $editorShort; ?><br /><br />
                <input type="hidden" name="editorOldShort" value="<?php echo $editorShort; ?>" />
                Publish Announcement: <input type="checkbox" name="editorPublish" value="1" <?php if ($editorPublish == 0){} else echo 'checked="checked"'; ?> /><br /><br />
                            <textarea id="editor1" name="editor110"><?php echo $editorData; ?></textarea>
                            <script type="text/javascript">
                                    CKEDITOR.replace( 'editor110' );
                            </script>
                    </p>
                    <p>
                            <input type="submit" name="saveBtn" value="Save" />
                    </p>
            </form>
            <?php

            if (isset($_POST['saveBtn'])) {
                //User pressed Save Button, so update with presented information
                $mysqli = connectToSQL();
                $myq = "UPDATE `PAYROLL`.`NEWS` SET 
                    `SHORTNAME` = '".$editorOldShort."',
                    `TITLE` = '".$editorTitle."',
                    `BODY` = '".$editorData."',
                    `PUBLISH` = '".$editorPublish."',
                    `TSTAMP` = NOW( ),
                    `AUDITID` = '".strtoupper($_SESSION['userName'])."',
                    `IP` = 'INET_ATON(\'".$_SERVER['REMOTE_ADDR']."\')' 
                    WHERE CONVERT( `NEWS`.`SHORTNAME` USING utf8 ) = '".$editorOldShort."' LIMIT 1 ;";
                $result = $mysqli->query($myq);
                if (!$result) 
                    throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
                else{
                    echo '<h3>Successful Save</h3>';
                }
            }
        }
        if(isset($_POST['addAnnounce'])){
            //User pressed Add an Announcement
            $editorTitle = isset($_POST['editorTitle']) ? $_POST['editorTitle'] : '';
            $editorShort = isset($_POST['editorShort']) ? $_POST['editorShort'] : '';
            $editorPublish = isset($_POST['editorPublish']) ? $_POST['editorPublish'] : '1';
            $editorData = isset($_POST['editor110']) ? $_POST['editor110'] : '';
            if (isset($_POST['editor110']) && !isset($_POST['editorPublish'])) 
                $editorPublish = 0;
            $isShort = false;
            if (isset($_POST['saveBtn']) && empty($editorShort))
                $isShort = true;
            ?>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?isAnounceAdmin=true" >Back</a>
            <script type="text/javascript" src="ckeditor/ckeditor.js"></script>
            <form action ="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
                <p>
                Announcement Title: <input type="text" name="editorTitle" value="<?php if (isset($editorTitle)) echo $editorTitle; ?>"/><br /><br />
                Short Name: <input type="text" name="editorShort" value="<?php if (isset($editorShort)) echo $editorShort; ?>" <?php if($isShort) echo "style=\"background:#FFFFFF;border:1px solid #FF0000;\""; ?> /><br /><br />
                Publish Announcement: <input type="checkbox" name="editorPublish" value="1" <?php if ($editorPublish == 0){} else echo 'checked="checked"'; ?> /><br /><br />
                            <textarea id="editor1" name="editor110"><?php echo $editorData; ?></textarea>
                            <script type="text/javascript">
                                    CKEDITOR.replace( 'editor110' );
                            </script>
                    </p>
                    <p>
                            <input type="hidden" name="addAnnounce" value="Add Announcement" />
                            <input type="submit" name="saveBtn" value="Save" />
                    </p>
            </form>
            <?php

            if (isset($_POST['saveBtn'])) {
                //Save button pressed, save data to database
                $mysqli = connectToSQL();
                //$myq = "INSERT INTO `PAYROLL`.`NEWS` (`SHORTNAME`, `TITLE`, `BODY`, `PUBLISH`, `TSTAMP`, `AUDITID`, 'IP') VALUES ('".$editorShort."', '".$editorTitle."', '".$editorData."', '".$editorPublish."', NOW(), 'awturner', '10.1.30.57');";
                $myq = "INSERT INTO `PAYROLL`.`NEWS` (`SHORTNAME`, `TITLE`, `BODY`, `PUBLISH`, `TSTAMP`, `AUDITID`, `IP`) 
                        VALUES ('".$editorShort."', '".$editorTitle."', '".$editorData."', '".$editorPublish."', NOW(), '".strtoupper($_SESSION['userName'])."', INET_ATON('${_SERVER['REMOTE_ADDR']}'))";
                $result = $mysqli->query($myq);
                if (!$result) 
                    throw new Exception("Database Error [{$mysqli->errno}] {$mysqli->error}");
                else{
                    echo '<h3>Successful Save</h3>';
                }
            }
        }
    }
}

?>
