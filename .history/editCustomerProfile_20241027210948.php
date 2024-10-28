<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']['email'])) {

$main = new Template("skins/motor-html-package/motor/frame-customer.html");
$body = new Template("skins/motor-html-package/motor/profile.html");

// Aggiornamento dei dati dell'utente nel template principale
$main->setContent('name', $_SESSION['user']['name']);
$main->setContent('surname', $_SESSION['user']['surname']);
$main->setContent('email', $_SESSION['user']['email']);

// Verifica e aggiunta del numero di telefono dalla tabella users se non è presente in session
if (!isset($_SESSION['user']['phone'])) {
    $phone_query = $mysqli->query("SELECT phone FROM users WHERE email='{$_SESSION['user']['email']}'");
    $phone_result = $phone_query->fetch_assoc();
    if ($phone_result) {
        $_SESSION['user']['phone'] = $phone_result['phone'];
    } else {
        header("location: /MotorShop/index.php");
        exit();
     }
 } else {
    $main->setContent('phone', $_SESSION['user']['phone']);
 }

// Caricamento dell'avatar
$data = $mysqli->query("SELECT avatar FROM users WHERE email='{$_SESSION['user']['email']}'");
$img = $data->fetch_assoc();
if ($img['avatar'] == null) {
    $main->setContent('img', "/../MotorShop/skins/multikart_all_in_one/back-end/assets/images/dashboard/user.jpg");
} else {
    $main->setContent('img', "data:image;base64," . "{$img['avatar']}");
}

// Caricamento degli indirizzi di spedizione dell'utente
$addresses = $mysqli->query("SELECT * FROM shipping_address WHERE users_email = '{$_SESSION['user']['email']}'");

// Inizializzazione del corpo del profilo con i dati degli indirizzi
foreach ($addresses as $key) {
    $body->setContent("ADid", $key['id']);
    $body->setContent("ADname", $key['name']);
    $body->setContent("ADsurname", $key['surname']);
    $body->setContent("ADphone", $key['phone']);
    $body->setContent("ADprovince", $key['province']);
    $body->setContent("ADcity", $key['city']);
    $body->setContent("ADstreetAddress", $key['streetAddress']);
    $body->setContent("ADcap", $key['cap']);
}

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

if (isset($_POST['add-address-button'])) {
    // Aggiunta di un nuovo indirizzo di spedizione
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $phone = $_POST["phone"];
    $province = $_POST["province"];
    $city = $_POST["city"];
    $address = $_POST["streetAddress"];
    $cap = $_POST["cap"];

    if ($name && $surname && $phone && $province && $city && $address && $cap) {
        // Controllo se l'indirizzo è già presente
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM shipping_address WHERE streetAddress = ? AND city = ? AND province = ? AND cap = ? AND users_email = ?");
        $stmt->bind_param("sssss", $address, $city, $province, $cap, $_SESSION['user']['email']);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $errorMessage = "Errore: l'indirizzo è già presente.";
        } else {
            // Inserimento nuovo indirizzo
            $stmt = $mysqli->prepare("INSERT INTO shipping_address (users_email, name, surname, phone, province, city, streetAddress, cap) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $_SESSION['user']['email'], $name, $surname, $phone, $province, $city, $address, $cap);
            $stmt->execute();
            $stmt->close();
            header("Location: editProfile.php");
            exit;
        }
    }
}

// Aggiunta della logica di eliminazione per un indirizzo specifico
if (isset($_POST['delete-address-button'])) {
    $addressId = $_POST['address_id'];

    // Eliminazione dell'indirizzo
    $stmt = $mysqli->prepare("DELETE FROM shipping_address WHERE id = ? AND users_email = ?");
    $stmt->bind_param("is", $addressId, $_SESSION['user']['email']);
    $stmt->execute();
    $stmt->close();
    header("Location: editProfile.php");
    exit;
}

// Recupera gli indirizzi dell'utente
$addresses = [];
$stmt = $mysqli->prepare("SELECT id, name, surname, streetAddress, cap FROM shipping_address WHERE users_email = ?");
$stmt->bind_param("s", $_SESSION['user']['email']);
$stmt->execute();
$stmt->bind_result($addressId, $name, $surname, $streetAddress, $cap);

while ($stmt->fetch()) {
    $addresses[] = [
        'id' => $addressId,
        'name' => $name,
        'surname' => $surname,
        'streetAddress' => $streetAddress,
        'cap' => $cap,
    ];
}

$stmt->close();

// Passa gli indirizzi e gli eventuali messaggi di errore alla vista
include '/MotorShop/editCustomerProfile.html';

$main->setContent("dynamic", $body->get());
$main->close();

}

?>