<?php

function searchPage($config){
    $searchInput = isset($_POST['searchInput']) ? $_POST['searchInput'] : false;
    if($searchInput){
        echo '<h3>Results for: '.$searchInput.'</h3>';
        searchLDAP($config, $searchInput);
    }
    else {
        echo 'No information provided';
    }
}
?>
