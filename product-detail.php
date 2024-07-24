<?php
session_start();

require "include/dbms.inc.php";
require "include/template2.inc.php";
require_once "include/utils/priceFormatter.php";

// Verifica se l'utente è loggato e ha groups_id = 2
if (isset($_SESSION['user']['groups'])) {
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
} else {
    $main = new Template("skins/motor-html-package/motor/frame_public.html");
}

// Inizializza il corpo della pagina
$body = '';

// Verifica se è presente l'ID del prodotto nell'URL
if (isset($_GET['id']) && !isset($_GET['subId'])) {
    $body = new Template("skins/motor-html-package/motor/product-detail.html");
    $product_id = $mysqli->real_escape_string($_GET['id']);

    // Query per ottenere le informazioni di base del prodotto
    $oid = $mysqli->query("SELECT title, id, categories_id, description, specification, information FROM products WHERE id = $product_id");
    $result = $oid->fetch_assoc();

    if ($result) {
        // Ottieni la categoria
        $categories = $mysqli->query("SELECT name FROM categories WHERE id = {$result['categories_id']}");
        $category = $categories->fetch_assoc();
        $body->setContent('category', $category['name']);

        // Ottieni la sottocategoria
        $subcategory_query = $mysqli->query("SELECT name FROM subcategories WHERE id = (
            SELECT subcategories_id FROM products WHERE id = $product_id)");
        $subcategory = $subcategory_query->fetch_assoc();
        $body->setContent("subcategory", $subcategory ? $subcategory['name'] : "");

        // Popola il template con i dati del prodotto
        $body->setContent("id", $result['id']);
        $body->setContent("title", $result['title']);
        $body->setContent("description", $result['description']);
        $body->setContent("specification", $result['specification']);
        $body->setContent("information", $result['information']);

        // Inizializza array per taglie e colori unici
        $unique_sizes = [];
        $unique_colors = [];

        // Query per ottenere tutte le taglie disponibili
        $size_query = $mysqli->query("SELECT DISTINCT size FROM sub_products WHERE products_id = {$result['id']}");
        while ($size_row = $size_query->fetch_assoc()) {
            $unique_sizes[] = $size_row['size'];
        }

        // Query per ottenere tutti i colori disponibili
        $color_query = $mysqli->query("SELECT DISTINCT color FROM sub_products WHERE products_id = {$result['id']}");
        while ($color_row = $color_query->fetch_assoc()) {
            $unique_colors[] = $color_row['color'];
        }

        // Popola il template con le taglie
        $sizeContent = '';
        foreach ($unique_sizes as $size) {
            $sizeContent .= '<li onclick="selectSize(\'' . $size . '\')"><a href="#" class="mv-btn mv-btn-style-8">' . $size . '</a></li>';
        }
        $body->setContent("size", $sizeContent);

        // Popola il template con i colori
        $colorContent = '';
        foreach ($unique_colors as $color) {
            $colorContent .= '<li onclick="selectColor(\'' . $color . '\')"><a href="#" class="mv-btn mv-btn-style-8">' . $color . '</a></li>';
        }
        $body->setContent("colorDiv", $colorContent);

        // Caricamento delle recensioni
        $reviews_query = $mysqli->query("SELECT f.users_email, f.rate, f.review, f.date
                                         FROM feedbacks f
                                         WHERE f.products_id = '{$product_id}' 
                                         ORDER BY f.date DESC");

        if ($reviews_query && $reviews_query->num_rows > 0) {
            $reviews = $reviews_query->fetch_all(MYSQLI_ASSOC);

            $reviews_data = array();
            foreach ($reviews as $review) {
                $user_email = $review['users_email'];

                // Query per ottenere il nome dell'utente
                $name_query = $mysqli->query("SELECT name FROM users WHERE email = '{$user_email}'");
                $name_result = $name_query->fetch_assoc();
                $name = isset($name_result['name']) ? $name_result['name'] : '';

                // Query per ottenere il cognome dell'utente
                $surname_query = $mysqli->query("SELECT surname FROM users WHERE email = '{$user_email}'");
                $surname_result = $surname_query->fetch_assoc();
                $surname = isset($surname_result['surname']) ? $surname_result['surname'] : '';

                // Combinazione del nome completo
                $fullname = $name . ' ' . $surname;

                // Costruzione dell'oggetto recensione
                $review_item = array(
                    'fullname' => htmlspecialchars($fullname),
                    'date' => htmlspecialchars($review['date']),
                    'review' => nl2br(htmlspecialchars($review['review'])),
                    'rate' => htmlspecialchars($review['rate'])
                );
                $reviews_data[] = $review_item;
            }
            $body->setContent("reviews", $reviews_data);
        } else {
            $body->setContent("reviews", array()); 
        }

        // Query per ottenere le immagini associate al prodotto principale
        $img_query = $mysqli->query("SELECT imgsrc FROM images WHERE product_id = {$product_id}");
        $img = $img_query->fetch_assoc();
        if ($img) {
            $body->setContent("imgView", $img['imgsrc']);
            $body->setContent("demo_img", $img['imgsrc']);
        } else {
            $body->setContent("imgView", ""); 
            $body->setContent("demo_img", "");
            error_log("Immagini non trovate per il product_id: " . $product_id);
        }

        // Caricamento dei prodotti correlati
        $related_products_query = $mysqli->query("SELECT id, title FROM products
                                                 WHERE categories_id = {$result['categories_id']}
                                                 AND id != {$result['id']}
                                                 ORDER BY CASE WHEN title LIKE '{$result['title']}' THEN 1 ELSE 2 END, title ASC
                                                 LIMIT 4");

        while ($related_product = $related_products_query->fetch_assoc()) {
            $body->setContent("related_product_id", $related_product['id']);
            $body->setContent("related_product_title", $related_product['title']);
        }
    }
}

// Verifica se è presente subId nell'URL
if (isset($_GET['subId'])) {
    $body = new Template("skins/motor-html-package/motor/subproduct-detail.html");
    $sub_product_id = $mysqli->real_escape_string($_GET['subId']);

    // Query per ottenere le informazioni del sottoprodotto
    $data = $mysqli->query("SELECT sp.price, sp.color, sp.quantity, sp.size 
                            FROM sub_products sp
                            WHERE sp.id = {$sub_product_id}");

    $sizes = [];
    $colors = [];
    $product_id = $_GET['id']; // Assicurati che l'ID del prodotto sia passato nell'URL

    while ($item = $data->fetch_assoc()) {
        $size = $item['size'];
        $color = $item['color'];

        // Aggiungi taglie alla lista se non sono già presenti
        if (!in_array($size, $sizes)) {
            $sizes[] = $size;
        }

        // Aggiungi colori alla lista se non sono già presenti
        if (!in_array($color, $colors)) {
            $colors[] = $color;
        }

        // Popola il template con i dati dei colori
        $body->setContent("colorDiv", '<li><a href="product-detail.php?id=' . $product_id . '&subId=' . $sub_product_id . '">
                    <span style="background-color:' . $color . '" class="icon-color"></span>
                  </a>
              </li>');

        $body->setContent("subId", $sub_product_id);
        $body->setContent("color", $color);
        $body->setContent("price", formatPrice($item['price']));
    }

    // Popola il template con le taglie
    foreach ($sizes as $size) {
        $body->setContent("size", '<li class="active"><a href="#" class="mv-btn mv-btn-style-8">' . $size . '</a></li>');
    }

    // Query per ottenere le immagini associate al sottoprodotto
    $img_query = $mysqli->query("SELECT imgsrc FROM images WHERE sub_products_id = {$sub_product_id}");
    $img = $img_query->fetch_assoc();
    if ($img) {
        $body->setContent("imgView", $img['imgsrc']);
        $body->setContent("demo_img", $img['imgsrc']);
    } else {
        $body->setContent("imgView", ""); 
        $body->setContent("demo_img", "");
        error_log("Immagini non trovate per il sub_product_id: " . $sub_product_id);
    }

    // Query per ottenere la quantità del sottoprodotto
    $quantity_query = $mysqli->query("SELECT quantity FROM sub_products WHERE id = {$sub_product_id}");
    if ($quantity_query) {
        $quantity_result = $quantity_query->fetch_assoc();
        $quantity_available = $quantity_result['quantity'];
    }  
}

// Se nessuno dei parametri size, color, subId è presente, mostrare tutte le taglie e tutti i colori disponibili
if (!isset($_GET['subId']) && !isset($_GET['size']) && !isset($_GET['color'])) {
    $product_id = $mysqli->real_escape_string($_GET['id']);

    // Query per ottenere tutte le taglie disponibili
    $size_query = $mysqli->query("SELECT DISTINCT size FROM sub_products WHERE products_id = {$product_id}");
    $unique_sizes = [];
    while ($size_row = $size_query->fetch_assoc()) {
        $unique_sizes[] = $size_row['size'];
    }

    // Query per ottenere tutti i colori disponibili
    $color_query = $mysqli->query("SELECT DISTINCT color FROM sub_products WHERE products_id = {$product_id}");
    $unique_colors = [];
    while ($color_row = $color_query->fetch_assoc()) {
        $unique_colors[] = $color_row['color'];
    }

    // Popola il template con le taglie
    $sizeContent = '';
    foreach ($unique_sizes as $size) {
        $sizeContent .= '<li><a href="product-detail.php?id=' . $product_id . '&size=' . urlencode($size) . '" class="mv-btn mv-btn-style-8">' . $size . '</a></li>';
    }
    $body->setContent("size", $sizeContent);

    // Popola il template con i colori
    $colorContent = '';
    foreach ($unique_colors as $color) {
        $colorContent .= '<li><a href="product-detail.php?id=' . $product_id . '&color=' . urlencode($color) . '" class="mv-btn mv-btn-style-8">' . $color . '</a></li>';
    }
    $body->setContent("colorDiv", $colorContent);
}

// Aggiorna il template principale con il corpo della pagina
$main->setContent('dynamic', $body->get()); // Utilizzo corretto di setContent per aggiungere il corpo della pagina al template principale
$main->close();
?>