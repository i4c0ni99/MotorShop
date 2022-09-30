<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/profile.html");
$main->setContent('name',$_SESSION['user']['name']);
$main->setContent('surname',$_SESSION['user']['surname']);
$main->setContent('email',$_SESSION['user']['email']);
$main->setContent('phone',$_SESSION['user']['phone']);

    if(isset($_POST['edit-details-button'])) {

        $name = $_POST["name"];
        $surname = $_POST["surname"];
        $phone = $_POST["phone"];

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['surname'] = $surname;
        $_SESSION['user']['phone'] = $phone;

        if ($name != "" && $surname != "" && $phone != "" ) {

        $oid = $mysqli->query("UPDATE users SET name ='$name', surname = '$surname', phone= '$phone'
                             WHERE email  ='".$_SESSION['user']['email']."'");

        header("location:/../MotorShop/editProfile.php");

        }

    }

$main->close();

?>