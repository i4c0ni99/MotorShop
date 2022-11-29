<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main=new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/edit-product.html");

$main->setContent('name',$_SESSION['user']['name']);

$data=$mysqli->query("SELECT name FROM categories");
$title=$mysqli->query("SELECT title FROM products WHERE id = '".$_GET['id']."'");
$title=$data->fetch_assoc();

foreach($data as $item){

        $body->setContent('categories',$item['name']);

}

if (isset($_POST['edit'])) {
       
    $mysqli->query(" UPDATE products SET title = '".$_POST['title']."', description = '".$_POST['description']."',
             availability = '0', specification = '".$_POST['details']."', categories_id = '".$_POST['category']."' WHERE id = '".$_GET['id']."' ");
          
    header('location:/MotorShop/product-list.php');

}

if (isset($_POST['delete'])) {
       
    $mysqli->query(" DELETE FROM products WHERE id = '".$_GET['id']."' ");
          
    header('location:/MotorShop/product-list.php');


}

$main->setContent("body",$body->get());
$main->close();

?>