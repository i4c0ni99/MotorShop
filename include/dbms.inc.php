<?php
    $host = "127.0.0.1";
    $user = "i4c0ni99";
    $pass = "motorShop99!";
    $name = "db_motorShop";

     $mysqli = new mysqli($host, $user, $pass, $name);

    if($mysqli->connect_error){
        die('Error'.('$mysqli->connect_errorno.'));
    }else{

    }
?>