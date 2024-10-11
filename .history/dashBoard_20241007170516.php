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

    // Verifica se l'utente appartiene al gruppo 1
    if ($_SESSION['user']['groups'] != '1') {
        header("Location: /MotorShop/login.php");
    exit();
 }

    // Visualizzazione lista ordini in attesa (max 5, dal più recente)
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

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();

?>