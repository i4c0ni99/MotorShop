<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/create-user.html");

function isEmailOrPhoneUnique($email, $phone) {
    global $mysqli;

    $email = $mysqli->real_escape_string($email);
    $phone = $mysqli->real_escape_string($phone);
 
    $emailQuery = "SELECT email FROM users WHERE email = '$email'";
    $emailResult = $mysqli->query($emailQuery);

    $phoneQuery = "SELECT phone FROM users WHERE phone = '$phone'";
    $phoneResult = $mysqli->query($phoneQuery);

    if ($emailResult->num_rows > 0) {
        // L'email è già presente nel DB
        return "email";
    } elseif ($phoneResult->num_rows > 0) {
        // Il numero di cellulare è già presente nel DB
        return "phone";
    } else {
        // email e numero di cellulare sono univoci
        return "unique";
    }
}

function sendMail($email, $v_cod) {
    $name = isset($_POST['name']) ? $_POST['name'] : ''; 
    $subject = "Benvenuto su MotorShop";
    $verificationLink = "http://localhost/MotorShop/verify.php?email=$email&v_cod=$v_cod";

    $htmlTemplate = file_get_contents('skins/multikart_all_in_one/email-template/welcome.html');
    
    if ($htmlTemplate === false) {
        error_log("Impossibile leggere il template dell'email");
        return false;
    }

    // sostituisce con i valori effettivi
    $htmlContent = str_replace(
        ['{{name}}', '{{verification_link}}'], 
        [$name, $verificationLink], 
        $htmlTemplate
    );

    $mail = new PHPMailer(true);
    
    try {
        // Configurazione SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'eservice19@gmail.com';
        $mail->Password = 'sppxbcjdsjisbrnc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        // Destinatario e mittente
        $mail->setFrom('noreply@motorshop.it', 'MotorShop Italia');
        $mail->addAddress($email);
        // Contenuto
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlContent;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // campi obbligatori
    if (isset($_POST['email'], $_POST['name'], $_POST['surname'], $_POST['password'], $_POST['confirmPassword'])) {

        // controlla che le password coincidano
        if ($_POST['password'] != $_POST['confirmPassword']) {
            echo "<script>alert('Attenzione, le password non coincidono');</script>";
        } else {
            // verifica se email e telefono sono univoci
            $unique = isEmailOrPhoneUnique($_POST['email'], $_POST['phoneNumber']);
            
            if ($unique === "unique") {
                $v_cod = bin2hex(random_bytes(16)); // Genera il codice di verifica
                $criptoPass = md5(md5($_POST['password'])); 
                
                // Inserisce l'utente nella tabella users
                $insertUserQuery = "INSERT INTO users (email, name, surname, password, phone, verified, verification_id) 
                                    VALUES ('{$_POST['email']}', '{$_POST['name']}', '{$_POST['surname']}', '$criptoPass', '{$_POST['phoneNumber']}', '1', '$v_cod')";
                if ($mysqli->query($insertUserQuery)) {
                    // Inserisce l'utente nel gruppo 2
                    $insertGroupQuery = "INSERT INTO users_has_groups (users_email, groups_id) VALUES ('{$_POST['email']}', 2)";
                    $mysqli->query($insertGroupQuery);

                    // Invia email di verifica
                    $_SESSION['v_cod'] = $v_cod; // Salva v_cod nella sessione
                    if (sendMail($_POST['email'], $v_cod)){
                        echo "<script>alert('Registrazione completata! Verifica la tua email dal link che hai ricevuto, se non lo trovi controlla su Spam.');</script>";
                    } else {
                        echo "<script>alert('Non siamo riusciti ad inviarti l'email di verifica. Riprova!');</script>";
                    }
                    
                    header("Location: /MotorShop/user-list.php");
                    exit();
                } else {
                    echo "<script>alert('Errore durante l'inserimento dell'utente. Riprova!');</script>";
                }
            } elseif ($unique === "email") {
                echo "<script>alert('Attenzione, l'email è già in uso');</script>";
            } elseif ($unique === "phone") {
                echo "<script>alert('Attenzione, il numero di cellulare è già in uso');</script>";
            }
        }
    } else {
        echo "<script>alert('Non hai completato il modulo di registrazione!');</script>";
    }
}

$main->setContent("body", $body->get());
$main->close();

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>