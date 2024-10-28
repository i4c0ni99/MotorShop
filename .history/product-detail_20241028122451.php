<?php

session_start();

require "include/dbms.inc.php";
require "include/template2.inc.php";

include "include/utils/priceFormatter.php";


if (isset($_SESSION['user'])) {
    require "include/auth.inc.php";
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    $body = new Template("skins/motor-html-package/motor/product-detail.html");
  
    $body->setContent('name', htmlspecialchars($_SESSION['user']['name']));
    $body->setContent('surname', htmlspecialchars($_SESSION['user']['surname']));
    $body->setContent('email', htmlspecialchars($_SESSION['user']['email']));
    $body->setContent('phone', htmlspecialchars($_SESSION['user']['phone']));
} else {
 
    $main = new Template("skins/motor-html-package/motor/frame_public.html");
    $body = new Template("skins/motor-html-package/motor/product-detail.html");
}




$oid = $mysqli->query("SELECT title,id,categories_id,subcategories_id,description,specification,information FROM products where id={$_GET['id']} AND products.availability = 1 ");
$result = $oid->fetch_assoc();
$categories = $mysqli->query("SELECT name FROM categories where id={$result['categories_id']}");
$category = $categories->fetch_assoc();
$body->setContent('category', $category['name']);

$subcategories = $mysqli->query("SELECT name FROM subcategories where id={$result['subcategories_id']}");
$subcategory = $subcategories->fetch_assoc();
$body->setContent('subcategory', $subcategory['name']);

$dizionario= array();   
$body->setContent("id", $result['id']);
$body->setContent("title", $result['title']);
$body->setContent("descrizione", $result['description']);
$body->setContent("information", $result['information']);
$body->setContent("specification", $result['specification']);

// Carica i dati dei sottoprodotti collegati al prodotto
$data = $mysqli->query("SELECT id,price,color,quantity as sub_quantity FROM sub_products where products_id={$_GET['id']}  AND sub_products.availability = 1");
$result1 = $data->fetch_assoc();
$offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$result1['id']} ");
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
$oid1 = $mysqli->query("SELECT imgsrc FROM images where product_id={$_GET['id']}");
$result2 = $oid1->fetch_assoc();

// Visualizzazione feedback
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

// L'utente ha scelto colore e size poiché la get contiene l'id del subproduct corrispondente
if (isset($_GET['id']) && !isset($_GET['subId'])) {
   
  $body->setContent("imgView",$result2['imgsrc']);
  $body->setContent("demo_img",$result2['imgsrc']);

}

