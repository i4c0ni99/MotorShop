<?php 
session_start();
require "include/template2.inc.php";
require "include/dbms.inc.php";

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


   $main = new Template("skins/motor-html-package/motor/frame_public.html");
   $body = new Template("skins/motor-html-package/motor/product-grid-3.html");


$oid = $mysqli->query("SELECT title,id FROM products LIMIT $PAGE,$TO");
$result = $oid;

if ($result->num_rows > 0) {
   foreach ($result as $key) {

      $body->setContent("id", $key['id']);
      $body->setContent("title", $key['title']);




      $data = $mysqli->query("SELECT images.imgsrc,sub_products.price FROM products join sub_products ON sub_products.products_id=products.id 
      join images ON images.sub_products_id=sub_products.id where products.id={$key['id']} limit 0,1");
      if ($data->num_rows > 0) {


         foreach ($data as $item) {
            $price = strval($item['price']);
            $body->setContent("img", $item['imgsrc']);
            $body->setContent("price", formatPrice($price));
         }
      }
   }
}


$main->setContent("dynamic", $body->get());
$main->setContent("body", $body->get());
$main->close();

?>