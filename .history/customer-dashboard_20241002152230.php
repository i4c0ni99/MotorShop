<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']['email'])) { // Verifica se l'utente è loggato

    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    $body = new Template("skins/motor-html-package/motor/dashboard.html");

    // Aggiornamento dei dati dell'utente nel template principale
    $main->setContent('name', $_SESSION['user']['name']);
    $main->setContent('surname', $_SESSION['user']['surname']);
    $main->setContent('email', $_SESSION['user']['email']);

    // Query per ottenere gli ordini del cliente
    $query = "SELECT id, number, state, date, paymentMethod, totalPrice, details FROM orders WHERE users_email = '{$_SESSION['user']['email']}'";

    $oid = $mysqli->query($query);
    $result = $oid;

    if ($result && $result->num_rows > 0) {
        foreach ($result as $order) {
            $body->setContent("ord_id", $order['id']);
            $body->setContent("ord_number", $order['number']);
            $body->setContent("ord_state", $order['state']);
            $body->setContent("ord_date", $order['date']);
            $body->setContent("ord_paymentMethod", $order['paymentMethod']);
            $body->setContent("ord_totalPrice", $order['totalPrice']);
            $body->setContent("ord_details", $order['details']);
        }
    } else {
        // Nessun ordine trovato
        $body->setContent("ord_id", '');
        $body->setContent("ord_number", 'Non hai ancora effettuato un ordine.');
        $body->setContent("ord_state", '');
        $body->setContent("ord_date", '');
        $body->setContent("ord_paymentMethod", '');
        $body->setContent("ord_totalPrice", '');
        $body->setContent("ord_details", '');
    }
} else {
    header("location:/../MotorShop/login.php");
    exit;
}

// Recupera le recensioni dell'utente
$reviews = $mysqli->query("SELECT products_id, rate, review, date from feedbacks WHERE users_email =
'{$_SESSION['user']['email']}'");

if ($reviews != null) {
    $feed = $reviews;

    if ($feed && $feed->num_rows > 0) {
        foreach ($feed as $f) {
            $body->setContent("prod_id", $f['products_id']);
            $body->setContent("f_rate", $f['rate']);
            $body->setContent("f_review", $f['review']);
            $body->setContent("f_date", $f['date']);
            
            $info_title = $mysqli->query("SELECT title FROM products WHERE id = $f['products_id']);
            $body->setContent("prod_title", $info_title);
        }
        
    }
}

$main->setContent("dynamic", $body->get());
$main->close();