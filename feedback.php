<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/motor-html-package/motor/frame-customer.html");
$body = new Template("skins/motor-html-package/motor/feedback.html");

if (!isset($_SESSION['user']['email'])) {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent('name', $_SESSION['user']['name']);
$main->setContent('surname', $_SESSION['user']['surname']);
$main->setContent('email', $_SESSION['user']['email']);

if (isset($_GET['id'])) {
    $sub_product_id = $mysqli->real_escape_string($_GET['id']);
    
    $product_query = $mysqli->query("SELECT products_id FROM sub_products WHERE id = '{$sub_product_id}'");
        
    if ($product_query && $product_query->num_rows > 0) {
        $product_data = $product_query->fetch_assoc();
        $product_id = $product_data['products_id'];
        
        $title_query = $mysqli->query("SELECT title FROM products WHERE id = '{$product_id}'");
        
        if ($title_query && $title_query->num_rows > 0) {
            $title_data = $title_query->fetch_assoc();
            $title = $title_data['title'];
            $body->setContent("title", $title);
        } else {
            $body->setContent("title", "Titolo non disponibile");
        }
        
        // Gestione inserimento feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit-review'])) {
    $rate = $mysqli->real_escape_string($_POST['rate']);
    $review = $mysqli->real_escape_string($_POST['review']);
    $user_email = $mysqli->real_escape_string($_SESSION['user']['email']);
    $current_date = date("Y-m-d");

    // Controlla se l'utente ha già inserito una recensione per il prodotto
    $check_query = $mysqli->query("
        SELECT COUNT(*) as review_count FROM feedbacks 
        WHERE users_email = '$user_email' 
        AND products_id = '$product_id'
    ");
    $check_result = $check_query->fetch_assoc();
    
    if ($check_result['review_count'] > 0) {
        echo "Hai già inserito una recensione per questo prodotto.";
        exit;
    }

    // Inserisci la recensione
    $insert_review = $mysqli->query("
        INSERT INTO feedbacks (users_email, products_id, rate, review, date) 
        VALUES ('$user_email', '$product_id', '$rate', '$review', '$current_date')
    ");
    
    if ($insert_review) {
        // Calcolare il mediumRate
        $average_query = $mysqli->query("
            SELECT AVG(rate) AS average_rate FROM feedbacks WHERE products_id = '{$product_id}'
        ");
        
        if ($average_query && $average_query->num_rows > 0) {
            $average_data = $average_query->fetch_assoc();
            $new_medium_rate = $average_data['average_rate'];

            // Aggiornare la colonna mediumRate nella tabella products
            $update_query = $mysqli->query("
                UPDATE products SET mediumRate = '{$new_medium_rate}' WHERE id = '{$product_id}'
            ");
            
            if ($update_query) {
                echo "Recensione pubblicata con successo e mediumRate aggiornato.";
            } else {
                echo "Recensione pubblicata, ma errore durante l'aggiornamento del mediumRate: " . $mysqli->error;
            }
        } else {
            echo "Errore nel calcolo del mediumRate: " . $mysqli->error;
        }
    } else {
        echo "Errore durante la pubblicazione della recensione: " . $mysqli->error;
    }
}
        
    } else {
        echo "Prodotto non trovato.";
    }
} else {
    header("Location: /MotorShop/editCustomerProfile.php");
    exit;
}

$main->setContent("dynamic", $body->get());
$main->close();

?>