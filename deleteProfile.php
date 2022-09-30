<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/profile.html");

function deleteAccount() {

    global $mysqli;

    if(isset($_POST['delete'])) {

        $oid = $mysqli->query("DELETE FROM users
                             WHERE email  ='".$_SESSION['user']['email']."'");

    }   

}

$main->close();

?>