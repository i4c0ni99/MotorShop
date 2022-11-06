<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require "include/template2.inc.php";
require "include/dbms.inc.php";
require_once __DIR__ . '/vendor/autoload.php';

$main = new Template("skins/motor-html-package/motor/frame_public.html");
$body = new Template("skins/motor-html-package/motor/contact.html");

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
       $errors[] = '';
   } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       $errors[] = 'Email non valida';
   }

   if (empty($message)) {
       $errors[] = 'Il messaggio è vuoto';
   }

   if (!empty($errors)) {
       $allErrors = join('<br/>', $errors);
       $errorMessage = "<p style='color: red;'>{$allErrors}</p>";
   } else {

       $mail = new PHPMailer();

       // specify SMTP credentials

       $mail->SMTPDebug = SMTP::DEBUG_SERVER;
       $mail->isSMTP();
       $mail->Host = 'smtp.gmail.com';
       $mail->SMTPAuth = true;
       $mail->Username = 'eservice19@gmail.com';
       $mail->Password = 'pdoithlryrictpli';
       $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
       $mail->Port = 465;

       $mail->setFrom('noreply@motorshop.it', 'MotorShop Italia');
       $mail->addAddress($email);

       // Enable HTML if needed
       $mail->isHTML(true);
       $mail->Subject = 'Nuova richiesta di contatto';
       $bodyParagraphs = ["Name: {$name}", "Surname: {$surname}", "Email: {$email}", " - {$phone} - ", "Message:", nl2br($message)];
       $body = join('<br />', $bodyParagraphs);
       $mail->Body = $body;
       echo $body;

       if ($mail->send()) {
           header('Location: /MotorShop/motor/contact.html');
       } else {

           $errorMessage = 'Oops, qualcosa è andato storto. Mailer Error: ' . $mail->ErrorInfo;
           
       }

    }

}

$main->setContent("dynamic", $body->get());

$main->close();


?>