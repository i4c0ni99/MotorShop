<?php

    session_start();

    require "include/template2.inc.php";
    require "include/dbms.inc.php";
    require "include/auth.inc.php";

    unset($_SESSION['auth']);
    unset($_SESSION['user']);
    
   
    Header("Location: /MotorShop/login.php");

?>