<?php

session_start();

require "include/dbms.inc.php";
require "include/template2.inc.php";
include "include/utils/priceFormatter.php";

$main = new Template("skins/motor-html-package/motor/frame_public.html");
$body = new Template("skins/motor-html-package/motor/product-detail.html");

$oid = $mysqli->query("SELECT title,id,categories_id,description FROM products where id={$_GET['id']}");
$result = $oid->fetch_assoc();
$categories = $mysqli->query("SELECT name FROM categories where id={$result['categories_id']}");
$category = $categories->fetch_assoc();
$body->setContent('category', $category['name']);

$body->setContent("id", $result['id']);
$body->setContent("title", $result['title']);
$body->setContent("descrizione", $result['description']);

$data = $mysqli->query("SELECT id,price,color FROM sub_products where products_id={$result['id']}");
$result1 = $data->fetch_assoc();
$oid1 = $mysqli->query("SELECT imgsrc FROM images where sub_products_id={$result1['id']} ");
$result2 = $oid1->fetch_assoc();

if (isset($_GET['id']) && !isset($_GET['subId'])) {
   
  $body->setContent("imgView",$result2['imgsrc']);
  $body->setContent("demo_img",$result2['imgsrc']);

}

foreach ($data as $item) {

   $body->setContent("colorDiv", '<li><a href="http://localhost/MotorShop/product-detail.php?id=' . $result['id'] . '&subId=' . $item['id'] . '">
            <span style="background-color:' . $item['color'] . '" class="icon-color"></span>
          </a>
      </li>');

   $body->setContent("subId", $item['id']);
   $body->setContent("color", $item['color']);
   $body->setContent("price", formatPrice($item['price']));
   $body->setContent("quantity", $item['quantity']);

}

if (isset($_GET['subId'])) {

   $data1 = $mysqli->query("SELECT sizes.size from sizes where sizes.sub_products_id={$_GET['subId']}");

   foreach ($data1 as $item2) {

      $body->setContent("size", '<li class="active"><a href="#" class="mv-btn mv-btn-style-8">' . $item2['size'] . '</a></li>');
   
   }

   $img_query = $mysqli->query("SELECT imgsrc FROM images where sub_products_id={$_GET['subId']} ");
   $img1=$img_query->fetch_assoc();

   foreach ($img_query as $img) {

    $body->setContent("imgView",$img['imgsrc']);
    $body->setContent("demo_img",$img['imgsrc']);

   }

}

if (isset($_POST['post-review'])) {

   $name = $_SESSION['user']['name'];
   $surname = $_SESSION['user']['surname'];
   $comment = $_POST["review"];
   $rating = $_POST["rate"];
   $curdate = date("Y/m/d");

   if ( $comment != "" ) {
   
   $oid = $mysqli->query("INSERT INTO feedbacks (users_email, products_id, rate, review, date) 
   VALUES ('".$_SESSION['user']['email']."', ".$_GET['id'].", '$rating', '$comment', '$curdate')");

   header("location:/MotorShop/product-list-customer.php");

   }

}

$main->setContent('dynamic',$body->get());
$main->close();
