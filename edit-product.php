<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/edit-product.html");
 $data=$mysqli->query("SELECT name FROM categories");

 foreach($data as $item){
        $main->setContent('categories',$item['name']);
 }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data=$mysqli->query("SELECT id FROM categories WHERE name='{$_POST['category']}'");
    foreach($data as $item){
        $mysqli->query("INSERT INTO products (title,description,availability,specification,categories_id,code) 
                value ('{$_POST['title']}','{$_POST['description']}',
                               1,'{$_POST['details']}','{$item['id']}',{$_POST['code']})");
 }
    
          header('location:/MotorShop/edit-product.php');
}
$main->close();

?>