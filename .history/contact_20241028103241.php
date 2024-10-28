<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once "include/template2.inc.php";
require_once "include/dbms.inc.php";
require_once __DIR__ . '/vendor/autoload.php';

session_start();

// Controlla se la sessione è attiva e se l'utente è autenticato
if (isset($_SESSION['user']['email'])) {
    require "include/auth.inc.php";
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
} else {
    $main = new Template("skins/motor-html-package/motor/frame_public.html");
}

// Inizializza il template del body
$body = new Template("skins/motor-html-package/motor/contact.html");

if (isset($_SESSION['user']['email'])) {
    // Popola il template con i dati dell'utente
    $body->setContent('name', htmlspecialchars($_SESSION['user']['name']));
    $body->setContent('surname', htmlspecialchars($_SESSION['user']['surname']));
    $body->setContent('email', htmlspecialchars($_SESSION['user']['email']));
    $body->setContent('phone', htmlspecialchars($_SESSION['user']['phone']));
}

$errors = [];
$errorMessage = '';

if (isset($_POST['contact-form'])) {

$name = $_POST['name'];
$surname = $_POST['surname'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$message = $_POST['message'];

if (empty($name)) {
$errors[] = 'Nome non valido';
}

if (empty($email)) {
$errors[] = 'Email non valida';
} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
$errors[] = 'Email non valida';
}

if (empty($message)) {
$errors[] = 'Il messaggio è vuoto';
}

if (!empty($errors)) {
$allErrors = join('<br />', $errors);
$errorMessage = "<p style='color: red;'>{$allErrors}</p>";
$body->setContent('error_message', $errorMessage);
} else {
$mail = new PHPMailer(true);

try {
// Configura le impostazioni SMTP
$mail->SMTPDebug = SMTP::DEBUG_SERVER;
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'eservice19@gmail.com';
$mail->Password = 'srikigsevgjzulxqc'
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = 465;

$mail->setFrom('noreply@motorshop.it', 'MotorShop Italia');
$mail->addAddress($email);

// Contenuto
$mail->isHTML(true);
$mail->Subject = 'Nuova richiesta di contatto';
$bodyParagraphs = ["Nome: {$name}", "Cognome: {$surname}", "Email: {$email}", "Telefono: {$phone}", "Messaggio:",
nl2br($message)];
$bodyContent = join('<br />', $bodyParagraphs);

// Leggi il template HTML per il corpo dell'email
$bodyTemplate = new Template("skins/motor-html-package/motor/email_template.html");
$bodyTemplate->setContent("email_content", $bodyContent);
$mail->Body = $bodyTemplate->get();

// Invia l'email
if ($mail->send()) {
header('Location: /MotorShop/contact.php');
exit();
} else {
$errorMessage = 'Oops, qualcosa è andato storto. Mailer Error: ' . $mail->ErrorInfo;
$body->setContent('error_message', $errorMessage);
}
} catch (Exception $e) {
$errorMessage = 'Oops, qualcosa è andato storto. Mailer Error: ' . $mail->ErrorInfo;
$body->setContent('error_message', $errorMessage);
}
}
}

$main->setContent("dynamic", $body->get());
$main->close();
?>