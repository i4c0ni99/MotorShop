<?php

session_start();
require "include/template2.inc.php";
require "include/dbms.inc.php";


$email = $_SESSION['user']['email']; 

$img=$mysqli->query("SELECT avatar FROM users WHERE email = '".$email."'");
$row = $img->fetch_assoc();
    
    // Prendi l'URL dell'immagine (assumendo che `avatar` contenga l'URL)
     $imageUrl = $row['avatar'];
echo json_encode(["imageUrl" =>  $imageUrl ]);