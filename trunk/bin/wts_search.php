<?php

function searchPage(){
    $searchInput = isset($_POST['searchInput']) ? $_POST['searchInput'] : false;
    if($searchInput)
        echo 'Results for: '.$searchInput;
    else {
        echo 'No information provided';
    }
}
?>
