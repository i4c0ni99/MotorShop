<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
// require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body=new Template("skins/multikart_all_in_one/back-end/subcategory.html");

$categories=$mysqli->query("SELECT name,id from subcategories");

foreach ($categories as $item) {

$date=$mysqli->query("SELECT id from subcategories where categories_id={$item['id']}");
$body->setContent('category',$item['name']);
$body->setContent('product',$date->num_rows);
$body->setContent('id',$item['id']);

}

if(isset($_GET['elimina'])) {

     $mysqli->query("DELETE FROM subcategories where id={$_GET['elimina']}");
     header('location:/MotorShop/create-subcategory.php');

  }

if ($_SERVER["REQUEST_METHOD"] == "POST") {

     $mysqli->query("INSERT INTO subcategories (name) value ('{$_POST['category']}')");
     header('location:/MotorShop/create-subcategory.php');

     }

$main->setContent('body',$body->get());

$main->close();

?>