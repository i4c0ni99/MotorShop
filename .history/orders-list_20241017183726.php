<?php 
session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";
require "vendor/autoload.php"; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inserire controllo utente gruppo 1
if (isset($_SESSION['user']['email'])) {

    $main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
    $body = new Template("skins/multikart_all_in_one/back-end/order.html");

    // Imposta il numero di ordini per pagina
    $ordersPerPage = 25;
    
    // Calcola il numero della pagina corrente
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $ordersPerPage;

    // Recupera il numero totale di ordini in stato 'pending'
    $totalOrdersQuery = "SELECT COUNT(*) AS total FROM orders WHERE state = 'pending'";
    $totalOrdersResult = $mysqli->query($totalOrdersQuery);
    $totalOrdersRow = $totalOrdersResult->fetch_assoc();
    $totalOrders = $totalOrdersRow['total'];

    // Calcola il numero totale di pagine
    $totalPages = ceil($totalOrders / $ordersPerPage);

    // Recupera gli ordini in stato 'pending' per la pagina corrente
    $ordersQuery = "SELECT * FROM orders WHERE state = 'pending' LIMIT $offset, $ordersPerPage";
    $result = $mysqli->query($ordersQuery);

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
        $body->setContent("ord_number", 'Nessun ordine trovato.');
        $body->setContent("ord_state", '');
        $body->setContent("ord_date", '');
        $body->setContent("ord_paymentMethod", '');
        $body->setContent("ord_totalPrice", '');
        $body->setContent("ord_details", '');
    }

    // Visualizzazione della paginazione
    if ($totalPages > 1) {
        $pagination = '';
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                $pagination .= "<span class='current-page'>$i</span> "; // Pagina corrente
            } else {
                $pagination .= "<a href='/MotorShop/orders-list.php?page=$i'>$i</a> "; // Link per altre pagine
            }
        }
        $body->setContent("pagination", $pagination);
    } else {
        $body->setContent("pagination", ''); // Nessuna paginazione necessaria
    }

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();
?>