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
        $body->setContent('src',$product['availability'] == 1?'<input name="availability" class="checkbox_animated check-it"
                                                            type="checkbox" value="0" checked="">':
                                                            '<input name="availability" class="checkbox_animated check-it"
                                                            type="checkbox" value="1">');
        echo "<script>console.log(".$product['availability'].")</script>";

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

// Carica recensioni
$feedback = $mysqli->query("SELECT * FROM feedbacks where products_id = {$_GET['id']}");
$count = 0;
$mediumRate;
if( $feedback -> num_rows > 0){
foreach ($feedback as $medium){
    $mediumRate += $medium['rate'];
   }
   $mediumReateRet /= $feedback -> num_rows ;
   $body -> setContent('mediumRateNum',$mediumReateRet);

foreach ($feedback as $item) {
   $count= 0;
   $rate = $item['rate'] ;
   $body->setContent("user", $item['users_email']);
   $body->setContent("creationDate", $item['date']);
   $body->setContent("content", $item['review']);
   $body->setContent("rate", $rate);
} 
}

// Carica le categorie dal database
// $categories_query = "SELECT id, name FROM categories";
// $categories_result = $mysqli->query($categories_query);
// $categories = [];

// while ($row = $categories_result->fetch_assoc()) {
//    $categories[] = $row['name'];
// }

// Passa le categorie al template
// foreach ($categories as $category) {
//    $body->setContent("categories", $category);
// }
} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent("body", $body->get());
$main->close();
?>