<?php session_start();
require "include/template2.inc.php";
require "include/dbms.inc.php";

foreach($_SESSION['user'] as $item){
    echo $item['name'];
} 
 $main = new Template("skins/multikart_all_in_one/back-end/index.html");
 
 $main->close();
?>