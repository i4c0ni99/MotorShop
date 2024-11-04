<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {
    
$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/profile.html");

$body->setContent('name', $_SESSION['user']['name']);
$body->setContent('surname', $_SESSION['user']['surname']);
$body->setContent('email', $_SESSION['user']['email']);

// recupero del numero di telefono
if (!isset($_SESSION['user']['phone'])) {
    $phone_query = $mysqli->query("SELECT phone FROM users WHERE email='{$_SESSION['user']['email']}'");
    $phone_result = $phone_query->fetch_assoc();
    if ($phone_result) {
        $_SESSION['user']['phone'] = $phone_result['phone'];
    } else {
        header("location: /MotorShop/index.php");
        exit();
     }
 }
 $main->setContent('phone', $_SESSION['user']['phone']);

// Caricamento avatar
$data = $mysqli->query("SELECT avatar FROM users WHERE email='{$_SESSION['user']['email']}'");
$img = $data->fetch_assoc();
if ($img['avatar'] == null) {
    
    $main->setContent('img', "/../MotorShop/skins/multikart_all_in_one/back-end/assets/images/dashboard/user.jpg");
    $body->setContent('img', "/../MotorShop/skins/multikart_all_in_one/back-end/assets/images/dashboard/user.jpg");
    $body->setContent('btn-set','<input type="submit" value="Aggiungi avatar" name="edit-avatar-button" class="btn btn-primary"></input>');
} else {
    
    $main->setContent('img', "data:image;base64," . $img['avatar']);
    $body->setContent('img', "data:image;base64," . $img['avatar']);
    $body->setContent('btn-set','<input type="submit" value="Cambia avatar" name="edit-avatar-button" class="btn btn-primary"></input>');
    $body->setContent('btn-del', '<input type="submit" value="Elimina avatar" name="delete-avatar-button" class="btn btn-primary"></input>');
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
    // Caricamento nuovo avatar
    $data = file_get_contents($_FILES['avatar']['tmp_name']);
    $data64 = base64_encode($data);
    $mysqli->query("UPDATE users SET avatar = '$data64' WHERE email = '{$_SESSION['user']['email']}'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['delete-avatar-button'])) {
    // Eliminazione dell'avatar
    $mysqli->query("UPDATE users SET avatar = null WHERE email = '{$_SESSION['user']['email']}'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
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
    header("Location: /MotorShop/editProfile.php");
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
        header("location:/../MotorShop/editProfile.php");
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
    header("location:/../MotorShop/editProfile.php");
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

    // Controllo se tutti i campi sono compilati
    if ($name != "" && $surname != "" && $phone != "" && $province != "" && $city != "" && $address != "" && $cap != "") {
        // Controlla se l'indirizzo è già presente
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM shipping_address WHERE streetAddress = ? AND city = ? AND province = ? AND cap = ? AND users_email = ?");
        $stmt->bind_param("sssss", $address, $city, $province, $cap, $_SESSION['user']['email']);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            // Indirizzo già presente
            echo "Errore: l'indirizzo è già presente.";
        } else {
            // Inserimento nuovo indirizzo
            $stmt = $mysqli->prepare("INSERT INTO shipping_address (users_email, name, surname, phone, province, city, streetAddress, cap) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $_SESSION['user']['email'], $name, $surname, $phone, $province, $city, $address, $cap);

            // Esegui la query
            if ($stmt->execute()) {
                header("location:/../MotorShop/editCustomerProfile.php");
                exit;
            } else {
                // Gestione dell'errore
                echo "Errore durante l'inserimento: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

if (isset($_POST['change-pass-button'])) {
    $currentpassword = $mysqli->real_escape_string($_POST["currentpassword"]);
    $newpassword = $mysqli->real_escape_string($_POST["newpassword"]);
    $confirmpassword = $mysqli->real_escape_string($_POST["confirmpassword"]);
    
    // Ottieni la password corrente dell'utente
    $email = $_SESSION['user']['email'];
    $password_query = $mysqli->query("SELECT password FROM users WHERE email = '$email'");
    
    if ($password_query) {
        $result = $password_query->fetch_assoc();
        if ($result) {
            $passmd5 = crypto($currentpassword);

            // Verifica se la password corrente è corretta e se la nuova password e la conferma coincidono
            if ($newpassword == $confirmpassword && $passmd5 == $result['password']) {
                $newpass_encrypted = crypto($newpassword);
                $update_pass_query = $mysqli->query("UPDATE users SET password = '$newpass_encrypted' WHERE email = '$email'");
                
                if ($update_pass_query) {
                    header("location:/../MotorShop/editProfile.php");
                    exit();
                } else {
                    echo "Errore nell'aggiornamento della password: " . $mysqli->error;
                }
            } else {
                echo "<script type='text/javascript'>alert('Attenzione, le password non coincidono');</script>";
            }
        } else {
            echo "Errore: Nessun risultato per la query della password";
        }
    } else {
        echo "Errore nella query per ottenere la password: " . $mysqli->error;
    }
}

if (isset($_POST['delete-account-button'])) {
    $mysqli->query("DELETE FROM users WHERE email ='" . $_SESSION['user']['email'] . "'");
    header("location:/../MotorShop/logout.php");
    exit();
}

if (isset($_POST['delete-address-button'])) {
    // Eliminazione di un indirizzo di spedizione
    $address_id = $_POST["check"];
    $mysqli->query("DELETE FROM shipping_address WHERE id = $address_id");
    header("location:/../MotorShop/editProfile.php");
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

    if ($name != "" && $surname != "" && $phone != "" && $province != "" && $city != "" && $address != "" && $cap != "") {
        // Usa dichiarazioni preparate per evitare problemi di SQL injection
        $stmt = $mysqli->prepare("INSERT INTO shipping_address (users_email, name, surname, phone, province, city, streetAddress, cap) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $_SESSION['user']['email'], $name, $surname, $phone, $province, $city, $address, $cap);
        
        // Esegui la query
        if ($stmt->execute()) {
            header("location:/../MotorShop/editProfile.php");
            exit;
        } else {
            // Gestione dell'errore
            echo "Errore durante l'inserimento: " . $stmt->error;
        }

        $stmt->close();
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

$main->setContent("body", $body->get());
$main->close();
?>