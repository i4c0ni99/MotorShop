<?php

session_start();

require_once "include/template2.inc.php";
require_once "include/dbms.inc.php";
require_once "include/auth.inc.php";

if (isset($_SESSION['user'])) {

    $main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
    $body = new Template("skins/multikart_all_in_one/back-end/index.html");

    $main->setContent('user', $_SESSION['user']['name']);
    $main->setContent('user', $_SESSION['user']['email']);
    $main->setContent('user', $_SESSION['user']['groups_id']);

    if ($_SESSION['user']['groups'] != '1') {
        header("Location: /MotorShop/login.php");
        exit();
    }

    // Fatturato mensile
$queryRevenue = "SELECT SUM(totalPrice) AS total_revenue
FROM orders
WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";

$resultRevenue = $mysqli->query($queryRevenue);
if ($resultRevenue) {
    $rowRevenue = $resultRevenue->fetch_assoc();
    $revenue = $rowRevenue['total_revenue']; 

    $revenue = number_format($revenue, 2, '.', ''); // 2 decimali
} else {
    $revenue = 0; 
}

$body->setContent("revenue", $revenue);

    // Contatore ordini mensile
$queryOrders = "SELECT COUNT(*) AS total_orders
FROM orders
WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";

$resultOrders = $mysqli->query($queryOrders);
if ($resultOrders) {
    $rowOrders = $resultOrders->fetch_assoc();
    $orders = $rowOrders['total_orders']; 
} else {
    $orders = 0; 
}

$body->setContent("orders", $orders);

        // Contatore utenti

        $queryUsers = "SELECT COUNT(*) AS total_users FROM users";
        $resultUsers = $mysqli -> query($queryUsers);
        if ($resultUsers) {
            $rowUsers = $resultUsers->fetch_assoc();
            $totalUsers = $rowUsers['total_users'];
        } else {
            $totalUsers = 0; 
        }
        
        $body->setContent("total_users", $totalUsers);

    // Contatore ordini

    $queryProducts = "SELECT COUNT(*) AS total_products FROM products";
    $resultProducts = $mysqli -> query($queryProducts);
    if ($resultProducts) {
        $rowProducts = $resultProducts->fetch_assoc();
        $totalProducts = $rowProducts['total_products'];
    } else {
        $totalProducts = 0; 
    }

    $body->setContent("total_products", $totalProducts);

    // Visualizzazione lista ordini in attesa (max 5 e dal più recente)
    $query = "SELECT * FROM orders WHERE state = 'pending' ORDER BY date DESC LIMIT 5";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        while ($order = $result->fetch_assoc()) {
            $body->setContent("ord_id", $order['id']);
            $body->setContent("ord_number", $order['number']);
            $body->setContent("ord_state", $order['state']);
            $body->setContent("ord_date", $order['date']);
            $body->setContent("ord_paymentMethod", $order['paymentMethod']);
            $body->setContent("ord_totalPrice", $order['totalPrice']);
            $body->setContent("ord_details", $order['details']);
            $body->setContent("manage",'<a href="/MotorShop/customer-order-detail.php?id='.$order['id'].'" class="btn btn-primary">Gestisci</a>');
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
        $body->setContent("manage",'');
    }

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();

?>