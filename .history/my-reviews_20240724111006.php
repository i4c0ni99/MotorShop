<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/motor-html-package/motor/frame_public.html");
$body = new Template("skins/motor-html-package/motor/my-reviews.html");   
$main->setContent('email',$_SESSION['user']['email']);

$reviews=$mysqli->query("SELECT products_id, rate, review, date from feedbacks WHERE users_email = '{$_SESSION['user']['email']}'");
if ($reviews != null) $result= $reviews;

$main->setContent("dynamic", $body->get());

$main->close();

?>