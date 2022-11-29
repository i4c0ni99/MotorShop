<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$main = new Template("skins/motor-html-package/motor/login.html");

function sendmail ($email,$v_cod) {
        
  require ('vendor/phpmailer/phpmailer/src/PHPMailer.php');
  require ('vendor/phpmailer/phpmailer/src/Exception.php');
  require ('vendor/phpmailer/phpmailer/src/SMTP.php');

  $mail = new PHPMailer(true);

  try {
      $mail->SMTPDebug = SMTP::DEBUG_SERVER; 
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com';
      $mail->SMTPAuth   = true;            
      $mail->Username   = 'eservice19@gmail.com';
      $mail->Password   = 'pdoithlryrictpli';                    
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;   
      $mail->Port       = 465;                           

      $mail->setFrom('noreply@motorshop.it', 'MotorShop Italia');
      $mail->addAddress($email);

      $mail->isHTML(true);
      $mail->Subject = 'Benvenuto su MotorShop';
      $mail->Body    = "Lo Staff di MotorShop ti da il benvenuto!<br>Per verificare il tuo account  
      <a href='http://localhost/MotorShop/verify.php?email=$email&v_cod=$v_cod'> premi qui.</a>";

      $mail->send();
          return true;
  } catch (Exception $e) {
          return false;
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

if ($_POST['password']!=$_POST['confirmPassword']) {

  echo "<script type='text/javascript'>alert('Attenzione, le password non coincidono');</script>";  

} else {

  $email =$_POST['email'];

  $v_cod = bin2hex(random_bytes(16));
   
  $criptoPass=MD5(MD5($_POST['password']));

  $exist= $mysqli->query("SELECT email from users where email='{$_POST['email']}'");

   if($exist->num_rows > 0) {

      echo "<script type='text/javascript'>alert('Attenzione, l'email è già in uso');</script>";
   } else {
       
  // Inserisce l'utente nella tabella users
  $mysqli->query ("INSERT INTO users (email,name,surname,password,phone,verified) VALUES('{$_POST['email']}','{$_POST['name']}',
                       '{$_POST['surname']}','$criptoPass','{$_POST['phoneNumber']}',0);");

   $mysqli->query ("INSERT INTO users_has_groups (users_email,groups_id) VALUES(
     '{$_POST['email']}',2);");

   }

if (sendmail($email,$v_cod ) == true) {
  $mysqli->query("UPDATE users SET verified = 1 WHERE email='{$_POST['email']}'");
  echo "
      <script>
          alert('Registrazione completata! Verifica la tua email dal link che hai ricevuto, se non lo trovi controlla su Spam.');
      </script>"; 
} else {
  echo "
      <script>
          alert('Query error!');
      </script>";
}



              // header("location:/MotorShop/login.php"); } 

}

}

$main->close();

?>