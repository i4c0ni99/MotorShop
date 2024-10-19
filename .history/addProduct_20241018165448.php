<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user'])) {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/add-product.html");

$main->setContent('name', $_SESSION['user']['name']);



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];

    // Verifica dei dati inseriti

    // Titolo deve avere almeno 3 caratteri
    if (strlen($_POST['title']) < 3) {
        $errors[] = "Il titolo deve avere almeno 3 caratteri.";
    }

    // Codice prodotto deve essere alfanumerico da 5 caratteri
    if (!preg_match('/^[A-Za-z0-9]{5}$/', $_POST['code'])) {
        $errors[] = "Il codice prodotto deve essere composto da 5 caratteri alfanumerici.";
    }

    // Descrizione e specifiche sono campi obbligatori da almeno 5 caratteri
    if (strlen($_POST['description']) < 5) {
        $errors[] = "La descrizione deve avere almeno 5 caratteri.";
    }

    if (strlen($_POST['details']) < 5) {
        $errors[] = "Le specifiche devono avere almeno 5 caratteri.";
    }

    // Verifica che il file sia stato caricato
    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] != 0) {
        $errors[] = "Carica un'immagine del prodotto.";
    }

    // Controllo unicità di "code" e "title"
    $code = $mysqli->real_escape_string($_POST['code']);
    $title = $mysqli->real_escape_string($_POST['title']);
    $checkQuery = "SELECT * FROM products WHERE code = '$code' OR title = '$title'";
    $result = $mysqli->query($checkQuery);

    if ($result->num_rows > 0) {
        $errors[] = "Il codice prodotto o il titolo esistono già.";
    }

    if (isset($_POST['information'])) {
        $information = $_POST['information'];
    } else {
        $information = NULL;
    }

    // Se non ci sono errori, procedi con l'inserimento nel database
    if (empty($errors)) {
        $description = $mysqli->real_escape_string($_POST['description']);
        $details = $mysqli->real_escape_string($_POST['details']);
        $brand_id = $mysqli->real_escape_string($_POST['brand']);
 
        echo "Category ID: $category_id<br>";
        echo "Subcategory ID: $subcategory_id<br>";

        $insertQuery = "INSERT INTO products (code, title, description, availability, specification, information, brand_id, categories_id, subcategories_id) 
            VALUES ('$code', '$title', '$description', 1, '$details', '$information', '$brand_id', ".$_GET['cat_id'].", ".$_GET['sub_cat_id'].")";

        echo $insertQuery;  // Debug: Print the query

        if ($mysqli->query($insertQuery)) {
            $product_id = $mysqli->insert_id;

            // Gestisci il caricamento dell'immagine
            $fileTmpPath = $_FILES['product_image']['tmp_name'];
            $data = file_get_contents($fileTmpPath);
            $data64 = base64_encode($data);

            $insertImageQuery = "INSERT INTO images (product_id, sub_products_id, imgsrc) 
                                 VALUES ($product_id, NULL, '$data64')";
            if ($mysqli->query($insertImageQuery)) {
                header('location: /MotorShop/product-list.php');
                exit();
            } else {
                $errors[] = "Errore nell'inserimento dell'immagine.";
            }
        } else {
            $errors[] = "Errore nell'esecuzione della query di inserimento.";
        }
    }
    // Mostra errori
    if (!empty($errors)) {
        $errorMessages = "<ul>";
        foreach ($errors as $error) {
            $errorMessages .= "<li>$error</li>";
        }
        $errorMessages .= "</ul>";
        $body->setContent('errorMessages', $errorMessages);
    }
}
} else {
        header("Location: /MotorShop/login.php");
        exit;
    }

$main->setContent("body", $body->get());
$main->close();

?>