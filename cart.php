<?php

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php";
include "include/utils/priceFormatter.php";

if (isset($_SESSION['user']['groups'])) {
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
} else {
    header("Location: /MotorShop/login.php");
}

if (isset($_SESSION['user']['email'])) {

        $userEmail = $_SESSION['user']['email'];
    
        // controlla se il carrello è vuoto
        $checkCartQuery = "SELECT COUNT(*) as item_count FROM cart WHERE user_email = ?";
        $stmt_check_cart = $mysqli->prepare($checkCartQuery);
        $stmt_check_cart->bind_param("s", $userEmail);
        $stmt_check_cart->execute();
        $result_check_cart = $stmt_check_cart->get_result();
        $cartData = $result_check_cart->fetch_assoc();
       
        if ($cartData['item_count'] == 0) {
            $body = new Template("skins/motor-html-package/motor/cart-empty.html");
        } else {
            $body = new Template("skins/motor-html-package/motor/cart.html");
        }
    
        // aggiungi prodotto al carrello
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isset($_POST['quantity'])) {
            
            $subproductId = (int) $_POST['add_to_cart'];
            
            // controlla se il prodotto è già nel carrello
            $checkQuery = "SELECT * FROM cart WHERE subproduct_id = ? AND user_email = ?";
            $stmt_check = $mysqli->prepare($checkQuery);
            $stmt_check->bind_param("is", $subproductId, $userEmail);
            $stmt_check->execute();
            $stmt_result = $stmt_check->get_result()->fetch_assoc();
            
            if ($stmt_result) {
                // Prodotto già presente nel carrello -> aggiorna la quantità
                $updateQuery = "UPDATE cart SET quantity = ? WHERE subproduct_id = ? AND user_email = ?";
                $stmt_update = $mysqli->prepare($updateQuery);
                if ($stmt_update) {
                    $quantity = $_POST['quantity'];
                    if ($quantity == $stmt_result['quantity']) {
                        echo "<script>console.log('Messaggio di debug nel browser');</script>";
                        $quantityZero = 0;
                        $stmt_update->bind_param("iis", $stmt_result['quantity'], $subproductId, $userEmail);
                    } else { 
                        echo "<script>console.log('entrato');</script>";
                        $stmt_update->bind_param("iis", $quantity, $subproductId, $userEmail);
                    }   
                    if ($stmt_update->execute()) {
                        echo json_encode(['success' => 'success']);
                    } else {
                        error_log("Execute statement failed: " . $stmt_update->error);
                    }
                    $stmt_update->close();
                } else {
                    error_log("Prepare statement failed: " . $mysqli->error);
                }
            } else {
                // prodotto non presente nel carrello
                $insertQuery = "INSERT INTO cart (subproduct_id, quantity, user_email) VALUES (?, ?, ?)";
                $stmt_insert = $mysqli->prepare($insertQuery);
                if ($stmt_insert) {
                    $stmt_insert->bind_param("iis", $subproductId, $_POST['quantity'], $userEmail);
                    if ($stmt_insert->execute()) {
                        echo "Prodotto aggiunto al carrello con successo!";
                        echo json_encode(['success' => 'success']);
                        exit;
                    } else {
                        echo "Errore durante l'aggiunta del prodotto al carrello.";
                        error_log("Execute statement failed: " . $stmt_insert->error);
                    }
                    $stmt_insert->close();
                } else {
                    echo "Errore durante l'aggiunta del prodotto al carrello.";
                    error_log("Prepare statement failed: " . $mysqli->error);
                }
            }
            
            exit;
        }

    // rimuovi prodotto dal carrello
    if (isset($_POST['delete']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userEmail = $_SESSION['user']['email'];
        $subproductId = (int) $_POST['delete'];

        $query = "DELETE FROM cart WHERE subproduct_id = ? AND user_email = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("is", $subproductId, $userEmail);
            if ($stmt->execute()) {
                echo json_encode(['success' => 'success']);
            } else {
                echo json_encode(['success' => false]);
                echo "Errore durante la rimozione del prodotto dal carrello.";
                error_log("Execute statement failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false]);
            echo "Errore durante la rimozione del prodotto dal carrello.";
            error_log("Prepare statement failed: " . $mysqli->error);
        }
    }

    // aggiorna le quantità dei prodotti nel carrello
    if (isset($_POST['update_quantities']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userEmail = $_SESSION['user']['email'];
        $quantities = $_POST['update_quantities']; // Array con id prodotto e quantità
        $subproductId = $_POST['id'];
        if($quantities == 0){ 
            $mysqli->query("DELETE FROM cart WHERE subproduct_id = ".$subproductId." AND user_email = '".$userEmail."'");   
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        $updateQuery = "UPDATE cart SET quantity = ? WHERE subproduct_id = ? AND user_email = ?";
        $stmt_update = $mysqli->prepare($updateQuery);
        if ($stmt_update) {
            $stmt_update->bind_param("iis", $quantities, $subproductId, $userEmail);
            if ($stmt_update->execute()) {
                echo "Quantità del prodotto nel carrello aggiornata con successo!";
                echo json_encode(['success' => 'success']);
            } else {
                echo "Errore durante l'aggiornamento della quantità del prodotto nel carrello.";
                error_log("Execute statement failed: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            echo "Errore durante l'aggiornamento della quantità del prodotto nel carrello.";
            error_log("Prepare statement failed: " . $mysqli->error);
        }
        
        // refresh
        header("Location: /MotorShop/cart.php");
        exit;
    }

    // mostra il carrello dell'utente
    $userEmail = $_SESSION['user']['email'];
    $query = "SELECT c.subproduct_id, c.quantity, sp.products_id, sp.price,sp.quantity as prod_quantity, sp.availability, sp.color, sp.size, i.imgsrc,i.id FROM cart c JOIN sub_products sp ON c.subproduct_id = sp.id INNER JOIN images i ON sp.products_id = i.product_id WHERE c.user_email = ? GROUP BY sp.products_id";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        // controlla se ci sono articoli nel carrello
        $cartItems = [];
        $checkoutDisabled = true; // se è vuoto
        
        while ($cartItem = $result->fetch_assoc()) {
            $checkoutDisabled = false; 
            $subproductId = $cartItem['subproduct_id'];
            
            $productQuery = "SELECT title FROM products WHERE id = ?";
            $stmt_product = $mysqli->prepare($productQuery);
            if ($stmt_product) {
                $stmt_product->bind_param("i", $cartItem['products_id']);
                $stmt_product->execute();
                $productResult = $stmt_product->get_result();

                if ($productData = $productResult->fetch_assoc()) {
                    // dati del prodotto
                    $title = $productData['title'];
                    $quantity = $cartItem['quantity'];
                    $price = formatPrice($cartItem['price']);
                    echo '<script>console.log('.$cartItem['quantity'].');</script>';
                
                    $quantities = $cartItem['prod_quantity'];
                    $size = $cartItem['size'];
                    $color = $cartItem['color'];
                    $availability = $cartItem['availability'] == 1 ? "Disponibile" : "Non disponibile";
                    $imgsrc = $cartItem['imgsrc'];
                    
                    $cartItems[] = [
                        "title" => $title,
                        "quantity" => $quantity,
                        "size" => $size,
                        "color" => $color,
                        "price" => $price,
                        "availability" => $availability,
                        "imgView" => $imgsrc,
                        "img" => $imgsrc,
                        "product_id" => $cartItem['products_id'],
                        "id" => $subproductId,
                        "prod_quantity" => $cartItem['prod_quantity']
                    ];
                } else {
                    error_log("Fetch product data failed: " . $stmt_product->error);
                }
                $stmt_product->close();
            } else {
                error_log("Prepare statement failed: " . $mysqli->error);
            }
        }
        $stmt->close();
        
        foreach ($cartItems as $cartItem) {

            $body->setContent("checkout_disabled", $checkoutDisabled);
            $body->setContent("title", $cartItem['title']);
            $body->setContent("quantity", $cartItem['quantity']);
            $body->setContent("size", $cartItem['size']);
            $body->setContent("color", $cartItem['color']);
            $body->setContent("price", $cartItem['price']);
            $body->setContent("availability", $cartItem['availability']);
            $body->setContent("imgView", $cartItem['imgView']);
            $body->setContent("img", $cartItem['img']);
            $body->setContent("product_id", $cartItem['product_id']);
            $body->setContent("id", $cartItem['id']);
            $body->setContent("quantities",$cartItem['prod_quantity']);
            
        }
    } else {
        error_log("Prepare statement failed: " . $mysqli->error);
    }
}

$main->setContent("dynamic", $body->get());
$main->close();
?>