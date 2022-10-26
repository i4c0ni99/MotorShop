<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/user-list.html");

$oid=$mysqli->query("SELECT users.name,users.surname,users.email,groups.roul FROM users 
                      JOIN users_has_groups ON users.email=users_has_groups.users_email 
                      JOIN groups ON groups.id=users_has_groups.groups_id;");
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

if (isset($_POST['delete-user-button'])) {
    $delete = $_POST["check"];
    $oid = $mysqli->query("DELETE FROM users
                         WHERE email = '$delete'");
    header("location:/../MotorShop/user-list.php");
}

$main->setContent("body",$body->get());

$main->close();

?>