<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

function formatPrice($price) {
    return number_format($price, 2, ',', '.'); 
}

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/order-detail.html");

if (isset($_SESSION['user']['groups']) && $_SESSION['user']['groups'] == 1) {
      
    if (isset($_GET['id'])) {
    
    $order_id = $mysqli->real_escape_string($_GET['id']);
    
    $main->setContent('name', $_SESSION['user']['name']);
    $main->setContent('surname', $_SESSION['user']['surname']);
    $main->setContent('email', $_SESSION['user']['email']);

    $body->setContent("order_id", $_GET['id']);

    $code_query = $mysqli->query("SELECT code FROM orders WHERE id = '$_GET['id']'");
    $code_query = $shipping_address_query->fetch_assoc();

    $shipping_address_query = $mysqli->query(" SELECT 
    sa.name, 
    sa.surname, 
    sa.province, 
    sa.city, 
    sa.streetAddress, 
    sa.cap, 
    sa.phone 
FROM 
    orders o 
JOIN 
    shipping_address sa ON o.shipping_address_id = sa.id 
WHERE 
    o.id = '{$order_id}'");

        $shipping_address_data = $shipping_address_query->fetch_assoc();
        
        $body->setContent("ad_name", $shipping_address_data['name']);
        $body->setContent("ad_surname", $shipping_address_data['surname']);
        $body->setContent("ad_province", $shipping_address_data['province']);
        $body->setContent("ad_city", $shipping_address_data['city']);
        $body->setContent("ad_street", $shipping_address_data['streetAddress']);
        $body->setContent("ad_cap", $shipping_address_data['cap']);
        $body->setContent("ad_phone", $shipping_address_data['phone']);
    
        
    } else {
        // Nessun ordine trovato o errore nella query
        error_log("Ordine non trovato per l'ordine con ID: $order_id");
        header("Location: /MotorShop/index.php");
    }

    // Definisci il numero di prodotti per pagina
    $itemsPerPage = 5;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $itemsPerPage;

    // Conta il numero totale di prodotti
    $totalItemsQuery = "SELECT COUNT(*) AS total FROM orders_has_products WHERE order_id = '{$order_id}'";
    $totalItemsResult = $mysqli->query($totalItemsQuery);
    $totalItemsRow = $totalItemsResult->fetch_assoc();
    $totalItems = $totalItemsRow['total'];

    // Calcola il numero totale di pagine
    $totalPages = ceil($totalItems / $itemsPerPage);

    // Recupera i prodotti per la pagina corrente
    $oid = $mysqli->query("SELECT id, sub_products_id, quantity FROM orders_has_products WHERE order_id = '{$order_id}' LIMIT $offset, $itemsPerPage");

    if ($oid && $oid->num_rows > 0) {
        foreach ($oid as $sub) {
            $body->setContent("id", $sub['id']);
            $body->setContent("sub_id", $sub['sub_products_id']);
            $body->setContent("sub_quantity", $sub['quantity']);
            
            // Aggiungi la query per ottenere size, color e price da sub_products
            $sub_product_id = $sub['sub_products_id'];
            $sub_product_query = $mysqli->query("SELECT size, color, price, products_id FROM sub_products WHERE id = '{$sub_product_id}'");
            
            if ($sub_product_query && $sub_product_query->num_rows > 0) {
                $sub_product_data = $sub_product_query->fetch_assoc();
                
                $calculatedPrice = $sub_product_data['price'] * $sub['quantity']; // Calcola il prezzo totale
                $body->setContent("size", $sub_product_data['size']);
                $body->setContent("color", $sub_product_data['color']);
                $body->setContent("price", formatPrice($calculatedPrice)); // Utilizzo di formatPrice() per il prezzo totale
                
                // Aggiungi la query per ottenere il titolo del prodotto dalla tabella products
                $product_id = $sub_product_data['products_id'];
                $order_title_query = $mysqli->query("SELECT title FROM products WHERE id = '{$product_id}'");
                
                if ($order_title_query && $order_title_query->num_rows > 0) {
                    $order_title_data = $order_title_query->fetch_assoc();
                    $body->setContent("title", $order_title_data['title']);
                } else {
                    // Se non viene trovato alcun prodotto corrispondente
                    error_log("Product title not found for product ID: $product_id");
                    $body->setContent("title", 'Titolo non disponibile');
                }
            } else {
                // Se non viene trovato alcun sub_product corrispondente
                error_log("Sub-product not found for ID: $sub_product_id");
                $body->setContent("size", '');
                $body->setContent("color", '');
                $body->setContent("price", '');
                $body->setContent("title", 'Titolo non disponibile');
            }
        }
    } else {
        // Nessun ordine trovato
        $body->setContent("id", 'Non abbiamo trovato il tuo ordine');
        $body->setContent("sub_id", '');
        $body->setContent("sub_quantity", '');
        $body->setContent("size", '');
        $body->setContent("color", '');
        $body->setContent("price", '');
        $body->setContent("title", '');
    }

    // Gestione della paginazione
    if ($totalPages > 1) {
        $pagination = '';
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $page) {
                $pagination .= "<span class='current-page'>$i</span> ";
            } else {
                $pagination .= "<a href='/MotorShop/order-summary.php?id={$order_id}&page=$i'>$i</a> ";
            }
        }
        $body->setContent("pagination", $pagination);
    } else {
        $body->setContent("pagination", '');
    }
    

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();

?>