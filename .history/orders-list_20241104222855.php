<?php 

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";
require "vendor/autoload.php"; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/order.html");
$orders_base="SELECT * FROM orders WHERE state = 'pending' ";

// Cambia lo stato dell'ordine da 'pending' a 'delivered' 
function updateOrderState($orderId) {
    global $mysqli;
    
    $updateQuery = "UPDATE orders SET state = 'delivered' WHERE id = '{$orderId}' AND state = 'pending'";
    $updateResult = $mysqli->query($updateQuery);

    if ($updateResult) {
        // dettagli dell'ordine
        $orderQuery = "SELECT * FROM orders WHERE id = '{$orderId}'";
        $orderResult = $mysqli->query($orderQuery);

        if ($orderResult && $orderResult->num_rows > 0) {
            $orderData = $orderResult->fetch_assoc();
            sendConfirmationEmail($orderData);
            return true; 
        }
    } 
    return false; 
}

function sendConfirmationEmail($orderData) {
    $mail = new PHPMailer(true);

    try {
        
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'eservice19@gmail.com'; 
        $mail->Password = 'zfeoebfhhdlwftvz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('noreply@motorshop.it', 'MotorShop Italia');
        $mail->addAddress($orderData['users_email']);

        if (isset($orderData['users_email'])) {
            $mail->addAddress($orderData['users_email']);
        } else {
            error_log("Errore: 'users_email' non definito.");
        }

        if (filter_var($orderData['users_email'], FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($orderData['users_email']);
        } else {
            error_log("Errore: indirizzo email non valido - " . $orderData['users_emaill']);
            return;
        }
        
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

if(isset($_GET['search'])){
    $orders_base .= "AND number = {$_GET['search']}";
    
}

// parametro 'id' nell'URL
if (isset($_GET['id'])) {
    $orderId = $mysqli->real_escape_string($_GET['id']);

    // funzione per aggiornare lo stato dell'ordine
    if (updateOrderState($orderId)) {
    } else {
        echo "Errore durante l'aggiornamento dello stato dell'ordine.";
    }
}

// cancella un ordine
function cancelOrder($orderId) {
    global $mysqli;

    $deleteQuery = "DELETE FROM orders WHERE id = '{$orderId}'";
    header("Location: /MotorShop/orders-list.php");
    return $mysqli->query($deleteQuery);
}


// Verifica se è stata richiesta la cancellazione di un ordine
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
    $orderId = $mysqli->real_escape_string($_GET['id']);
    
    // Chiama la funzione per cancellare l'ordine
    if (cancelOrder($orderId)) {
        header("Location: /MotorShop/orders-list.php");
    } else {
        echo "Errore durante la cancellazione dell'ordine.";
    }
}

// Visualizzazione lista ordini in attesa
echo "<script>console.log(".$orders_base.")</script>";
$orders = $orders_base;
$oid = $mysqli->query($orders);
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
            $body->setContent("open_ord",'<a href="/MotorShop/customer-order-detail.php?id='.$order['id'].'" class="btn btn-primary">Apri</a>');
            $body->setContent("manage_ord",'<a href="/MotorShop/orders-list.php?id='.$order['id'].'" class="btn btn-primary">Spedisci</a>
                                                <a href="/MotorShop/orders-list.php?action=cancel&id='.$order['id'].'" class="btn btn-danger">Annulla</a>');
        
    }
    if(isset($_POST['id'])){
        echo json_encode(['success' => 'success']);
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
        $body->setContent("open_ord",'');
        $body->setContent("manage_ord",' ');
    
}

$main->setContent("body", $body->get());
$main->close();

} else {
    header("Location /MotorShop/login.php");
}

?>