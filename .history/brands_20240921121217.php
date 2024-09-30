<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";

if (isset($_SESSION['user'])) {

    $main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
    $body = new Template("skins/multikart_all_in_one/back-end/brands.html");

    // Carica lista marche
$brands = $mysqli->query("SELECT * FROM brands ORDER BY name ASC");
if ($brands) {
    while ($brand = $categories->fetch_assoc()) {
        $body->setContent('id', $brand['id']);
        $body->setContent('brand', $brand['name']);
    }
}

    // Eliminazione marca selezionata
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $category_id = intval($_GET['delete']);

        $deleteCategoryQuery = "DELETE FROM brands WHERE id = $category_id";
        if ($mysqli->query($deleteCategoryQuery)) {
            echo "Marca eliminata con successo.";
            // Redirect dopo l'eliminazione
            header('Location: /MotorShop/brands.php');
            exit;
        } else {
            echo "Errore durante l'eliminazione della marca: " . $mysqli->error;
        }
    }

    // Inserimento nuova marca
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['brand'])) {
        $newBrandName = $mysqli->real_escape_string($_POST['brand']);
        
        // Controlla se esiste già una categoria con lo stesso nome
        $checkCategoryQuery = "SELECT id FROM categories WHERE name = '$newCategoryName'";
        $checkCategoryResult = $mysqli->query($checkCategoryQuery);

        if ($checkCategoryResult->num_rows > 0) {
            echo "Errore: esiste già una categoria con lo stesso nome.";
        } else {
            $insertBrandQuery = "INSERT INTO brands (name) VALUES ('$newBrandName')";
            if ($mysqli->query($insertBrandQuery)) {
                echo "Marca inserita con successo.";
                // Redirect dopo l'inserimento
                header('Location: /MotorShop/brands.php');
                exit;
            } else {
                echo "Errore durante l'inserimento della marca: " . $mysqli->error;
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