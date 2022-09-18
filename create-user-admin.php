<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/create-user.html");

 if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($_POST['password']!=$_POST['confirmPassword']) {
      echo "<script type='text/javascript'>alert('Le password non coincidono');</script>";  
    } else {
        doRegister();
    }
    
    }
    $main->close();
?>