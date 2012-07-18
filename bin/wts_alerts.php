<?php

function myAlerts($config){
    if(isValidUser()){
        popUpMessage('You have an Alert! <a href="?approve=true">Go To Request</a>');
    }
}
?>
