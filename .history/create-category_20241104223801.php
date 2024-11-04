<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    $main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
    $body = new Template("skins/multikart_all_in_one/back-end/category.html");

    // lista categorie
    $categories = $mysqli->query("SELECT id, name FROM categories ORDER BY name ASC");
    if ($categories) {
        
    while ($category = $categories->fetch_assoc()) {
        // Conta il numero di prodotti per questa categoria
        $productCountResult = $mysqli->query("SELECT COUNT(*) as product_count FROM products WHERE categories_id = {$category['id']}");
        $productCount = $productCountResult->fetch_assoc()['product_count'];
        // Conta il numero di sottocategorie per questa categoria
        $subcategoryCountResult = $mysqli->query("SELECT COUNT(*) as subcategory_count FROM subcategories WHERE categories_id = {$category['id']}");
        $subcategoryCount = $subcategoryCountResult->fetch_assoc()['subcategory_count'];
        // Imposta i valori nel template per ciascuna categoria
        $body->setContent('category', $category['name']);
        $body->setContent('product', $productCount);
        $body->setContent('sub-category', "<a href='create-subcategory.php?categories_id={$category['id']}'>{$subcategoryCount}</a>");
        $body->setContent('id', $category['id']);
        $body->setContent('delete-url', "/MotorShop/create-category.php?elimina={$category['id']}");
    }
}

    // Elimina categoria selezionata
    if (isset($_GET['elimina']) && is_numeric($_GET['elimina'])) {
        $category_id = intval($_GET['elimina']);

        $deleteCategoryQuery = "DELETE FROM categories WHERE id = $category_id";
        if ($mysqli->query($deleteCategoryQuery)) {
            echo "Categoria eliminata con successo.";
            header('Location: /MotorShop/create-category.php');
            exit;
        } else {
            echo "Errore durante l'eliminazione della categoria: " . $mysqli->error;
        }
    }

    // Inserimento nuova categoria
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['category'])) {
        $newCategoryName = $mysqli->real_escape_string($_POST['category']);
        
        // Controlla se esiste già una categoria con lo stesso nome
        $checkCategoryQuery = "SELECT id FROM categories WHERE name = '$newCategoryName'";
        $checkCategoryResult = $mysqli->query($checkCategoryQuery);

        if ($checkCategoryResult->num_rows > 0) {
            echo "Errore: esiste già una categoria con lo stesso nome.";
        } else {
            $insertCategoryQuery = "INSERT INTO categories (name) VALUES ('$newCategoryName')";
            if ($mysqli->query($insertCategoryQuery)) {
                echo "Categoria inserita con successo.";
                header('Location: /MotorShop/create-category.php');
                exit;
            } else {
                echo "Errore durante l'inserimento della categoria: " . $mysqli->error;
            }
        }
    }

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent('body', $body->get());
$main->close();

?>