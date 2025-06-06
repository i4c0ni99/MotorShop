<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
include "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {
    
    $main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
    $body = new Template("skins/multikart_all_in_one/back-end/edit-subproduct.html");

    $main->setContent('name', $_SESSION['user']['name']);

    // dettagli del sottoprodotto 
    if (isset($_GET['id'])) {
        $subproduct_query = "SELECT sp.*,sp.id as sub_id, p.id AS product_id 
                            FROM sub_products sp 
                            INNER JOIN products p ON sp.products_id = p.id 
                            WHERE sp.id = '" . $mysqli->real_escape_string($_GET['id']) . "'";
        $subproduct_result = $mysqli->query($subproduct_query);
        
        if ($subproduct_result && $subproduct_result->num_rows > 0) {
            $subproduct = $subproduct_result->fetch_assoc();
            $body->setContent('id', $_GET['id']);
            $body->setContent('color', $subproduct['color']);
            $body->setContent('price', $subproduct['price']);
            $body->setContent('src',$subproduct['availability'] == 1?'<input name="availability" class="checkbox_animated "
            type="checkbox" value="0" checked="">':
            '<input name="availability" class="checkbox_animated "
            type="checkbox" value="1">');
            $body->setContent('quantity', $subproduct['quantity']);
            
            $img_query = $mysqli->query("SELECT * FROM images where sub_products_id =".$subproduct['sub_id']);
            
            foreach($img_query as $img){
                
                $body->setContent('id_sub',$img['sub_products_id']);
                $body->setContent('img_id',$img['id']);
                $body->setContent('img',$img['imgsrc']);
            }
            $body->setContent('size', $subproduct['size']);
            
            // id del prodotto 
            $productId = $subproduct['product_id'];
            $body->setContent('productId', $productId); 
            
            $offer_query = "SELECT * FROM offers WHERE subproduct_id = '{$_GET['id']}'";
            $offer_result = $mysqli->query($offer_query);

            if ($offer_result && $offer_result->num_rows > 0) {
                $offer = $offer_result->fetch_assoc();
                $body->setContent('id_offerta', $offer['id']);
                $body->setContent('percentage', $offer['percentage']);
                $body->setContent('activation_date', $offer['activation_date']);
                $body->setContent('expiration_date', $offer['expiration_date']);
            }

        } else {
            $_SESSION['error'] = "Nessun sottoprodotto trovato con ID: " . $_GET['id'];
            // header('Location: /MotorShop/product-list.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "ID del sottoprodotto non specificato.";
        // header('Location: /MotorShop/product-list.php');
        exit;
    }
    
    if(!empty($_FILES['image']) && !empty($_POST['img_id'])){
        
        $fileTmpPath = $_FILES['image']['tmp_name'];
                    $data = file_get_contents($fileTmpPath);
                    echo "<script>console.log('".$_POST['img_id']."');</script>";
                    echo "<script>console.log('".$fileTmpPath."');</script>";
                    $data64 = base64_encode($data);
        $mysqli->query("UPDATE images SET imgsrc ='".$data64."' WHERE  id =".$_POST['img_id']);
        header('Location: /MotorShop/edit-subproduct.php?id=' . $_GET['id']);
        
    }

    if (isset($_POST['edit'])) {
        if (!isset($_GET['id'])) {
            die("ID del sottoprodotto non specificato.");
        }

        $subproduct_id = $mysqli->real_escape_string($_GET['id']);
        
        $update_query = "UPDATE sub_products SET ";

        $updates = [];
        $params = [];
        
        
        if (!empty($_POST['price'])) {
            $updates[] = "price = ?";
            $params[] = $_POST['price'];
        }
        if (!empty($_POST['quantity'])) {
            $updates[] = "quantity = ?";
            $params[] = $_POST['quantity'];
        }
        if (!empty($_POST['availability'])) {
        
            $availability = $mysqli->real_escape_string($_POST['availability']);
            echo "<script>console.log(".$_POST['availability'].")</script>";
            $updates[] = "availability = '$availability'";
           
        }
        if (empty($_POST['availability'])) {
            
            $availability = $mysqli->real_escape_string(0);
            echo "<script>console.log(".$_POST['availability'].")</script>";
            $updates[] = "availability = '$availability'";
           
        }
        if (!empty($_POST['size'])) {
            $updates[] = "size = ?";
            $params[] = $_POST['size'];
        }
        if (!empty($_POST['color'])) {
            $updates[] = "color = ?";
            $params[] = $_POST['color'];
        }
        
        $params[] = $subproduct_id;

        // Verifica se ci sono campi da aggiornare
        if (!empty($updates)) {
            $update_query .= implode(", ", $updates);
            $update_query .= " WHERE id = ?";
            
            $stmt = $mysqli->prepare($update_query);
            if ($stmt) {
                
                $types = str_repeat('s', count($params) - 1) . 'i';
                $stmt->bind_param($types, ...$params);

                // aggiornamento
                if ($stmt->execute()) {
                    header('Location: /MotorShop/subproduct-list.php?id=' . $productId);
                    exit;
                } else {
                    $_SESSION['error'] = "Errore nell'esecuzione della query di aggiornamento: " . $stmt->error;
                    exit;
                }
            } else {
                $_SESSION['error'] = "Errore nella preparazione della query: " . $mysqli->error;
                exit;
            }
        } else {
            $_SESSION['error'] = "Nessun dato da aggiornare.";
            exit;
        }
    }
    
    if (isset($_POST['delete_offer'])) {
        if (!isset($_GET['id'])) {
            die("ID del sottoprodotto non specificato.");
        }
    
        $subproduct_id = $mysqli->real_escape_string($_GET['id']);
        
        // query di eliminazione
        $delete_offer = "DELETE FROM offers WHERE subproduct_id = '{$subproduct_id}'";
        
        if ($mysqli->query($delete_offer)) {
            if ($mysqli->affected_rows > 0) {
                $_SESSION['success'] = "Sconto eliminato con successo.";
            } else {
                $_SESSION['error'] = "Nessun sconto trovato per questo articolo.";
            }
        } else {
            $_SESSION['error'] = "Errore nell'esecuzione della query di eliminazione: " . $mysqli->error;
        }

        header('Location: /MotorShop/subproduct-list.php?id=' . $productId);
        exit;

    }    
 
    if (isset($_POST['add_offer'])) {
        if (!isset($_GET['id'])) {
            die("ID del sottoprodotto non specificato.");
        }
        
        $subproduct_id = $mysqli->real_escape_string($_GET['id']);
        $activation_date = date('Y-m-d'); // Data corrente
        $duration = intval($_POST['duration']); // Durata in giorni
        $expiration_date = date('Y-m-d', strtotime("+$duration days"));
        $percentage = intval($_POST['percentage']); //
        
        $check_offer_query = "SELECT COUNT(*) as count FROM offers WHERE subproduct_id = '{$subproduct_id}'";
        $check_result = $mysqli->query($check_offer_query);
        
        if ($check_result) {
            $count = $check_result->fetch_assoc()['count'];
            $check_result->free();
    
            if ($count > 0) {
                // Esiste già un’offerta
                $_SESSION['error'] = "E' già attivo uno sconto per questo articolo.";
                // header('Location: /MotorShop/subproduct-list.php?id=' . $productId);
                exit;
            }
    
            // se non esiste già un’offerta
            $offer_query = "INSERT INTO offers (subproduct_id, activation_date, expiration_date, percentage) VALUES ('{$subproduct_id}', '{$activation_date}', '{$expiration_date}', {$percentage})";
            
            if ($mysqli->query($offer_query)) {
                $_SESSION['success'] = "Sconto aggiunto con successo.";
            } else {
                $_SESSION['error'] = "Errore nell'esecuzione della query di inserimento: " . $mysqli->error;
            }
    
            header('Location: /MotorShop/subproduct-list.php?id=' . $productId);
            exit;
        } else {
            $_SESSION['error'] = "Errore nella preparazione della query di controllo: " . $mysqli->error;
            // header('Location: /MotorShop/subproduct-list.php?id=' . $productId);
            exit;
        }
    }    

    $main->setContent("body", $body->get());
    $main->close();

} else {
    header("Location: /MotorShop/login.php");
    exit;
}
?>