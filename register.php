<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";

$main = new Template("skins/motor-html-package/motor/login.html");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

if ($_POST['password']!=$_POST['confirmPassword']) {

  echo "<script type='text/javascript'>alert('Attenzione, le password non coincidono');</script>";  

} else {
   
  $criptoPass=MD5(MD5($_POST['password']));

  $exist= $mysqli->query("SELECT email from users where email='{$_POST['email']}'");

   if($exist->num_rows > 0) {

      echo "<script type='text/javascript'>alert('Attenzione, l'email è già in uso');</script>";
   } else {
       
  // Inserisce l'utente nella tabella users
  $mysqli->query ("INSERT INTO users (email,name,surname,password,phone) VALUES('{$_POST['email']}','{$_POST['name']}',
                       '{$_POST['surname']}','$criptoPass','{$_POST['phoneNumber']}');");

   $mysqli->query ("INSERT INTO users_has_groups (users_email,groups_id) VALUES(
     '{$_POST['email']}',2);");

              header("location:/MotorShop/index.php"); }
      }  

}

$main->close();

?>