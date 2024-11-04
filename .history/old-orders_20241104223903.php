<?php 
session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    $main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
    $body = new Template("skins/multikart_all_in_one/back-end/order-history.html");

    // numero di ordini per pagina
    $itemsPerPage = 10; 
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $itemsPerPage;

    // numero totale di ordini spediti
    $totalOrdersQuery = "SELECT COUNT(*) AS total FROM orders WHERE state = 'delivered'";
    $totalOrdersResult = $mysqli->query($totalOrdersQuery);
    $totalOrdersRow = $totalOrdersResult->fetch_assoc();
    $totalOrders = $totalOrdersRow['total'];

    // numero totale di pagine
    $totalPages = ceil($totalOrders / $itemsPerPage);

    $query_base = "SELECT * FROM orders WHERE state = 'delivered' LIMIT $offset, $itemsPerPage  ";
    if(isset($_GET['search'])){
        $query_base = " SELECT * FROM orders WHERE state = 'delivered' AND number = {$_GET['search']} LIMIT $offset, $itemsPerPage ";
        
    }
    // Visualizzazione lista ordini spediti con paginazione
    
    $result = $mysqli->query($query_base);

    if ($result && $result->num_rows > 0) {
        while ($order = $result->fetch_assoc()) {
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

    // Gestione della paginazione
    if ($totalPages > 1) {
        $pagination = '';
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                $pagination .= "<span class='current-page'>$i</span> ";
            } else {
                $pagination .= "<a href='/MotorShop/order-history.php?page=$i'>$i</a> ";
            }
        }
        $body->setContent("pagination", $pagination);
    } else {
        $body->setContent("pagination", '');
    }

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();

?>