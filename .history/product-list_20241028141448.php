<?php 

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";
include "include/utils/priceFormatter.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {
$PAGE = 0;
    $TO   = 9;

    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        $PAGE = ($_GET['page'] - 1) * $TO;
    }

    // Query per contare il numero totale di prodotti
    $totalResult = $mysqli->query("SELECT COUNT(*) AS total FROM products");
    $totalCount = $totalResult->fetch_assoc()['total'];
    $totalPages = ceil($totalCount / $TO); // Calcola il numero totale di pagine

    $main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
    $body = new Template("skins/multikart_all_in_one/back-end/product-list.html");

    // Query per ottenere i prodotti della pagina corrente
    $oid = $mysqli->query("SELECT id, title FROM products LIMIT $PAGE, $TO");

    if ($oid->num_rows > 0) {
        foreach ($oid as $key) {
            $productId = $key['id'];
            $body->setContent("id", $productId);
            $body->setContent("title", $key['title']);
    
            // Media recensioni prodotto
            $average_query = $mysqli->query("SELECT AVG(rate) AS average_rate FROM feedbacks WHERE products_id = $productId");
            if ($average_query && $average_query->num_rows > 0) {
                $average_data = $average_query->fetch_assoc();
                $new_medium_rate = $average_data['average_rate'];
                $body->setContent("average_rate", $new_medium_rate); /
            } else {
                $body->setContent("average_rate", "N/A");
            }

            // Recupera l'immagine del prodotto
            $data = $mysqli->query("SELECT imgsrc FROM images WHERE product_id={$productId} LIMIT 1");
            if ($data->num_rows > 0) {
                $item = $data->fetch_assoc();
                $body->setContent("img", $item['imgsrc']);
            } else {
                // Immagine di default se non presente nel DB
                $body->setContent("img", "path/to/default/image.jpg");
            }

            // Recupera il prezzo del prodotto
            $priceData = $mysqli->query("SELECT MIN(price) AS min_price FROM sub_products WHERE products_id = {$productId}");
            if ($priceData->num_rows > 0) {
                $priceItem = $priceData->fetch_assoc();
                $price = strval($priceItem['min_price']);
                $body->setContent("price", formatPrice($price));
            } else {
                $body->setContent("price", "N/A");
            }

    // Genera i link di paginazione
    $pagination = '';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i == ($_GET['page'] ?? 1)) ? 'active' : '';
        $pagination .= "<a href='?page=$i' class='$active'>$i</a> ";
    }
    
    // Aggiungi la paginazione al template
    $body->setContent("pagination", $pagination);

if ($oid->num_rows > 0) {
   foreach ($oid as $key) {
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
      $priceData = $mysqli->query("SELECT MIN(price) AS min_price FROM sub_products WHERE products_id = {$productId}");
      if ($priceData->num_rows > 0) {
         $priceItem = $priceData->fetch_assoc();
         $price = strval($priceItem['min_price']);
         $body->setContent("price", formatPrice($price));
      } else {
         $body->setContent("price", "N/A");
      }
   }
}
        }
    }
            }

$main->setContent("dynamic", $body->get());
$main->setContent("body", $body->get());
$main->close();

} else {
   header("Location: /MotorShop/login.php");
   exit;
}

?>