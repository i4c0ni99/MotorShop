<?php 
session_start();
require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php";
include "include/utils/priceFormatter.php";

// Verifica se l'utente è loggato
if (isset($_SESSION['user']['groups'])) {
   $main = new Template("skins/motor-html-package/motor/frame-customer.html");
} else {
   header("Location: /MotorShop/login.php");
}

$body = new Template("skins/motor-html-package/motor/cart.html");

// Verifica se l'utente è loggato
if (isset($_SESSION['user'])) {
    // Connessione al database già inclusa in "dbms.inc.php"

    // Funzione per aggiungere un prodotto al carrello
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isset($_GET['id'])) {
        $userEmail = $_SESSION['user']['email'];
        $subproductId = (int) $_GET['id'];

        // Controlla se il prodotto è già nel carrello
        $checkQuery = "SELECT * FROM cart WHERE subproduct_id = ? AND user_email = ?";
        $stmt_check = $mysqli->prepare($checkQuery);
        if ($stmt_check) {
            $stmt_check->bind_param("is", $subproductId, $userEmail);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                // Prodotto già presente nel carrello, aggiorna la quantità
                $updateQuery = "UPDATE cart SET quantity = quantity + 1 WHERE subproduct_id = ? AND user_email = ?";
                $stmt_update = $mysqli->prepare($updateQuery);
                if ($stmt_update) {
                    $stmt_update->bind_param("is", $subproductId, $userEmail);
                    if ($stmt_update->execute()) {
                        echo "Quantità del prodotto nel carrello aggiornata con successo!";
                    } else {
                        echo "Errore durante l'aggiornamento della quantità del prodotto nel carrello.";
                        error_log("Execute statement failed: " . $stmt_update->error);
                    }
                    $stmt_update->close();
                } else {
                    echo "Errore durante l'aggiornamento della quantità del prodotto nel carrello.";
                    error_log("Prepare statement failed: " . $mysqli->error);
                }
            } else {
                // Prodotto non presente nel carrello, aggiungilo
                $insertQuery = "INSERT INTO cart (subproduct_id, quantity, user_email) VALUES (?, 1, ?)";
                $stmt_insert = $mysqli->prepare($insertQuery);
                if ($stmt_insert) {
                    $stmt_insert->bind_param("is", $subproductId, $userEmail);
                    if ($stmt_insert->execute()) {
                        echo "Prodotto aggiunto al carrello con successo!";
                        header("Location: /MotorShop/cart.php");
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
            $stmt_check->close();
        } else {
            echo "Errore durante la verifica del prodotto nel carrello.";
            error_log("Prepare statement failed: " . $mysqli->error);
        }
        exit; // Termina lo script dopo l'aggiunta al carrello
    }
    
    // Funzione per rimuovere un prodotto dal carrello
if (isset($_POST['delete']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userEmail = $_SESSION['user']['email'];
    $subproductId = (int) $_POST['id'];
    $query = "DELETE FROM cart WHERE subproduct_id = ? AND user_email = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
    $stmt->bind_param("is", $subproductId, $userEmail);
    if ($stmt->execute()) {
    echo "Prodotto rimosso dal carrello con successo!";
    header("Location: /MotorShop/cart.php");
    exit;
    } else {
    echo "Errore durante la rimozione del prodotto dal carrello.";
    error_log("Execute statement failed: " . $stmt->error);
    }
    $stmt->close();
    } else {
    echo "Errore durante la rimozione del prodotto dal carrello.";
    error_log("Prepare statement failed: " . $mysqli->error);
    }
    }

    // Funzione per aggiornare le quantità dei prodotti nel carrello
    if (isset($_POST['update_quantities']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userEmail = $_SESSION['user']['email'];
        $quantities = $_POST['quantities']; // Array associativo con id prodotto e quantità
        
        foreach ($quantities as $subproductId => $newQuantity) {
            $subproductId = (int) $subproductId;
            $newQuantity = (int) $newQuantity;
            
            // Ottieni la quantità attuale dal database
            $query = "SELECT quantity FROM cart WHERE subproduct_id = ? AND user_email = ?";
            $stmt = $mysqli->prepare($query);
            if ($stmt) {
                $stmt->bind_param("is", $subproductId, $userEmail);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($cartItem = $result->fetch_assoc()) {
                    $currentQuantity = (int) $cartItem['quantity'];
                    
                    // Aggiorna solo se la nuova quantità è diversa dalla quantità attuale
                    if ($currentQuantity !== $newQuantity) {
                        $updateQuery = "UPDATE cart SET quantity = ? WHERE subproduct_id = ? AND user_email = ?";
                        $stmt_update = $mysqli->prepare($updateQuery);
                        if ($stmt_update) {
                            $stmt_update->bind_param("iis", $newQuantity, $subproductId, $userEmail);
                            if ($stmt_update->execute()) {
                                echo "Quantità del prodotto nel carrello aggiornata con successo!";
                            } else {
                                echo "Errore durante l'aggiornamento della quantità del prodotto nel carrello.";
                                error_log("Execute statement failed: " . $stmt_update->error);
                            }
                            $stmt_update->close();
                        } else {
                            echo "Errore durante l'aggiornamento della quantità del prodotto nel carrello.";
                            error_log("Prepare statement failed: " . $mysqli->error);
                        }
                    }
                } else {
                    echo "Prodotto non trovato nel carrello.";
                    error_log("Fetch cart data failed: " . $stmt->error);
                }
                $stmt->close();
            } else {
                echo "Errore durante il recupero della quantità del prodotto nel carrello.";
                error_log("Prepare statement failed: " . $mysqli->error);
            }
        }
        // Ricarica la pagina del carrello dopo l'aggiornamento
        header("Location: /MotorShop/cart.php");
        exit;
    }

// Funzione per mostrare il carrello dell'utente
$userEmail = $_SESSION['user']['email'];
$query = "SELECT c.subproduct_id, c.quantity, sp.products_id, sp.price, sp.availability, sp.color, sp.size, i.imgsrc
          FROM cart c
          INNER JOIN sub_products sp ON c.subproduct_id = sp.id
          LEFT JOIN images i ON sp.id = i.sub_products_id
          WHERE c.user_email = ?";
$stmt = $mysqli->prepare($query);
if ($stmt) {
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItems = []; // Array per memorizzare i dati del carrello

    while ($cartItem = $result->fetch_assoc()) {
        $subproductId = $cartItem['subproduct_id'];

        // Ottieni title dalla tabella products
        $productQuery = "SELECT title FROM products WHERE id = ?";
        $stmt_product = $mysqli->prepare($productQuery);
        if ($stmt_product) {
            $stmt_product->bind_param("i", $cartItem['products_id']);
            $stmt_product->execute();
            $productResult = $stmt_product->get_result();

            if ($productData = $productResult->fetch_assoc()) {
                $title = $productData['title'];
                // Altri dati del prodotto
                $quantity = $cartItem['quantity'];
                $price = formatPrice($cartItem['price']);
                
                // Ricevi e converti quantity
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Converti $price da stringa a float (se necessario)
//$price = floatval(str_replace(',', '.', $price));

// Verifica se $price è numericamente valido e quantity è un intero
//if (is_numeric($price) && is_int($quantity)) {
    // Calcola il prezzo totale per questo prodotto nel carrello
    //$totalPrice = $price * $quantity;
//} else {
    // Gestisci il caso in cui $price o $quantity non siano numerici
    //echo "Errore: Prezzo o quantity non sono valori numerici validi.";
//}
                
                $size = $cartItem['size'];
                $color = $cartItem['color'];
                $availability = $cartItem['availability'] == 1 ? "Disponibile" : "Non disponibile";
                $imgsrc = $cartItem['imgsrc'];

                // Aggiungi i dati del prodotto all'array
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

    // Imposta i contenuti nel template
    foreach ($cartItems as $cartItem) {
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
    }
} else {
    error_log("Prepare statement failed: " . $mysqli->error);
}
}

$main->setContent("dynamic", $body->get());
$main->close();
?>