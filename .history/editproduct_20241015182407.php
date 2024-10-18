<?php
session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
include "include/auth.inc.php";

if (isset($_SESSION['user'])) {

    $main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
    $body = new Template("skins/multikart_all_in_one/back-end/edit-product.html");

    $main->setContent('name', $_SESSION['user']['name']);

    // Verifica che products_id sia definito in GET
    if (!isset($_GET['id'])) {
        die("ID del prodotto non specificato.");
    }

    // Controlla se è stato inviato il form di modifica
    if (isset($_POST['edit'])) {

        $productId = intval($_GET['id']);
        $updates = [];

        // Escape dei dati POST e costruzione della query di aggiornamento
        if (!empty($_POST['title'])) {
            $title = $mysqli->real_escape_string($_POST['title']);
            $updates[] = "title = '$title'";
        }
        if (!empty($_POST['description'])) {
            $description = $mysqli->real_escape_string($_POST['description']);
            $updates[] = "description = '$description'";
        }
        if (!empty($_POST['details'])) {
            $details = $mysqli->real_escape_string($_POST['details']);
            $updates[] = "specification = '$details'";
        }
        

        // Verifica disponibilità prodotto e sottoprodotti
        $availability = isset($_POST['availability']) ? intval($_POST['availability']) : 0;

        // Controllo se availability è 1 o il prodotto è già disponibile
        if ($availability == 1) {
            // Controlla se ci sono sottoprodotti disponibili
            $check_subproduct_query = "
                SELECT COUNT(*) as available_subproducts 
                FROM sub_products 
                WHERE products_id = '$productId' 
                AND quantity > 0 
                AND availability = 1";
            
            $result = $mysqli->query($check_subproduct_query);
            $row = $result->fetch_assoc();

            // Se non ci sono sottoprodotti disponibili, forza availability a 0
            if ($row['available_subproducts'] == 0) {
                $availability = 0;
            }
        }

        // Aggiungi availability agli aggiornamenti
        $updates[] = "availability = '$availability'";

        // Se ci sono campi da aggiornare, esegui la query di aggiornamento
        if (!empty($updates)) {
            $update_query = "UPDATE products SET " . implode(", ", $updates) . " WHERE id = '$productId'";

            if ($mysqli->query($update_query)) {
                header('Location: /MotorShop/product-list.php?id=' . $productId);
                exit;
            } else {
                echo "Errore nell'esecuzione della query di aggiornamento: " . $mysqli->error;
            }
        } else {
            echo "Nessun dato da aggiornare.";
        }
    }

    // Carica i dettagli del prodotto per il form di modifica
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $productId = intval($_GET['id']);

        $product_query = "SELECT * FROM products WHERE id = '$productId'";
        $product_result = $mysqli->query($product_query);

        if ($product_result->num_rows > 0) {
            $product = $product_result->fetch_assoc();

            $category_query = "SELECT name FROM categories WHERE id = '".$product['categories_id']."'";
            $category_result = $mysqli->query($category_query);
            $category_name = $category_result->fetch_assoc()['name'];

            $body->setContent('id', $productId);
            $body->setContent('code', $product['code']);
            $body->setContent('title', $product['title']);

            // Forza la checkbox availability a "non selezionato" se il prodotto non ha sottoprodotti disponibili
            $availability_checked = ($product['availability'] == 1) ? 'checked' : '';
            $body->setContent('src', 
                '<input name="availability" class="checkbox_animated check-it" type="checkbox" value="1" ' . $availability_checked . '>'
            );

            $body->setContent('description', $product['description']);
            $body->setContent('details', $product['specification']);
            $body->setContent('category', $category_name);
        } else {
            echo "Nessun prodotto trovato con ID: " . $productId;
        }
    } else {
        echo "ID prodotto non valido.";
    }
} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();
?>