<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']['email'])) { // Aggiungere controllo gruppo utente (2)

$main = new Template("skins/motor-html-package/motor/frame-customer.html");
$body = new Template("skins/motor-html-package/motor/profile.html");

// Aggiornamento dei dati dell'utente nel template principale
$main->setContent('name', $_SESSION['user']['name']);
$main->setContent('surname', $_SESSION['user']['surname']);
$main->setContent('email', $_SESSION['user']['email']);



































// Gestione delle azioni dei form

if (isset($_POST['edit-avatar-button'])) {
    // Caricamento di un nuovo avatar
    $data = file_get_contents($_FILES['avatar']['tmp_name']);
    $data64 = base64_encode($data);
    $mysqli->query("UPDATE users SET avatar = '$data64' WHERE email = '{$_SESSION['user']['email']}'");
    header("location:/../MotorShop/editCustomerProfile.php");
}

if (isset($_POST['delete-avatar-button'])) {
    // Eliminazione dell'avatar
    $mysqli->query("UPDATE users SET avatar = null WHERE email = '{$_SESSION['user']['email']}'");
    header("location:/../MotorShop/editCustomerProfile.php");
}

if (isset($_POST['edit-details-button'])) {
    $email = $_SESSION['user']['email'];

    if ($_POST["name"] != "") {
        $stmt = $mysqli->prepare("UPDATE users SET name = ? WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $_POST["name"], $email);
            $stmt->execute();
            $stmt->close();
        } else {
            die("Errore durante la preparazione della query: " . $mysqli->error);
        }
    }

    if ($_POST["surname"] != "") {
        $stmt = $mysqli->prepare("UPDATE users SET surname = ? WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $_POST["surname"], $email);
            $stmt->execute();
            $stmt->close();
        } else {
            die("Errore durante la preparazione della query: " . $mysqli->error);
        }
    }

    if ($_POST["phone"] != "") {
        $stmt = $mysqli->prepare("UPDATE users SET phone = ? WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $_POST["phone"], $email);
            $stmt->execute();
            $stmt->close();
        } else {
            die("Errore durante la preparazione della query: " . $mysqli->error);
        }
    }

    header("Location: /MotorShop/editCustomerProfile.php");
    exit();
}

if (isset($_POST['change-pass-button'])) {
    // Cambio della password
    $currentpassword = $_POST["currentpassword"];
    $newpassword = $_POST["newpassword"];
    $confirmpassword = $_POST["confirmpassword"];

    $password = $mysqli->query("SELECT password FROM users WHERE email = '{$_SESSION['user']['email']}'");
    $result = $password->fetch_assoc();
    $passmd5 = crypto($currentpassword);

    if ($newpassword == $confirmpassword && $passmd5 == $result['password']) {
        $mysqli->query("UPDATE users SET password = '" . crypto($newpassword) . "' WHERE email = '{$_SESSION['user']['email']}'");
        header("location:/../MotorShop/editCustomerProfile.php");
    } else {
        echo "<script type='text/javascript'>alert('Attenzione, le password non coincidono');</script>";
    }
}

if (isset($_POST['delete-account-button'])) {
    // Eliminazione dell'account utente
    $mysqli->query("DELETE FROM users WHERE email = '{$_SESSION['user']['email']}'");
    header("location:/../MotorShop/logout.php");
}

if (isset($_POST['delete-address-button'])) {
    // Eliminazione di un indirizzo di spedizione
    $address_id = $_POST["check"];
    $mysqli->query("DELETE FROM shipping_address WHERE id = $address_id");
    header("location:/../MotorShop/editCustomerProfile.php");
}

if (isset($_POST['add-address-button'])) {
    // Aggiunta di un nuovo indirizzo di spedizione
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $phone = $_POST["phone"];
    $province = $_POST["province"];
    $city = $_POST["city"];
    $address = $_POST["address"];
    $cap = $_POST["cap"];

    if ($name != "" && $surname != "" && $phone != "" && $province != "" && $city != "" && $address != "" && $cap != "") {
        $mysqli->query("INSERT INTO shipping_address (users_email, name, surname, phone, province, city, streetAddress, cap) 
                        VALUE ('{$_SESSION['user']['email']}', '$name', '$surname', '$phone', '$province', '$city', '$address', '$cap')");
        header("location:/../MotorShop/editCustomerProfile.php");
    }
}

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
    $body->setContent("ord_number", 'Non hai ancora fatto il primo ordine!');
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

$main->setContent("dynamic", $body->get());
$main->close();

?>