<form id="" 
<script language="JavaScript" type="text/javascript">   
                    function addLookupButton(formName) {
                        var _form = document.getElementById(formName);

                        var _search = document.createElement('input');
                        _search.type = "submit";
                        _search.value = "Lookup User";
                        _form.appendChild(_search);
                    }
                    </script>
                    <?php
                    $isCallOff = "";
                    if(isset($_POST['calloff'])){
                        $isCallOff = "CHECKED ";
                        echo '<input type="submit" name="searchBtn" value="Lookup Users" />';
                    }
                    echo '<input type="checkbox" name="calloff" value="YES" '.$isCallOff.' />Check if calling in sick.';    
                    echo '<input type=button value="submit" onclick=\'addLookupButton("leave");\' />';