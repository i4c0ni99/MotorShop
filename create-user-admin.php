<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
// require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/create-user.html");

$oid=$mysqli->query("SELECT users.name,users.surname,users.email,groups.roul FROM users 
                      JOIN users_has_groups ON users.email=users_has_groups.users_email 
                      JOIN groups ON groups.id=users_has_groups.groups_id WHERE groups.id = 2;");
 $result= $oid;

$id=1;

if($result->num_rows>0) {

foreach($result as $key) {

 $id++; 
 $body->setContent("id",$id);
 $body->setContent("name",$key['name']);
 $body->setContent("surname",$key['surname']);
 $body->setContent("email",$key['email']);
 $body->setContent("ruolo",$key['roul']);

}

} else {
    
    $body->setContent("id",'');
    $body->setContent("name",'');
    $body->setContent("surname",'');
    $body->setContent("email",'');
    $body->setContent("ruolo",'');

}

 if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($_POST['password']!=$_POST['confirmPassword']) {

      echo "<script type='text/javascript'>alert('Attenzione, le password non coincidono');</script>";  

    } else {

      $criptoPass=MD5(MD5($_POST['password']));
      
      $exist= $mysqli->query("SELECT email from users where email='{$_POST['email']}'");
      
      if ($exist->num_rows > 0) {
        
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

    if (isset($_POST['admin-user-button'])) {
      $oid = $mysqli->query ("UPDATE users_has_groups SET groups_id = 1
                            WHERE users_email = '{$_POST['check']}'");
      header("location:/../MotorShop/create-user-admin.php");
  }

    $main->setContent("body",$body->get());

    $main->close();

?>