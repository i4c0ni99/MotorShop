<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

function formatPrice($price) {
    return number_format($price, 2, ',', '.'); // Formatta il prezzo con due decimali, usando la virgola come separatore decimale e il punto come separatore delle migliaia
}

if (isset($_SESSION['user']) && (isset($_GET['id']))) {
    
    $order_id = $mysqli->real_escape_string($_GET['id']);
    
    // Verifica lo stato dell'ordine
    $order_status_query = $mysqli->query("SELECT state FROM orders WHERE id = '{$order_id}'");
    
    if ($order_status_query && $order_status_query->num_rows > 0) {
        $order_status_data = $order_status_query->fetch_assoc();
        
        if ($order_status_data['state'] == 'delivered') {
            $body = new Template("skins/motor-html-package/motor/my-orders.html");
        } else {
            $body = new Template("skins/motor-html-package/motor/my-pending-orders.html");
        }

        $order_status_query = $mysqli->query( SELECT 
    sa.name, 
    sa.surname, 
    sa.province, 
    sa.city, 
    sa.street_address, 
    sa.cap, 
    sa.phone 
FROM 
    orders o 
JOIN 
    shipping_address sa ON o.shipping_address_id = sa.id 
WHERE 
    o.id = '{$order_id}';

        $body->setContent("address", $order_status_data['shipping_address_id']);
        
    } else {
        // Nessun ordine trovato o errore nella query
        error_log("Order not found or error in query for order ID: $order_id");
        $body = new Template("skins/motor-html-package/motor/my-pending-orders.html");
    }
    
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    
    $main->setContent('name', $_SESSION['user']['name']);
    $main->setContent('surname', $_SESSION['user']['surname']);
    $main->setContent('email', $_SESSION['user']['email']);

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

    $main->setContent("dynamic", $body->get());
    $main->close();

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>