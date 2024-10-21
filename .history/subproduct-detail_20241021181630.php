<?php

session_start();

require "include/dbms.inc.php";
require "include/template2.inc.php";
require_once "include/utils/priceFormatter.php";

// Verifica se l'utente è loggato e ha groups_id = 2
if (isset($_SESSION['user']['groups']) && $_SESSION['user']['groups'] == 1) {
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    $body = new Template("skins/motor-html-package/motor/subproduct-detail.html");

// Verifica se è presente l'ID del sottoprodotto nell'URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) { // Verifica che id sia numerico
    $subproduct_id = $mysqli->real_escape_string($_GET['id']);

    // Query per ottenere l'ID del prodotto associato al sottoprodotto
    $product_id_query = $mysqli->query("SELECT products_id FROM sub_products WHERE id = $subproduct_id");

    if ($product_id_query && $product_id_row = $product_id_query->fetch_assoc()) {
        $product_id = $product_id_row['products_id'];

        // Query per ottenere le informazioni di base del prodotto
        $product_query = $mysqli->query("SELECT title, id, categories_id, description, specification, information FROM products WHERE id = $product_id");

        if ($product_query && $result = $product_query->fetch_assoc()) {
            // Ottieni la categoria
            $category_query = $mysqli->query("SELECT name FROM categories WHERE id = {$result['categories_id']}");
            if ($category_query && $category_row = $category_query->fetch_assoc()) {
                $category = $category_row['name'];
                $body->setContent('category', $category);
            }

            // Ottieni la sottocategoria
            $subcategory_query = $mysqli->query("SELECT name FROM subcategories WHERE id = (
                SELECT subcategories_id FROM products WHERE id = $product_id)");
            if ($subcategory_query && $subcategory_row = $subcategory_query->fetch_assoc()) {
                $subcategory = $subcategory_row['name'];
                $body->setContent("subcategory", $subcategory);
            }

            // Popola il template con i dati del prodotto
            $body->setContent("id", $subproduct_id);
            $body->setContent("product_id", $result['id']);
            $body->setContent("title", $result['title']);
            $body->setContent("description", $result['description']);
            $body->setContent("specification", $result['specification']);
            $body->setContent("information", $result['information']);

            // Query per ottenere le informazioni del sottoprodotto
            $sub_query = $mysqli->query("SELECT color, size, quantity, price, availability FROM sub_products WHERE id = {$subproduct_id}");
            if ($sub_query && $sub = $sub_query->fetch_assoc()) {
                $body->setContent("color", $sub['color']);
                $body->setContent("size", $sub['size']);
                $body->setContent("quantity", $sub['quantity']);
                $body->setContent("price", formatPrice($sub['price']));
                $body->setContent("availability", $sub['availability']);
            }

            // Caricamento delle recensioni
            $reviews_query = $mysqli->query("SELECT f.users_email, f.rate, f.review, f.date
                                             FROM feedbacks f
                                             WHERE f.products_id = '{$product_id}' 
                                             ORDER BY f.date DESC");

            if ($reviews_query && $reviews_query->num_rows > 0) {
                $reviews_data = [];
                while ($review = $reviews_query->fetch_assoc()) {
                    $user_email = $review['users_email'];

                    // Query per ottenere il nome dell'utente
                    $name_query = $mysqli->query("SELECT name FROM users WHERE email = '{$user_email}'");
                    if ($name_query && $name_row = $name_query->fetch_assoc()) {
                        $name = $name_row['name'];

                        // Query per ottenere il cognome dell'utente
                        $surname_query = $mysqli->query("SELECT surname FROM users WHERE email = '{$user_email}'");
                        if ($surname_query && $surname_row = $surname_query->fetch_assoc()) {
                            $surname = $surname_row['surname'];

                            // Combinazione del nome completo
                            $fullname = trim($name . ' ' . $surname);
                            $review_item = [
                                'fullname' => htmlspecialchars($fullname),
                                'date' => htmlspecialchars($review['date']),
                                'review' => nl2br(htmlspecialchars($review['review'])),
                                'rate' => htmlspecialchars($review['rate'])
                            ];
                            $reviews_data[] = $review_item;
                        }
                    }
                }
                $body->setContent("reviews", $reviews_data);
            } else {
                $body->setContent("reviews", []); 
            }

            // Query per ottenere le immagini associate al prodotto principale
            $img_query = $mysqli->query("SELECT imgsrc FROM images WHERE sub_products_id = {$subproduct_id}");
            if ($img_query && $img_row = $img_query->fetch_assoc()) {
                $img_src = $img_row['imgsrc'];
                $body->setContent("imgView", $img_src);
                $body->setContent("demo_img", $img_src);
            } else {
                $body->setContent("imgView", ""); 
                $body->setContent("demo_img", "");
                error_log("Immagini non trovate per il subproduct_id: " . $subproduct_id);
            }

            // Caricamento dei prodotti correlati
            $related_products_query = $mysqli->query("SELECT id, title FROM products
                                                     WHERE categories_id = {$result['categories_id']}
                                                     AND id != {$result['id']}
                                                     ORDER BY CASE WHEN title LIKE '{$result['title']}' THEN 1 ELSE 2 END, title ASC
                                                     LIMIT 4");

            if ($related_products_query) {
                $related_products_data = [];
                while ($related_product = $related_products_query->fetch_assoc()) {
                    $related_products_data[] = [
                        'related_product_id' => $related_product['id'],
                        'related_product_title' => $related_product['title']
                    ];
                }
                $body->setContent("related_products", $related_products_data);
            } else {
                $body->setContent("related_products", []);
            }
        } else {
            // Gestione caso in cui non ci sono risultati per la query del prodotto
            error_log("Nessun risultato trovato per l'ID del prodotto: " . $product_id);
        }
    } else {
        // Gestione caso in cui non ci sono risultati per la query del sottoprodotto
        error_log("Nessun risultato trovato per l'ID del sottoprodotto: " . $subproduct_id);
    }
} else {
    // Gestione caso in cui manca l'ID del sottoprodotto nell'URL
    header('location: /MotorShop/product-list.php');
    exit();
}

$main->setContent('dynamic', $body->get()); 
$main->close();

} else {
    header("Location: /MotorShop/login.php");
}

?>