<?php 
session_start();
require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";
include "include/utils/priceFormatter.php";

$PAGE = 0;
$TO   = 9;

if (isset($_GET['page']) && isset($_GET['to'])) {
   if ($_GET['page'] > 1) {
      $PAGE = ($_GET['page'] - 1) * 9;
   }

   $to   = $_GET['to'];
   if ($to > 9 || $to < 1) {
      $to = 9;
   }

   $TO   = $to;
}

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/product-list.html");

$oid = $mysqli->query("SELECT id, title FROM products LIMIT $PAGE, $TO");
$result = $oid;

if ($result->num_rows > 0) {
   foreach ($result as $key) {
      $productId = $key['id'];
      $body->setContent("id", $productId);
      $body->setContent("title", $key['title']);

      $data = $mysqli->query("SELECT imgsrc FROM images WHERE product_id={$productId} LIMIT 1");
      if ($data->num_rows > 0) {
         $item = $data->fetch_assoc();
         $body->setContent("img", $item['imgsrc']);
      } else {
         // Immagine di default se non presente nel DB
         $body->setContent("img", "path/to/default/image.jpg");
      }

      // Recupera il prezzo del prodotto
      
      $priceData = $mysqli->query("SELECT MIN(price) AS min_price FROM sub_products WHERE products_id={$productId}");
      if ($priceData->num_rows > 0) {
    $priceItem = $priceData->fetch_assoc();
    $price = strval($priceItem['min_price']);
    $body->setContent("price", formatPrice($price));
} else {
    $body->setContent("price", "N/A");
}
      
   }
}

$main->setContent("dynamic", $body->get());
$main->setContent("body", $body->get());
$main->close();
?>