$uniqueColor = [];
foreach ($data as $item) {

    if(!array_key_exists($item['color'], $uniqueColor)){
   $body->setContent("colorDiv", '<li><a href="http://localhost/MotorShop/product-detail.php?id=' . $result['id'] . '&subId=' . $item['id'] .'&color='.urlencode($item['color']).'">
            <span style="background-color:' . $item['color'] . '" class="icon-color"></span>
          </a>
      </li>');

    $uniqueColor[$item['color']]=0;
   
   $body->setContent("subId", $item['id']);
   $body->setContent("color", $item['color']);
   
}
}

// L'utente ha selezionato il colore
if (isset($_GET['color'])) {
    
    
    $data1 = $mysqli->query("SELECT id, size, quantity, color 
FROM sub_products 
WHERE products_id = ".$_GET['id']."
AND sub_products.availability = 1 
AND color = '".$_GET['color']."' 
ORDER BY 
    CASE 
        -- Ordinamento per taglie alfabetiche
        WHEN size = 'XS' THEN 1
        WHEN size = 'S' THEN 2
        WHEN size = 'M' THEN 3
        WHEN size = 'L' THEN 4
        WHEN size = 'XL' THEN 5
        WHEN size = 'XXL' THEN 6
        -- Ordinamento per taglie numeriche
        WHEN size = '36' THEN 7
        WHEN size = '38' THEN 8
        WHEN size = '40' THEN 9
        WHEN size = '42' THEN 10
        WHEN size = '44' THEN 11
        WHEN size = '46' THEN 12
        ELSE 13 -- per eventuali taglie extra
    END;");
    
   foreach ($data1 as $item2) {

    echo "<script>console.log(".$item2['size'].")</script>";
            $body->setContent("size", '<li class="active"><a href="http://localhost/MotorShop/product-detail.php?id=' . $result['id'] . '&subId=' . $item2['id'] .'&color='.urlencode($item2['color']). '&size=' . $item2['size'] . '" class="mv-btn mv-btn-style-8">' . $item2['size'] . '</a></li>');
            $dizionario[$item2['size']] = $item2['quantity']; 
        
       
   }
   
$cart_quantity=$mysqli->query("SELECT quantity FROM `cart`WHERE subproduct_id = {$item['id']}");
   $cartData=$cart_quantity->fetch_assoc();
   $body->setContent("prodQuantity",$dizionario[$_GET['size']]);
   if($cartData){
    $body->setContent("quantity", $cartData['quantity']);
       
    }else {
        $body->setContent("quantity", 1);
    }
    
// L'utente ha selezionato la taglia
   if (isset($_GET['size'])) 
   $body->setContent("buttons",'
                    <div class="block-27-button">
                        <div class="mv-dp-table align-middle">
                        <div class="mv-dp-table-cell">
                            <div class="mv-btn-group text-left">
                            <div class="group-inner" >
                                <button type="button" class="mv-btn mv-btn-style-1 btn-1-h-50 responsive-btn-1-type-3 btn-add-to-cart" data-id="'.$_GET['subId'].'">
                                <span class="btn-inner">
                                <i class="btn-icon fa fa-cart-plus">
                                </i><span class="btn-text">add to cart</span>
                                </span>
                                </button>
                            
                                <button type="button" class="mv-btn mv-btn-style-3 btn-3-h-50 responsive-btn-3-type-1 btn-add-to-wishlist" data-id="'.$_GET['subId'].'"><i class="fa fa-heart-o"></i></button>
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
  if (empty($_POST["rate"])) {
      echo "Devi inserire una valutazione per poter pubblicare la recensione";
      exit;
  }

  $name = $_SESSION['user']['name'];
  $surname = $_SESSION['user']['surname'];
  $comment = $_POST["review"];
  $rating = $_POST["rate"];
  $curdate = date("Y/m/d");

  if (empty($_SESSION['user']['email'])) {
      header("location:/MotorShop/login.php");
      exit; 
  } elseif (!empty($comment)) {
      
      $user_email = $_SESSION['user']['email'];
      $product_id = $_GET['id'];
      
      $check_query = "SELECT COUNT(*) as review_count FROM feedbacks 
                      WHERE users_email = '$user_email' 
                      AND products_id = $product_id";
       $check_result = $mysqli->query($check_query);
       $review_count = $check_result->fetch_assoc()['review_count'];

       if ($review_count > 0) {
           echo "Hai già inserito una recensione per questo prodotto.";
           exit;
       }
      
      $oid = $mysqli->query("INSERT INTO feedbacks (users_email, products_id, rate, review, date) 
                             VALUES ('$user_email', $product_id, '$rating', '$comment', '$curdate')");
      
      // Calcola il nuovo mediumRate
      $average_query = $mysqli->query("SELECT AVG(rate) AS average_rate FROM feedbacks WHERE products_id = $product_id");

      if ($average_query && $average_query->num_rows > 0) {
          $average_data = $average_query->fetch_assoc();
          $new_medium_rate = $average_data['average_rate'];

          // Aggiorna la colonna mediumRate nella tabella products
          $mysqli->query("UPDATE products SET mediumRate = {$new_medium_rate} WHERE id = $product_id");
      }

      header("location:/MotorShop/product-detail.php?id=$product_id");
      exit; 
  }
}

$main->setContent('dynamic',$body->get());
$main->close();