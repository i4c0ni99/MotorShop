<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

session_start();

$main = new Template("skins/multikart_all_in_one/back-end/profile.html");

$main->setContent("user", $_GETCOOKIE['user']);

function updateProfile() {

    global $mysqli;

    if(isset($_POST['submit'])) {

        $name = $_POST["name"];
        $surname = $_POST["surname"];
        $email = $_POST["email"];
        $phone = $_POST["phone"];

        if ($name != "" && $surname != "" && $email != "" && $phone != "" ) {

        $oid = $mysqli->query("UPDATE db_motorShop.users SET name ='$name', surname = '$surname', email =' $email', phone= '$phone'
                             WHERE email  ='".$_SESSION['user']['email']."'");

        }

    }   

}

$main->close();

?>