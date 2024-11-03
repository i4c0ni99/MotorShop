<?php

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php";
include "include/utils/priceFormatter.php";

if (isset($_SESSION['user']['email'])) {
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
} else {
    header("Location: /MotorShop/login.php");
}


function moveProductToCart($subproductId)
{
    global $mysqli;

    if (isset($_SESSION['user'])) {
        $userEmail = $_SESSION['user']['email'];

        // Verifica se il sottoprodotto è presente nella wishlist dell'utente
        $checkQuery = "SELECT subproduct_id FROM wishlist WHERE subproduct_id = ? AND user_email = ?";
        if ($stmt = $mysqli->prepare($checkQuery)) {
            $stmt->bind_param("is", $subproductId, $userEmail);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Procedi con l'inserimento nel carrello
                $insertQuery = "INSERT INTO cart (subproduct_id, quantity, user_email) VALUES (?, 1, ?)";
                if ($insertStmt = $mysqli->prepare($insertQuery)) {
                    $insertStmt->bind_param("is", $subproductId, $userEmail);
                    if ($insertStmt->execute()) {
                        // Se l'inserimento nel carrello è avvenuto con successo, elimina dalla wishlist
                        $deleteQuery = "DELETE FROM wishlist WHERE subproduct_id = ? AND user_email = ?";
                        if ($deleteStmt = $mysqli->prepare($deleteQuery)) {
                            $deleteStmt->bind_param("is", $subproductId, $userEmail);
                            if ($deleteStmt->execute()) {
                                echo "Prodotto spostato dalla wishlist al carrello con successo!";
                            } else {
                                echo "Errore durante l'eliminazione del prodotto dalla wishlist.";
                            }
                            $deleteStmt->close();
                        } else {
                            echo "Errore durante la preparazione della query di eliminazione: " . $mysqli->error;
                        }
                    } else {
                        echo "Errore durante l'inserimento del prodotto nel carrello: " . $insertStmt->error;
                    }
                    $insertStmt->close();
                } else {
                    echo "Errore durante la preparazione della query di inserimento: " . $mysqli->error;
                }
            } else {
                echo "Il sottoprodotto non è presente nella wishlist.";
            }
            $stmt->close();
        } else {
            echo "Errore durante la preparazione della query di verifica: " . $mysqli->error;
        }
    } else {
        echo "Utente non autenticato.";
    }
}

    // Gestione dell'aggiunta alla wishlist
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist'])) {
        $userEmail = $_SESSION['user']['email'];
        $subproductId = (int) $_POST['wishlist'];

        $query = "INSERT INTO wishlist (subproduct_id, user_email) VALUES (?, ?)";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("is", $subproductId, $userEmail);
            if ($stmt->execute()) {
                echo "Prodotto aggiunto alla wishlist con successo!";
                header("Location: /MotorShop/wishlist.php");
                exit;
            } else {
                echo "Errore durante l'aggiunta del prodotto alla wishlist.";
                error_log("Execute statement failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            echo "Errore durante l'aggiunta del prodotto alla wishlist.";
            error_log("Prepare statement failed: " . $mysqli->error);
        }
        exit; 
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_cart']) && isset($_POST['id'])) {
        $subproductId = (int) $_POST['id'];

        $query = "SELECT availability FROM sub_products WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $subproductId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Verifica disponibilità
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['availability'] == 1) {
                // Procedi con lo spostamento nel carrello
                moveProductToCart($subproductId);
                header("Location: /MotorShop/cart.php");
                exit;
            } else {
                echo "Prodotto non disponibile.";
            }
        } else {
            echo "Prodotto non trovato.";
        }
    }    

// Gestione della rimozione dalla wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && isset($_POST['id'])) {
    $userEmail = $_SESSION['user']['email'];
    $subproductId = (int) $_POST['id'];

    $query = "DELETE FROM wishlist WHERE subproduct_id = ? AND user_email = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("is", $subproductId, $userEmail);
        if ($stmt->execute()) {
            echo "Prodotto rimosso dalla wishlist con successo!";
            header("Location: /MotorShop/wishlist.php");
            exit;
        } else {
            echo "Errore durante la rimozione del prodotto dalla wishlist.";
            error_log("Execute statement failed: " . $stmt->error);
        }
        $stmt->close();
    } else {
        echo "Errore durante la rimozione del prodotto dalla wishlist.";
        error_log("Prepare statement failed: " . $mysqli->error);
    }
    exit; 
}

// Carica item wishlist utente
$userEmail = $_SESSION['user']['email'];
$query = "SELECT w.subproduct_id
FROM wishlist w
WHERE w.user_email = ?";
$stmt = $mysqli->prepare($query);
if ($stmt) {
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se è vuota
    if ($result->num_rows === 0) {
        $body = new Template("skins/motor-html-package/motor/wishlist-empty.html");
    } else {
        $body = new Template("skins/motor-html-package/motor/wishlist.html");
    }

    while ($wishlistItem = $result->fetch_assoc()) {
        $subproductId = $wishlistItem['subproduct_id'];

        // Prendi i dati da sub_products
        $query = "SELECT sp.id as subproduct_id, sp.products_id, sp.price, sp.availability, sp.color, sp.size, i.imgsrc
FROM sub_products sp
LEFT JOIN images i ON sp.id = i.sub_products_id
WHERE sp.id = ? AND sp.availability = 1"; 
        $stmt_subproduct = $mysqli->prepare($query);
        if ($stmt_subproduct) {
            $stmt_subproduct->bind_param("i", $subproductId);
            $stmt_subproduct->execute();
            $subProductResult = $stmt_subproduct->get_result();

            if ($subProductData = $subProductResult->fetch_assoc()) {
                $subProductId = $subProductData['subproduct_id'];
                $productsId = $subProductData['products_id'];
                $color = $subProductData['color'];
                $size = $subProductData['size'];

                // Ottieni dati dalla tabella products
                $query = "SELECT title FROM products WHERE id = ?";
                $stmt_product = $mysqli->prepare($query);
                if ($stmt_product) {
                    $stmt_product->bind_param("i", $productsId);
                    $stmt_product->execute();
                    $productResult = $stmt_product->get_result();

                    if ($productData = $productResult->fetch_assoc()) {
                        $title = $productData['title'];
                        $price = formatPrice($subProductData['price']);
                        $availability = $subProductData['availability'] == 1 ? "Disponibile" : "Non disponibile";
                        $imgsrc = $subProductData['imgsrc'];
                        
                        $body->setContent("title", $title);
                        $body->setContent("color", $color);
                        $body->setContent("size", $size);
                        $body->setContent("price", $price);
                        $body->setContent("availability", $availability);
                        $body->setContent("imgView", $imgsrc);
                        $body->setContent("img", $imgsrc);
                        $body->setContent("product_id", $productsId); 
                        $body->setContent("id", $subProductId); 
                    } else {
                        error_log("Fetch product data failed: " . $stmt_product->error);
                    }
                    $stmt_product->close();
                } else {
                    error_log("Prepare statement failed: " . $mysqli->error);
                }
            }
            $stmt_subproduct->close();
        } else {
            error_log("Prepare statement failed: " . $mysqli->error);
        }
    }
    $stmt->close();
} else {
    error_log("Prepare statement failed: " . $mysqli->error);
}

$main->setContent("dynamic", $body->get());
$main->close();