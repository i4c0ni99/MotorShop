<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/motor-html-package/motor/frame_public.html");
$body = new Template("skins/motor-html-package/motor/my-orders.html");

$main->setContent('name',$_SESSION['user']['name']);
$main->setContent('surname',$_SESSION['user']['surname']);
$main->setContent('email',$_SESSION['user']['email']);
$main->setContent('phone',$_SESSION['user']['phone']);

$main->setContent("dynamic", $body->get());

$main->close();

?>