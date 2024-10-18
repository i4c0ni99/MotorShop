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

    // Configura la paginazione
    $itemsPerPage = 5;
    $orderPage = isset($_GET['order_page']) ? (int)$_GET['order_page'] : 1;
    $orderOffset = ($orderPage - 1) * $itemsPerPage;

    // Conta il numero totale di ordini
    $totalOrdersQuery = "SELECT COUNT(*) as total FROM orders WHERE users_email = '{$_SESSION['user']['email']}'";
    $totalOrdersResult = $mysqli->query($totalOrdersQuery);
    $totalOrdersRow = $totalOrdersResult->fetch_assoc();
    $totalOrders = $totalOrdersRow['total'];

    // Calcola il numero totale di pagine per gli ordini
    $totalOrderPages = ceil($totalOrders / $itemsPerPage);

    // Query per ottenere gli ordini del cliente con paginazione
    $query = "SELECT id, number, state, date, paymentMethod, totalPrice, details 
              FROM orders 
              WHERE users_email = '{$_SESSION['user']['email']}' 
              LIMIT $itemsPerPage OFFSET $orderOffset";

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

    // Generazione della paginazione per gli ordini
    if ($totalOrderPages > 1) {
        $orderPagination = '';
        for ($i = 1; $i <= $totalOrderPages; $i++) {
            if ($i == $orderPage) {
                $orderPagination .= "<span class='current-page'>$i</span> ";
            } else {
                $orderPagination .= "<a href='/MotorShop/dashboard.php?order_page=$i'>$i</a> ";
            }
        }
        $body->setContent("order_pagination", $orderPagination);
    } else {
        $body->setContent("order_pagination", '');
    }
} else {
    header("location:/../MotorShop/login.php");
    exit;
}

// Recupera le recensioni dell'utente con paginazione
$reviewPage = isset($_GET['review_page']) ? (int)$_GET['review_page'] : 1;
$reviewOffset = ($reviewPage - 1) * $itemsPerPage;

// Conta il numero totale di recensioni
$totalReviewsQuery = "SELECT COUNT(*) as total FROM feedbacks WHERE users_email = '{$_SESSION['user']['email']}'";
$totalReviewsResult = $mysqli->query($totalReviewsQuery);
$totalReviewsRow = $totalReviewsResult->fetch_assoc();
$totalReviews = $totalReviewsRow['total'];

// Calcola il numero totale di pagine per le recensioni
$totalReviewPages = ceil($totalReviews / $itemsPerPage);

// Recupera le recensioni dell'utente con paginazione
$reviews = $mysqli->query("SELECT products_id, rate, review, date 
                           FROM feedbacks 
                           WHERE users_email = '{$_SESSION['user']['email']}' 
                           LIMIT $itemsPerPage OFFSET $reviewOffset");

if ($reviews != null) {
    $feed = $reviews;

    if ($feed && $feed->num_rows > 0) {
        foreach ($feed as $f) {
            $body->setContent("prod_id", $f['products_id']);
            $body->setContent("f_rate", $f['rate']);
            $body->setContent("f_review", $f['review']);
            $body->setContent("f_date", $f['date']);
            
            $info_title = $mysqli->query("SELECT title FROM products WHERE id = " . $f['products_id']);
            $prod_title = $info_title->fetch_assoc();
            $body->setContent("prod_title", $prod_title['title']);
        }
    }
}

// Generazione della paginazione per le recensioni
if ($totalReviewPages > 1) {
    $reviewPagination = '';
    for ($i = 1; $i <= $totalReviewPages; $i++) {
        if ($i == $reviewPage) {
            $reviewPagination .= "<span class='current-page'>$i</span> ";
        } else {
            $reviewPagination .= "<a href='/MotorShop/dashboard.php?review_page=$i'>$i</a> ";
        }
    }
    $body->setContent("review_pagination", $reviewPagination);
} else {
    $body->setContent("review_pagination", '');
}

$main->setContent("dynamic", $body->get());
$main->close();