<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/profile.html");

function updatePassword() {

    global $mysqli;

    if(isset($_POST['submit'])) {

        $currentpassword = $_POST["currentpassword"];
        $newpassword = $_POST["newpassword"];
        $confirmpassword = $_POST["confirmpassword"];

// Aggiungere controllo password corrente

        if ($newpassword == "" && $currentpassword ) {
            
        // Richiamare funzione MD5    
        $oid = $mysqli->query("UPDATE db_motorShop.users SET password ='$newpassword'
                             WHERE email  ='".$_SESSION['user']['email']."'");

        } else {
            echo "<script type='text/javascript'>alert('Le password non coincidono');</script>";
        }

    }   

}

$main->close();

?>