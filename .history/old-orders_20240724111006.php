<?php 
session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

// Inserire controllo utente gruppo 1
if (isset($_SESSION['user']['email'])) {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/order-history.html");

// Funzione per cambiare lo stato dell'ordine da 'pending' a 'delivered'
function updateOrderState($orderId) {
    global $mysqli;

    // Preparare la query per aggiornare lo stato dell'ordine
    $stmt = $mysqli->prepare("UPDATE orders SET state = 'delivered' WHERE id = ? AND state = 'pending'");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        return true; // Aggiornamento avvenuto con successo
    } else {
        $stmt->close();
        return false; // Errore durante l'aggiornamento o nessun ordine aggiornato
    }
}

// Verifica se è stato passato un parametro 'id' nell'URL e se è un numero intero
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $orderId = intval($_GET['id']);

    // Chiama la funzione per aggiornare lo stato dell'ordine
    if (updateOrderState($orderId)) {
        echo "Stato dell'ordine aggiornato con successo!";
    } else {
        echo "Errore durante l'aggiornamento dello stato dell'ordine.";
    }
} else {
    echo "ID dell'ordine non valido.";
}

// Visualizzazione lista ordini spediti

$query = "SELECT * FROM orders WHERE state = 'delivered'";
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