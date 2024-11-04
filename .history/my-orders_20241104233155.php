<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

function formatPrice($price) {
    return number_format($price, 2, ',', '.'); 
}

if (isset($_SESSION['user'])) {
    
    $order_id = $mysqli->real_escape_string($_GET['id']);
    
    // stato dell'ordine
    $order_status_query = $mysqli->query("SELECT state FROM orders WHERE id = '{$order_id}'");

    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    
    if ($order_status_query && $order_status_query->num_rows > 0) {
        $order_status_data = $order_status_query->fetch_assoc();
        
        if ($order_status_data['state'] == 'delivered') {
            $body = new Template("skins/motor-html-package/motor/my-orders.html");
        } else {
            $body = new Template("skins/motor-html-package/motor/my-pending-orders.html");
        }

        $code_query = $mysqli->query("SELECT number, totalPrice FROM orders WHERE id = '{$order_id}'");
$code_query_data = $code_query->fetch_assoc();

$body->setContent("order_id", $order_id);
    
$body->setContent("order_number", $code_query_data['number']);
$body->setContent("order_total", $code_query_data['totalPrice']);

// dettagli
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

    if ($order_status_query && $order_status_query->num_rows > 0) {

        $shipping_address_data = $shipping_address_query->fetch_assoc();
        $body->setContent("ad_name", $shipping_address_data['name']);
        $body->setContent("ad_surname", $shipping_address_data['surname']);
        $body->setContent("ad_province", $shipping_address_data['province']);
        $body->setContent("ad_city", $shipping_address_data['city']);
        $body->setContent("ad_street", $shipping_address_data['streetAddress']);
        $body->setContent("ad_cap", $shipping_address_data['cap']);
        $body->setContent("ad_phone", $shipping_address_data['phone']);

    } else {
        error_log("Indirizzo non trovato per l'ordine con ID: $order_id");
    }
        
    } else {
        // Nessun ordine trovato o errore nella query
        error_log("Ordine non trovato per l'ordine con ID: $order_id");
        header("Location: /MotorShop/customer-dashboard.php");
    }
    
    $main->setContent('name', $_SESSION['user']['name']);
    $main->setContent('surname', $_SESSION['user']['surname']);
    $main->setContent('email', $_SESSION['user']['email']);

    // Paginazione
    $itemsPerPage = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $itemsPerPage;

    $totalItemsQuery = "SELECT COUNT(*) AS total FROM orders_has_products WHERE order_id = '{$order_id}'";
    $totalItemsResult = $mysqli->query($totalItemsQuery);
    $totalItemsRow = $totalItemsResult->fetch_assoc();
    $totalItems = $totalItemsRow['total'];

    $totalPages = ceil($totalItems / $itemsPerPage);

    // Recupera i prodotti per la pagina corrente
    $oid = $mysqli->query("SELECT id, sub_products_id, quantity FROM orders_has_products WHERE order_id = '{$order_id}' LIMIT $offset, $itemsPerPage");

    $products = [];
    
    if ($oid && $oid->num_rows > 0) {
        foreach ($oid as $sub) {
            $body->setContent("id", $sub['id']);
            $body->setContent("sub_id", $sub['sub_products_id']);
            $body->setContent("sub_quantity", $sub['quantity']);

            $products[$sub['sub_products_id']] = $sub['quantity'];
            
            // query per size, color e price
            $sub_product_id = $sub['sub_products_id'];
            $sub_product_query = $mysqli->query("SELECT size, color, price, products_id FROM sub_products WHERE id = '{$sub_product_id}'");
            
            if ($sub_product_query && $sub_product_query->num_rows > 0) {
                $sub_product_data = $sub_product_query->fetch_assoc();
                
                $calculatedPrice = $sub_product_data['price'] * $sub['quantity']; // prezzo totale
                $body->setContent("size", $sub_product_data['size']);
                $body->setContent("color", $sub_product_data['color']);
                $body->setContent("price", formatPrice($calculatedPrice)); 
                
                //  query per ottenere il titolo del prodotto
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
                // Se non viene trovato alcun sottoprodotto corrispondente
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

    // Funzione per cancellare un ordine
function cancelOrder($orderId) {
   global $mysqli;
   global $products;
   $deleteQuery = "DELETE FROM orders WHERE id = '{$orderId}'";
   $mysqli->query($deleteQuery);
   
   // eliminare da orders_has_products le varie istanze
   $deleteHasQuery = "DELETE FROM orders_has_products WHERE order_id = '{$orderId}'";
   return $mysqli->query($deleteQuery);
   // ripristinare le quantity dei sub, con cambio da 0 a 1 di availability eventualmente
   foreach($products as $id => $quantity) {
    $mysqli->query("UPDATE sub_products SET quantity = quantity + " . intval($quantity) . ", availability = 1 WHERE id = " . intval($id));
   }
   header("Location: /MotorShop/customer-dashboard.php");
   
}
// cancellazione di un ordine
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
   $orderId = $mysqli->real_escape_string($_GET['id']);
   // riaggiungere la quantity ai subproduct
   
   
   
   // funzione per cancellare l'ordine
   if (cancelOrder($orderId)) {
       header("Location: /MotorShop/customer-dashboard.php");
   } else {
       echo "Errore durante la cancellazione dell'ordine.";
   }
}

    $main->setContent("dynamic", $body->get());
    $main->close();

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>