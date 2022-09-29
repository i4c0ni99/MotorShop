<?php session_start();
require "include/template2.inc.php";
require "include/dbms.inc.php";

 $main = new Template("skins/multikart_all_in_one/back-end/index.html");
$main->setContent('user',$_SESSION['user']['name']);
 $main->close();
?>