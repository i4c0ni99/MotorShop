<?php 

require "include/template2.inc.php";

$main = new Template("skins/motor-html-package/motor/frame_public.html");
$body = new Template("skins/motor-html-package/motor/contact.html");

$main->setContent("dynamic", $body->get());

$main->close();


?>