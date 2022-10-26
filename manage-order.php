<?php 

session_start();

require "include/template2.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/order-detail.html");

$main->setContent("body", $body->get());

$main->close();


?>