<?php

    $host = "localhost";
    $user = "root";
    $passw= "";
    $name = "db_motorShop";

     $mysqli = new mysqli($host, $user,$passw,$name);

     // Check
     if ($mysqli->connect_error) {
        die("Connssione fallita: " . $mysqli->connect_error);
    } */

?>