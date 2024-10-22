<?php
session_start();
require "include/dbms.inc.php";
require "include/auth.inc.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $subproductId = intval($_POST['id']);

        // Inizio transazione
        $mysqli->begin_transaction();

        try {
            // Ottieni il products_id dal sub_products
            $productIdStmt = $mysqli->prepare("SELECT products_id FROM sub_products WHERE id = ?");
            if ($productIdStmt === false) {
                throw new Exception("Errore nella preparazione della query: " . $mysqli->error);
            }

            $productIdStmt->bind_param("i", $subproductId);
            $productIdStmt->execute();
            $productIdStmt->bind_result($productId);
            $productIdStmt->fetch();
            $productIdStmt->close();

            if (empty($productId)) {
                throw new Exception("ID del prodotto non trovato.");
            }

            $stmt = $mysqli->prepare("DELETE FROM cart WHERE subproduct_id = ?");
            $stmt->bind_param("i", $subproductId);
            $stmt->execute();
            // Elimina dalla tabella sub_products
            $deleteSubProducts = $mysqli->prepare("DELETE FROM sub_products WHERE id = ?");
            if ($deleteSubProducts === false) {
                throw new Exception("Errore nella preparazione della query: " . $mysqli->error);
            }
            $deleteSubProducts->bind_param("i", $subproductId);
            $deleteSubProducts->execute();
            $deleteSubProducts->close();

            // Elimina dalla tabella images
            $deleteImages = $mysqli->prepare("DELETE FROM images WHERE sub_products_id = ?");
            if ($deleteImages === false) {
                throw new Exception("Errore nella preparazione della query: " . $mysqli->error);
            }
            $deleteImages->bind_param("i", $subproductId);
            $deleteImages->execute();
            $deleteImages->close();

            // Commit transazione
            $mysqli->commit();

            // Redirect alla lista dei prodotti con un messaggio di successo
            $_SESSION['message'] = "Sottoprodotto eliminato con successo.";
            header('Location: /MotorShop/subproduct-list.php?id=' . $productId); // Redirect alla lista dei prodotti
            exit();
    } catch (Exception $e) {
            // Rollback in caso di errore
            $mysqli->rollback();

            // Redirect alla lista dei prodotti con un messaggio di errore
            $_SESSION['error'] = "Errore durante l'eliminazione del sottoprodotto: " . $e->getMessage();
            header('Location: /MotorShop/product-list.php'); // Redirect alla lista dei prodotti
            exit();
        } 
    } else {
        // Redirect alla lista dei sottoprodotti con un messaggio di errore
        $_SESSION['error'] = "ID sottoprodotto non valido.";
        header('Location: /MotorShop/product-list.php'); // Redirect alla lista dei prodotti
        exit();
    }
} else {
    // Redirect alla lista dei prodotti se il metodo non è POST
    header('Location: /MotorShop/product-list.php'); // Redirect alla lista dei prodotti
    exit();
}
?>