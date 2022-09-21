<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/category.html");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $mysqli->query("INSERT INTO categories (name) value ('{$_POST['category']}')");
     header("Location:skins/multikart_all_in_one/back-end/category.html");}
$main->close();

?>