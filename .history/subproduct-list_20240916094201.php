<?php
session_start();

require "include/dbms.inc.php";
require "include/template2.inc.php";
include "include/utils/priceFormatter.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/subproduct-list.html");

// Verifica se è stato passato l'id del prodotto tramite GET
if (isset($_GET['id'])) {
    $productId = $mysqli->real_escape_string($_GET['id']);

    // Verifica se $productId è stato impostato correttamente
    if (empty($productId)) {
        die("ID del prodotto non valido.");
    }

    // Esegui la query per recuperare i sottoprodotti del prodotto specificato
    $sub_query = "SELECT * FROM sub_products WHERE products_id = $productId";
    $sub_result = $mysqli->query($sub_query);

    // Controlla se la query ha avuto successo
    if (!$sub_result) {
        die("Errore nella query: " . $mysqli->error);
    }

    if ($sub_result->num_rows > 0) {
        // Prepara un array di sottoprodotti
        $subProducts = [];

        while ($row = $sub_result->fetch_assoc()) {
            $subProducts[] = [
                'id' => $row['id'],
                'products_id' => $row['products_id'],
                'color' => $row['color'],
                'size' => $row['size'],
                'quantity' => $row['quantity'],
                'availability' => $row['availability']
            ];
        }

        // Passa l'array dei sottoprodotti al template
        foreach ($subProducts as $subProduct) {
            $body->setContent('id', $subProduct['id']);
            $body->setContent('products_id', $subProduct['products_id']);
            $body->setContent('color', $subProduct['color']);
            $body->setContent('size', $subProduct['size']);
            $body->setContent('quantity', $subProduct['quantity']);
            $body->setContent('availability', $subProduct['availability']);
            
            $query = "SELECT imgsrc FROM images WHERE sub_products_id = " . (int)$subProduct['id'] . " LIMIT 1";
            $data = $mysqli->query($query);
            if ($data->num_rows > 0) {
                $item = $data->fetch_assoc();
                $body->setContent("img", $item['imgsrc']);
            } else {
                // Immagine di default se non presente nel DB
                $body->setContent("img", "path/to/default/image.jpg");
            }
            
            $offer_query = "SELECT percentage FROM offerts WHERE subproduct_id = ";
        }
    } else {
        // Nessun sottoprodotto trovato
        $body->setContent("subProducts", "Nessun sottoprodotto trovato");
    }
} else {
    // Messaggio di errore se l'id del prodotto non è stato fornito
    header('Location: /MotorShop/product-list.php');
    die("ID del prodotto non fornito.");
}

$main->setContent('body', $body->get());
$main->close();
?>