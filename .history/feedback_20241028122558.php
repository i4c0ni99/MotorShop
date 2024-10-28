<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/motor-html-package/motor/frame-customer.html");
$body = new Template("skins/motor-html-package/motor/feedback.html");

if (!isset($_SESSION['user']['email'])) {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent('name', $_SESSION['user']['name']);
$main->setContent('surname', $_SESSION['user']['surname']);
$main->setContent('email', $_SESSION['user']['email']);

if (isset($_GET['id'])) {
    $sub_product_id = $mysqli->real_escape_string($_GET['id']);

    // Prendere products_id dalla tabella sub_products
    $product_query = $mysqli->query("SELECT products_id FROM sub_products WHERE id = '{$sub_product_id}'");
        
    if ($product_query && $product_query->num_rows > 0) {
        $product_data = $product_query->fetch_assoc();
        $product_id = $product_data['products_id'];

        // Prendere title dalla tabella products
        $title_query = $mysqli->query("SELECT title FROM products WHERE id = '{$product_id}'");
        
        if ($title_query && $title_query->num_rows > 0) {
            $title_data = $title_query->fetch_assoc();
            $title = $title_data['title'];
            $body->setContent("title", $title);
        } else {
            $body->setContent("title", "Titolo non disponibile");
        }

        
        
        
        
        
        
        
        
        
        

        
        
        
        
        

        
        
        

        
        
        
        

        
        
        
        
        
        
        
        
        
        
        
        
    } else {
        echo "Prodotto non trovato.";
    }
} else {
    header("Location: /MotorShop/editCustomerProfile.php");
    exit;
}

$main->setContent("dynamic", $body->get());
$main->close();

?>