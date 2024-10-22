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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && isset($_POST['quantity'])) {

        $userEmail = $_SESSION['user']['email'];
        $subproductId = (int) $_POST['add_to_cart'];
        
        // Controlla se il prodotto è già nel carrello
        $checkQuery = "SELECT * FROM cart  WHERE  subproduct_id = ".$subproductId." AND user_email = '".$userEmail."'";
        $stmt_check = $mysqli->query($checkQuery);
        $stmt_result = $stmt_check->fetch_assoc();            
            if ($stmt_result) {
              
                $updateQuery = "UPDATE cart SET quantity = ? WHERE subproduct_id = ? AND user_email = ?";
                $stmt_update = $mysqli->prepare($updateQuery);
                if ($stmt_update) {
                    $quantity = $_POST['quantity'];
                    if( $quantity == $stmt_result['quantity'] ){
                        echo "<script>console.log('Messaggio di debug nel browser');</script>";
                        $quantityZero = 0;
                        $stmt_update->bind_param("iis", $stmt_result['quantity'],$subproductId, $userEmail);
                    }
                    else{ 
                        echo "<script> console.log('entrato')</script>";
                        $stmt_update->bind_param("iis",$quantity ,$subproductId, $userEmail);
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
                // Prodotto non presente nel carrello, aggiungilo
                
                $insertQuery = "INSERT INTO cart (subproduct_id, quantity, user_email) VALUES (?, ?, ?)";
                $stmt_insert = $mysqli->prepare($insertQuery);
                if ($stmt_insert) {
                    $stmt_insert->bind_param("iis", $subproductId,$_POST['quantity'], $userEmail);
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
            
      
        exit; // Termina lo script dopo l'aggiunta al carrello
    }

    // Funzione per rimuovere un prodotto dal carrello
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

    // Funzione per aggiornare le quantità dei prodotti nel carrello
    if (isset($_POST['update_quantities']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $userEmail = $_SESSION['user']['email'];
        $quantities = $_POST['update_quantities']; // Array associativo con id prodotto e quantità
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
            
        
        
        // Ricarica la pagina del carrello dopo l'aggiornamento
        header("Location: /MotorShop/cart.php");
        exit;
    }

    // Funzione per mostrare il carrello dell'utente
    $userEmail = $_SESSION['user']['email'];
    $query = "SELECT c.subproduct_id, c.quantity, sp.products_id, sp.price,sp.quantity as prod_quantity, sp.availability, sp.color, sp.size, i.imgsrc
          FROM cart c
          INNER JOIN sub_products sp ON c.subproduct_id = sp.id
          LEFT JOIN images i ON sp.id = i.sub_products_id
          WHERE c.user_email = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        // Controlla se ci sono articoli nel carrello
$cartItems = [];
$checkoutDisabled = true; // Imposta su true per disabilitare il pulsante di checkout










        
        while ($cartItem = $result->fetch_assoc()) {
            $checkoutDisabled = false; 
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
                    echo '<script>console.log('.$cartItem['quantity'].');</script>';
                
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
                    $quantities = $cartItem['prod_quantity'];
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
            $body->setContent("quantities",$cartItem['prod_quantity']);
            
        }
    } else {
        error_log("Prepare statement failed: " . $mysqli->error);
    }
}

$main->setContent("dynamic", $body->get());
$main->close();
?>