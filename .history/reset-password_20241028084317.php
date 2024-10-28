<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Includi l'autoload di Composer per caricare PHPMailer
require 'vendor/autoload.php';

$main = new Template("skins/motor-html-package/motor/frame_public.html");

function sendMail($email, $v_cod) {
    $subject = "Recupero della Password";
    $resetLink = "http://localhost/MotorShop/reset-password.php?key=" . $email . "&reset=" . $v_cod;

    // Leggi il template HTML dal file
    $htmlTemplate = file_get_contents('skins/multikart_all_in_one/email-template/recovery.html');
    
    if ($htmlTemplate === false) {
        error_log("Impossibile leggere il template dell'email");
        return false;
    }

    // Sostituisci i segnaposto con i valori effettivi
    $htmlContent = str_replace(
        ['{{reset_link}}'], 
        [$resetLink], 
        $htmlTemplate
    );

    $mail = new PHPMailer(true);

    try {
        // Configura il server SMTP
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'eservice19@gmail.com';
        $mail->Password = 'zfeoebfhhdlwftvz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Mittente e destinatario
        $mail->setFrom('noreply@motorshop.it', 'MotorShop Italia');
        $mail->addAddress($email);

        // Contenuto dell'email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlContent;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Errore nell'invio dell'email: {$mail->ErrorInfo}");
        return false;
    }
}
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Verifica se l'email esiste nel database
    $result = $mysqli->query("SELECT * FROM users WHERE email = '$email'");
    
    if ($result && $result->num_rows > 0) {
        // Genera il codice di reset
        $v_cod = bin2hex(random_bytes(16));

        // Salva il codice di reset nel database
        $mysqli->query("UPDATE users SET verification_id = '$v_cod' WHERE email = '$email'");

        // Invia l'email di reset
        if (sendMail($email, $v_cod)) {
            echo "<script>alert('Email di reset password inviata con successo! Controlla la tua casella di posta.');</script>";
        } else {
            echo "<script>alert('Errore durante l'invio dell'email.');</script>";
        }
    } else {
        echo "<script>alert('L'email non esiste nel nostro sistema.');</script>";
    }
}

// Se nella get ci sono key=email e reset=vcod porta a form di inserimento nuova password
if (isset($_GET['key']) && ($_GET['reset'])) {
    
    $body = new Template("skins/motor-html-package/motor/new-pass.html");
    
    if (isset($_POST['change-pass-button'])) {
        // Reset della password
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
    
} else {
    $body = new Template("skins/motor-html-package/motor/forgot-password.html");
}

$main->setContent("dynamic", $body->get());
$main->close();

?>