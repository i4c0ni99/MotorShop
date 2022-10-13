<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";


$main = new Template("skins/motor-html-package/motor/login.html");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   require "include/auth.inc.php";
 
  
  $result = $mysqli -> query ("

  SELECT DISTINCT groups_has_services.groups_id FROM users 
   LEFT JOIN users_has_groups
   ON users_has_groups.users_email = users.email
   LEFT JOIN groups_has_services
   ON groups_has_services.groups_id = users_has_groups.groups_id 
   LEFT JOIN services
   ON services.id = groups_has_services.services_id
   WHERE email = '".$_POST['email']."'"

);
   
if (!$result) {

       $mysqli->error;
       exit;

   }

   $data = $result -> fetch_assoc();

   if ($data['groups_id'] == 1){
    $_SESSION['user']['groups']=$data['groups_id'];
       header('location:/../MotorShop/dashBoard.php');

   } else {
    $_SESSION['user']['groups']=$data['groups_id'];
       header("location:/../MotorShop/index.php");
       
   }

}

$main->close();

?>