<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
include "include/auth.inc.php";

if (isset($_SESSION['user'])) {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/edit-product.html");

$main->setContent('name', $_SESSION['user']['name']);

// Verifica che products_id sia definito in GET
if (!isset($_GET['id'])) {
    die("ID del prodotto non specificato.");
}

// Controlla se è stato inviato il form di modifica
if (isset($_POST['edit'])) {

    // Costruisci la query di aggiornamento
    $update_query = "UPDATE products SET ";
    $updates = [];

    // Escape dei dati POST e costruzione della query di aggiornamento
    if (!empty($_POST['title'])) {
        $title = $mysqli->real_escape_string($_POST['title']);
        $updates[] = "title = '$title'";
    }
    if (!empty($_POST['description'])) {
        $description = $mysqli->real_escape_string($_POST['description']);
        $updates[] = "description = '$description'";
    }
    if (!empty($_POST['details'])) {
        $details = $mysqli->real_escape_string($_POST['details']);
        $updates[] = "specification = '$details'";
    }
    if (!empty($_POST['information'])) {
        $information = $mysqli->real_escape_string($_POST['information']);
        $updates[] = "information = '$information'";
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

    // Verifica se ci sono campi da aggiornare
    if (!empty($updates)) {
        $update_query .= implode(", ", $updates);
        $update_query .= " WHERE id = ".$_GET['id'];

        // Esegui la query di aggiornamento
        if ($mysqli->query($update_query)) {
            header('Location: /MotorShop/product-list.php?id=' . $_GET['id']);
            exit;
        } else {
            echo "Errore nell'esecuzione della query di aggiornamento: " . $mysqli->error;
        }
    } else {
        echo "Nessun dato da aggiornare.";
    }
}

// Carica i dettagli del prodotto per il form di modifica
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = intval($_GET['id']);
    
    $product_query = "SELECT * FROM products WHERE id = '$productId'";
    $product_result = $mysqli->query($product_query);
    
    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        
        $category_query = "SELECT name FROM categories WHERE id = '".$product['categories_id']."'";
        $category_result = $mysqli->query($category_query);
        $category_name = $category_result->fetch_assoc()['name'];

        $subcategory_query = "SELECT name FROM subcategories WHERE id = '".$product['subcategories_id']."'";
        $subcategory_result = $mysqli->query($subcategory_query);
        $subcategory_name = $subcategory_result->fetch_assoc()['name'];

        $body->setContent('id', $productId);
        $body->setContent('code', $product['code']);
        $body->setContent('title', $product['title']);
        $body->setContent('src',$product['availability'] == 1?'<input name="availability" class="checkbox_animated "
                                                            type="checkbox" value="0" checked="">':
                                                            '<input name="availability" class="checkbox_animated "
                                                            type="checkbox" value="1">');
    

        $body->setContent('description', $product['description']);
        $body->setContent('details', $product['specification']);
        $body->setContent('information', $product['information']);
        $body->setContent('category', $category_name);
        $body->setContent('subcategory', $subcategory_name);
    } else {
        echo "Nessun prodotto trovato con ID: " . $productId;
    }
} else {
    echo "ID prodotto non valido.";
}

// carica recensioni
$feedback = $mysqli->query("SELECT * FROM feedbacks where products_id = {$_GET['id']}");
$count = 0;
$mediumRate;
if( $feedback -> num_rows > 0){
    foreach ($feedback as $medium){
            $mediumRate += $medium['rate'];
        }
        $mediumRate /= $feedback -> num_rows;
        $body -> setContent('mediumRateNum',$mediumRate);

        foreach ($feedback as $item) {
            $count= 0;
            $rate = $item['rate'] ;
            $body->setContent('item','<div class="item">
                                            <div class="mv-dp-table">
                                                <div class="mv-dp-table-cell block-28-main">
                                                    <div class="block-28-main-header">
                                                    <div class="block-28-name">Email utente: '.$item['users_email'].'</div><span
                                                        class="block-28-date">Data di pubblicazione: '.$item['date'].'</span>
                                                    <div data-rate="true" data-score="4" class="block-28-rate mv-rate">
                                                        <div class="block-29-name">Valutazione: '.$rate.' / 5</div>
                                                        </div>
                                                    </div>
                                                    </div>
                                                    <div class="block-28-desc">Commento: '.$item['review'].'</div>
                                                    <div class="block-28-desc">
                                                        <form class="delete-address-form" method="post" action="/MotorShop/editproduct.php" style="display:inline;">
                                                            <input type="hidden" name="address_id" value="'.$item['id'].'"> 
                                                            <input type="submit" class="btn btn-danger" name="delete-address-button" value="Elimina">
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>');
            } 
    }else $body->setContent('item','non ci sono recenzione su questo prodotto');
} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();
?>