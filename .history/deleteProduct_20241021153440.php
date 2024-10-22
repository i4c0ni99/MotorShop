<?php
session_start();
require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $productId = intval($_POST['id']);

        $mysqli->begin_transaction();

        try {
            // Elimina dalla tabella sub_products
            $deleteSubProducts = $mysqli->prepare("DELETE FROM sub_products WHERE products_id = '$productId'");
            $deleteSubProducts->execute();

            // Elimina dalla tabella images
            $deleteImages = $mysqli->prepare("DELETE FROM images WHERE product_id = '$productId'");
            $deleteImages->execute();

            // Elimina dalla tabella products
            $deleteProduct = $mysqli->prepare("DELETE FROM products WHERE id = '$productId'");
            $deleteProduct->execute();
       
            $mysqli->commit();

            // Redirect alla lista dei prodotti con un messaggio di successo
            $_SESSION['message'] = "Prodotto eliminato con successo.";
            header('Location: /MotorShop/product-list.php');
            exit();
        } catch (Exception $e) {
            // In caso di errore
            $mysqli->rollback();
            
            $_SESSION['error'] = "Errore durante l'eliminazione del prodotto.";
            header('Location: /MotorShop/product-list.php');
            exit();
        }
    } else {
        // Redirect alla lista dei prodotti con un messaggio di errore
        $_SESSION['error'] = "ID prodotto non valido.";
        header('Location: /MotorShop/product-list.php');
        exit();
    }
} else {
    // Redirect alla lista dei prodotti se il metodo non è POST
    header('Location: /MotorShop/product-list.php');
    exit();
}

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>