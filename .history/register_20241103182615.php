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
use PHPMailer\PHPMailer\SMTP;

function isEmailOrPhoneUnique($email, $phone) {
    global $mysqli;

    // Verifica se l'email è univoca
    $stmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $emailResult = $stmt->num_rows;
    $stmt->close();

    // Verifica se il numero di cellulare è univoco
    $stmt = $mysqli->prepare("SELECT phone FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();
    $phoneResult = $stmt->num_rows;
    $stmt->close();

    if ($emailResult > 0) {
        return "email";
    } elseif ($phoneResult > 0) {
        return "phone";
    } else {
        return "unique";
    }
}

function sendMail($email, $v_cod) {
    $name = $_POST['name'];
    $subject = "Benvenuto su MotorShop";
    $verificationLink = "http://localhost/MotorShop/verify.php?email=$email&v_cod=$v_cod";

    // Legge il template HTML dal percorso
    $htmlTemplate = file_get_contents('skins/multikart_all_in_one/email-template/welcome.html');
    
    if ($htmlTemplate === false) {
        error_log("Impossibile leggere il template dell'email");
        return false;
    }

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
        $mail->Password = 'zfeoebfhhdlwftvz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        $mail->setFrom('noreply@motorshop.it', 'MotorShop Italia');
        $mail->addAddress($email);

        // Contenuto dell'email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlContent;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Errore durante l'invio dell'email di verifica: {$mail->ErrorInfo}");
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Controllo dei dati inviati
    if (!isset($_POST['email'], $_POST['name'], $_POST['surname'], $_POST['password'], $_POST['confirmPassword'], $_POST['phoneNumber'])) {
        echo "Non hai completato il modulo di registrazione!";
        exit();
    }

    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password != $confirmPassword) {
        echo "<script>alert('Attenzione, le password non coincidono!');</script>";
    } else {
        
        $unique = isEmailOrPhoneUnique($email, $_POST['phoneNumber']);
        var_dump($unique);
        if ($unique === "unique") {
            
            $v_cod = bin2hex(random_bytes(16));
            $_SESSION['v_cod'] = $v_cod;
            $criptoPass = password_hash($password, PASSWORD_DEFAULT);

            // Inserisci utente nel database
            $stmt = $mysqli->prepare("INSERT INTO users (email, name, surname, password, phone, verified, verification_id) VALUES (?, ?, ?, ?, ?, 0, ?)");
            $stmt->bind_param("ssssss", $email, $_POST['name'], $_POST['surname'], $criptoPass, $_POST['phoneNumber'], $v_cod);
            
            if ($stmt->execute()) {
                $stmt->close();
                
                // Inserimento dell'utente nel gruppo 2
                $stmt = $mysqli->prepare("INSERT INTO users_has_groups (users_email, groups_id) VALUES (?, 2)");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();

                if (sendMail($email, $v_cod)) {
                    echo "<script>alert('Registrazione completata! Verifica la tua email dal link che hai ricevuto, se non lo trovi controlla su Spam.');</script>";
                    header("location:/../MotorShop/login.php");
                    exit();  
                } else {
                    echo "<script>alert('Non siamo riusciti ad inviarti l'email di verifica. Riprova!');</script>";
                }
            } else {
                echo "<script>alert('Errore durante la registrazione. Riprova più tardi.');</script>";
            }
        } else {
            echo "<script>alert('Attenzione, email o numero di cellulare già in uso!');</script>";
        } 
    }
}

?>