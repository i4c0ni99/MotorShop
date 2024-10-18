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

    // Cambia lo stato dell'ordine da 'pending' a 'delivered' e invia email di conferma spedizione
    function updateOrderState($orderId) {
        global $mysqli;

        // Aggiorna lo stato dell'ordine
        $updateQuery = "UPDATE orders SET state = 'delivered' WHERE id = '{$orderId}' AND state = 'pending'";
        $updateResult = $mysqli->query($updateQuery);

        if ($updateResult) {
            // Ottieni i dettagli dell'ordine per l'email
            $orderQuery = "SELECT * FROM orders WHERE id = '{$orderId}'";
            $orderResult = $mysqli->query($orderQuery);

            if ($orderResult && $orderResult->num_rows > 0) {
                $orderData = $orderResult->fetch_assoc();
                sendConfirmationEmail($orderData);
                return true; // Aggiornamento avvenuto con successo
            }
        } 
        return false; // Errore durante l'aggiornamento
    }

    function sendConfirmationEmail($orderData) {
        $mail = new PHPMailer(true);

        try {
            // Configurazione del server SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'eservice19@gmail.com'; 
            $mail->Password = 'imoddxemcldfvkol'; // Aggiornare password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('noreply@motorshop.it', 'MotorShop Italia');
            $mail->addAddress($orderData['customerEmail']);

            // Contenuto dell'email
            $mail->isHTML(true);
            $mail->Subject = 'Conferma di spedizione ordine #' . $orderData['number'];
            $mailBody = "Gentile Cliente,<br><br>"
                . "Il tuo ordine con numero #" . $orderData['number'] . " è stato spedito.<br>"
                . "Dettagli dell'ordine:<br>"
                . "Data: " . $orderData['date'] . "<br>"
                . "Metodo di pagamento: " . $orderData['paymentMethod'] . "<br>"
                . "Totale: €" . $orderData['totalPrice'] . "<br>"
                . "Dettagli: " . $orderData['details'] . "<br><br>"
                . "Grazie per aver acquistato con noi!<br>"
                . "MotorShop Italia";
            $mail->Body = $mailBody;

            $mail->send();
        } catch (Exception $e) {
            error_log("Errore durante l'invio dell'email: " . $mail->ErrorInfo);
        }
    }

    // Verifica se è stato passato un parametro 'id' nell'URL
    if (isset($_GET['id'])) {
        $orderId = $mysqli->real_escape_string($_GET['id']);

        // Chiama la funzione per aggiornare lo stato dell'ordine
        if (updateOrderState($orderId)) {
            echo "Stato dell'ordine aggiornato con successo!";
        } else {
            echo "Errore durante l'aggiornamento dello stato dell'ordine.";
        }
    }

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();
?>