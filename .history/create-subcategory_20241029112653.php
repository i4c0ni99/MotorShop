<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require_once "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/category-sub.html");

// Verifica se l'utente appartiene al gruppo 1
if ($_SESSION['user']['groups'] != '1') {
    header("Location: /MotorShop/login.php");
    exit();
}

// Verifica la presenza e la validità del parametro categories_id nella query string
if (!isset($_GET['categories_id']) || !is_numeric($_GET['categories_id'])) {
    echo "Parametro categories_id non valido.";
    exit;
}

// Ottieni l'ID della categoria dal parametro URL
$category_id = intval($_GET['categories_id']);

// Verifica che l'ID della categoria esista nella tabella categories
$check_category_query = "SELECT id FROM categories WHERE id = $category_id";
$check_category_result = $mysqli->query($check_category_query);

if ($check_category_result->num_rows == 0) {
    echo "ID categoria non valido.";
    exit;
}

// Ottieni le sottocategorie correlate all'ID della categoria
$categories = $mysqli->query("SELECT id, name FROM categories");

    // Carica lista categorie
    $subcategories = $mysqli->query("SELECT id, name FROM subcategories WHERE categories_id = $category_id ORDER BY name ASC");
    if ($subcategories) {
        while ($subcategory = $subcategories->fetch_assoc()) {
            // Conta il numero di prodotti per questa categoria
            $productCountResult = $mysqli->query("SELECT COUNT(*) as product_count FROM products WHERE subcategories_id = $category_id");
            $productCount = $productCountResult->fetch_assoc()['product_count'];
            // Imposta i valori nel template per ciascuna categoria
            $body->setContent('product', $productCount);
            $body->setContent('sub-category', "<a href='create-subcategory.php?categories_id={$category_id}'>{$subcategoryCount}</a>");
            $body->setContent('id', $category_id);
            $body->setContent('delete-url', "/MotorShop/create-category.php?elimina={$category_id}");
        }
    } else {
        $body->setContent('message', 'Nessuna sottocategoria trovata');
    }

// Se il form è stato inviato per salvare la sottocategoria
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_subcategory'])) {
    if (isset($_POST['category_id']) && isset($_POST['subcategory_name'])) {
        $category_id_post = $mysqli->real_escape_string($_POST['category_id']);
        $subcategory_name = $mysqli->real_escape_string($_POST['subcategory_name']);

        // Verifica che il nome della sottocategoria non esista già per questa categoria
        $check_query = "SELECT COUNT(*) as count FROM subcategories WHERE name = '$subcategory_name' AND categories_id = $category_id_post";
        $check_result = $mysqli->query($check_query);
        $count = $check_result->fetch_assoc()['count'];

        if ($count > 0) {
            echo "Una sottocategoria con questo nome esiste già per questa categoria.";
        } else {
            // Esegui l'inserimento nella tabella subcategories
            $insert_query = "INSERT INTO subcategories (name, categories_id) VALUES ('$subcategory_name', $category_id_post)";
            if ($mysqli->query($insert_query)) {
                echo "Inserimento sottocategoria avvenuto con successo";
                header("Location: /MotorShop/create-subcategory.php?categories_id=$category_id_post");
                exit;
            } else {
                echo "Errore durante l'inserimento della sottocategoria: " . $mysqli->error;
            }
        }
    } else {
        echo "Assicurati di selezionare una categoria e inserire un nome per la sottocategoria.";
    }
}

// Se è stato inviato il parametro elimina nella query string, elimina la sottocategoria corrispondente
if (isset($_GET['elimina']) && is_numeric($_GET['elimina'])) {
    $subcategory_id = intval($_GET['elimina']);
    
    $delete_query = "DELETE FROM subcategories WHERE id = $subcategory_id AND categories_id = $category_id";
    if ($mysqli->query($delete_query)) {
        echo "Sottocategoria eliminata con successo.";
        header("Location: /MotorShop/create-subcategory.php?categories_id=$category_id");
        exit;
    } else {
        echo "Errore durante l'eliminazione della sottocategoria: " . $mysqli->error;
    }
}

$main->setContent('body', $body->get());
$main->close();

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>