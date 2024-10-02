<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/motor-html-package/motor/frame-customer.html");
$body = new Template("skins/motor-html-package/motor/feedback.html");

// Verifica se l'utente è autenticato
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

        // Se la richiesta è POST, gestisci l'inserimento del feedback
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit-review'])) {
            $rate = $mysqli->real_escape_string($_POST['rate']);
            $review = $mysqli->real_escape_string($_POST['review']);
            $user_email = $mysqli->real_escape_string($_SESSION['user']['email']);
            $current_date = date("Y-m-d");

            // Inserire i dati nella tabella feedbacks
            $insert_review = $mysqli->query("
                INSERT INTO feedbacks (users_email, products_id, rate, review, date) 
                VALUES ('$user_email', '$product_id', '$rate', '$review', '$current_date')
            ");

            if ($insert_review) {
                echo "Recensione pubblicata con successo.";
            } else {
                echo "Errore durante la pubblicazione della recensione: " . $mysqli->error;
            }
        }
    } else {
        echo "Prodotto non trovato.";
    }
} else {
    header("Location: /MotorShop/product-detail.php?id=" . );
    exit;
}

$main->setContent("dynamic", $body->get());
$main->close();

?>