<?php 

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/order.html");

$oid=$mysqli->query("SELECT * FROM shipping_address WHERE shipping_address.users_email = '".$_SESSION['user']['email']."'");
   $result= $oid;
   $id=1;

if ($result->num_rows>0) {

foreach ($result as $key) {

 $id++; 
 $body->setContent("ADid",$key['id']);
 $body->setContent("ADname",$key['name']);
 $body->setContent("ADsurname",$key['surname']);
 $body->setContent("ADphone",$key['phone']);
 $body->setContent("ADprovince",$key['province']);
 $body->setContent("ADcity",$key['city']);
 $body->setContent("ADstreetAddress",$key['streetAddress']);
 $body->setContent("ADcap",$key['cap']);

}

} else {
    
    $body->setContent("ADid",'');
    $body->setContent("ADname",'');
    $body->setContent("ADsurname",'');
    $body->setContent("ADphone",'');
    $body->setContent("ADprovince",'');
    $body->setContent("ADcity",'');
    $body->setContent("ADstreetAddress",'');
    $body->setContent("ADcap",'');

}

$main->setContent("body", $body->get());

$main->close();


?>