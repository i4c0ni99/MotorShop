<?php

session_start();

require "include/dbms.inc.php";
require "include/template2.inc.php";
include "include/utils/priceFormatter.php";

$main = new Template("skins/motor-html-package/motor/frame_public.html");
$body = new Template("skins/motor-html-package/motor/product-detail.html");

$oid = $mysqli->query("SELECT title,id,categories_id,description,specification,information FROM products where id={$_GET['id']}");
$result = $oid->fetch_assoc();
$categories = $mysqli->query("SELECT name FROM categories where id={$result['categories_id']}");
$category = $categories->fetch_assoc();
$body->setContent('category', $category['name']);

$body->setContent("id", $result['id']);
$body->setContent("title", $result['title']);
$body->setContent("descrizione", $result['description']);
$body->setContent("information", $result['information']);
$body->setContent("specification", $result['specification']);


$data = $mysqli->query("SELECT id,price,color FROM sub_products where products_id={$_GET['id']}");
$result1 = $data->fetch_assoc();
$offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$result1['id']}");
            $offerItem = $offer->fetch_assoc();
            if($offerItem){
            $body->setContent('offer',
            ' <div onclick="$(this).remove()" class="block-27-sale-off mv-label-style-2 text-center">
                    <div class="label-2-inner">
                      <ul class="label-2-ul">
                        <li class="number">-'.$offerItem['percentage'].'%</li>
                        <li class="text">sconto</li>
                      </ul>
                    </div>
                  </div>');
            $price = $result1['price'];
            $body->setContent("pricePercentage",$price - ($price * ($offerItem['percentage']/100)));
            $body->setContent("price", formatPrice($price));
            }else{
            $price = strval($result1['price']);
            $body->setContent("percentage",$offerItem['percentage']);
            $body->setContent("pricePercentage",formatPrice($price));
            
            }
$oid1 = $mysqli->query("SELECT imgsrc FROM images where product_id={$_GET['id']} ");
$result2 = $oid1->fetch_assoc();

//visualizzazione feedback
$feedback = $mysqli->query("SELECT * FROM feedbacks where products_id = {$_GET['id']}");
$count = 0;
$mediumReateRet;
if( $feedback -> num_rows > 0){
foreach ($feedback as $mediumReate){
    $mediumReateRet += $mediumReate['rate'];
   }
   $mediumReateRet /= $feedback -> num_rows ;
   $body -> setContent('mediumRateNum',$mediumReateRet);

foreach ($feedback as $item) {
   $count= 0;
   $rate = $item['rate'] ;
   $body->setContent("user", $_SESSION['user']['name']);
   $body->setContent("creationDate", $item['date']);
   $body->setContent("content", $item['review']);
   $body->setContent("rate", $rate);
} 
}

if (isset($_GET['id']) && !isset($_GET['subId'])) {
   
  $body->setContent("imgView",$result2['imgsrc']);
  $body->setContent("demo_img",$result2['imgsrc']);

}

$uniqueColor = [];
foreach ($data as $item) {

    if(!array_key_exists($item['color'], $uniqueColor)){
   $body->setContent("colorDiv", '<li><a href="http://localhost/MotorShop/product-detail.php?id=' . $result['id'] . '&subId=' . $item['id'] . '">
            <span style="background-color:' . $item['color'] . '" class="icon-color"></span>
          </a>
      </li>');
    $uniqueColor[$item['color']]=0;
   $body->setContent("subId", $item['id']);
   $body->setContent("color", $item['color']);
   $body->setContent("quantity", $item['quantity']);
    }else{echo('ciao');}
}


   

if (isset($_GET['subId'])) {

   $data1 = $mysqli->query("SELECT size from sub_products where products_id ={$_GET['id']}");
   foreach ($data1 as $item2) {

      $body->setContent("size", '<li class="active"><a href="http://localhost/MotorShop/product-detail.php?id=' . $result['id'] . '&subId=' . $item['id'] . '&size=' . $item2['size'] . '" class="mv-btn mv-btn-style-8">' . $item2['size'] . '</a></li>');
    
   }
   

   if (isset($_GET['size'])) 
   $body->setContent("buttons",'
            <div class="block-27-button">
                        <div class="mv-dp-table align-middle">
                        <div class="mv-dp-table-cell">
                            <div class="mv-btn-group text-left">
                            <div class="group-inner">
                                <form method="post" action="/MotorShop/cart.php?id='.$_GET['subId'].'">
                                <input type="hidden" name="add_to_cart" value="1">
                                <button type="submit" class="mv-btn mv-btn-style-1 btn-1-h-50 responsive-btn-1-type-3 btn-add-to-cart">
                                    <span class="btn-inner">
                                    <i class="btn-icon fa fa-cart-plus"></i>
                                    <span class="btn-text">Carrello</span>
                                    </span>
                                </button>
                                </form>
                                <form method="post" action="/MotorShop/wishlist.php?id='.$_GET['subId'].'">
                                <input type="hidden" name="wishlist" value="1">
                                <button type="submit" class="mv-btn mv-btn-style-3 btn-3-h-50 responsive-btn-3-type-1 btn-add-to-wishlist"
                                    name="add_to_wishlist">
                                    <span class="btn-inner">
                                    <span class="btn-text">Wishlist</span>
                                    <i class="fa fa-heart-o"></i>
                                    </span>
                                </button>
                                </form>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>   ');

   $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$_GET['id']}");
            $offerItem = $offer->fetch_assoc();
            if($offerItem){
            $body->setContent('offer',
            ' <div onclick="$(this).remove()" class="block-27-sale-off mv-label-style-2 text-center">
                    <div class="label-2-inner">
                      <ul class="label-2-ul">
                        <li class="number">-'.$offerreItem['percentage'].'%</li>
                        <li class="text">sconto</li>
                      </ul>
                    </div>
                  </div>');
            $price = $result1['price'];
            $body->setContent("pricePercentage",$price - ($price * ($offerreItem['percentage']/100)));
            $body->setContent("price", formatPrice($price));
            }else{
            $price = strval($result1['price']);
            $body->setContent("percentage",$offerreItem['percentage']);
            $body->setContent("pricePercentage",formatPrice($price));
            
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