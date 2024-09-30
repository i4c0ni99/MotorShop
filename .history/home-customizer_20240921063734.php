<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/home-customizer.html");


if (isset($_SESSION['user'])) {

    if (isset($_POST['submit'])) {

        // Inserisci pagina slider
        $title = $mysqli->real_escape_string($_POST['title']);
        $description = $mysqli->real_escape_string($_POST['description']);
        $button = $mysqli->real_escape_string($_POST['button-text']);
        $link = $mysqli->real_escape_string($_POST['link']);
        $insertQuery = "INSERT INTO slider (title, description, button, link) 
                VALUES ('$title', '$description', '$button', '$link')";
                if ($mysqli->query($insertQuery)) {
                    $product_id = $mysqli->insert_id;
                }

    }

if (isset($_POST['submit'])) {
    // Inserisci pagina slider
    $title = $mysqli->real_escape_string($_POST['title']);
    $description = $mysqli->real_escape_string($_POST['description']);
    $button = $mysqli->real_escape_string($_POST['button-text']);
    $link = $mysqli->real_escape_string($_POST['link']);
    $insertQuery = "INSERT INTO slider (title, description, button, link) 
            VALUES ('$title', '$description', '$button', '$link')";
            if ($mysqli->query($insertQuery)) {
                $product_id = $mysqli->insert_id;
            }
}
    
    $main->setContent('user',$_SESSION['user']['name']);
    $main->setContent("body", $body->get());
    $main->close();

}

?>