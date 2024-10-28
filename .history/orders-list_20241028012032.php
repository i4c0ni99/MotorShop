<?php 

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";
require "vendor/autoload.php"; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica se la chiave "groups_id" esiste
if (isset($_SESSION['user']['groups'])) {

if ($_SESSION['user']['groups'] == '1') {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/order.html");
$orders_base="SELECT * FROM orders WHERE state = 'pending' ";
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
        $mail->Password = 'zfeoebfhhdlwftvz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('noreply@motorshop.it', 'MotorShop Italia');
        $mail->addAddress($orderData['customerEmail']);

        if (isset($orderData['customerEmail'])) {
            $mail->addAddress($orderData['customerEmail']);
        } else {
            error_log("Errore: 'customerEmail' non definito.");
        }

        if (filter_var($orderData['customerEmail'], FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($orderData['customerEmail']);
        } else {
            error_log("Errore: indirizzo email non valido - " . $orderData['customerEmail']);
            return; // Esci dalla funzione se l'email non è valida
        }

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

if(isset($_GET['search'])){
    $orders_base .= "AND number = {$_GET['search']}";
    
}
// Verifica se è stato passato un parametro 'id' nell'URL
if (isset($_GET['id'])) {
    $orderId = $mysqli->real_escape_string($_GET['id']);

    // Chiama la funzione per aggiornare lo stato dell'ordine
    if (updateOrderState($orderId)) {
    } else {
        echo "Errore durante l'aggiornamento dello stato dell'ordine.";
    }
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

// Funzione per cancellare un ordine
function cancelOrder($orderId) {
    global $mysqli;

    // Cancella l'ordine
    $deleteQuery = "DELETE FROM orders WHERE id = '{$orderId}'";
    return $mysqli->query($deleteQuery);
}

// Visualizzazione lista ordini in attesa
echo "<script>console.log(".$orders_base.")</script>";
$orders = $orders_base;
$oid = $mysqli->query($orders);
$result = $oid;

if ($result && $result->num_rows > 0) {
    
    foreach ($result as $order) {
        
        $body->setContent("row",'<tr data-row-id="'.$order['id'].'">
                                            <td>'.$order['number'].'</td>
                                            <td>'.$order['date'].'</td>
                                            <td>'.$order['paymentMethod'].'</td>
                                            <td>'.$order['totalPrice'].'</td>
                                            <td>'.$order['details'].'</td>
                                            <td><a href="/MotorShop/customer-order-detail.php?id='.$order['id'].'" class="btn btn-primary">Apri</a></td>
                                            <td>
                                                <a href="/MotorShop/orders-list.php?id='.$order['id'].'" class="btn btn-primary">Spedisci</a>
                                                <a href="/MotorShop/orders-list.php?action=cancel&id='.$order['id'].'" class="btn btn-danger">Annulla</a>
                                            </td>
                                        </tr>');
        
    }
    if(isset($_POST['id'])){
        echo json_encode(['success' => 'success']);
    }
} else {
    // Nessun ordine trovato
    $body->setContent("row",'<tr>NESSUN ORDINE TROVATO</tr>');
}
} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();

} else {
    header
}

?>