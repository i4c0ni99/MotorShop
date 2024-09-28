<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user'])) {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/add-product.html");

$main->setContent('name', $_SESSION['user']['name']);

// Carica le marche
$brands = $mysqli->query("SELECT * FROM brands");
if($brands){
    foreach ($brands as $marca) {
    $body->setContent('brand-name',$marca['name']);
    $body->setContent('brand-id',);
    }
}

// Carica le categorie e sottocategorie
$data = $mysqli->query("SELECT id, name FROM categories");
if(!isset($_GET['cat_id'])){
    $body->setContent('select_cat','Scegli una categoria');
    $body->setContent('select_cat_id','');
}
if(!isset($_GET['sub_cat_id'])){
    $body->setContent('select_sub_cat','Scegli una sotto categoria');
    $body->setContent('select_sub_id','');
}

foreach ($data as $item) {
    $body->setContent('cat_id', $item['id']);
    $body->setContent('categories', $item['name']);
    if (isset($_GET['cat_id']) && !empty($_GET['cat_id']) && $_GET['cat_id'] == $item['id']){
        echo "<script>console.log('".$item['name']."');</script>";
          
     $body->setContent('select_cat',$item['name']);
     $body->setContent('select_cat_id',$item['id']);
    }
    
}
// La categoria è già stata selezionata
$category_condition = '';
if (isset($_GET['cat_id']) && !empty($_GET['cat_id'])) {
    $category_id = $mysqli->real_escape_string($_GET['cat_id']);
    $category_condition = " AND products.categories_id = $category_id ";
    $subCat = $mysqli->query("SELECT * FROM subcategories WHERE categories_id={$category_id}");
    $body->setContent("cat_id_in_sub", $category_id);
    $body->setContent("title", $_GET['title']);
    $body->setContent("description", $_GET['description']);
    $body->setContent("details", $_GET['details']);
    $body->setContent("code", $_GET['code']);
    $body->setContent("product_image", $_GET['product_image']);
    $body->setContent("category_id",$_GET['category_id']);
    

    
    foreach($subCat as $key) {
        $body->setContent("sub_cat_id", $key['id']);
        $body->setContent("sub_cat_name", $key['name']);
        if (isset($_GET['sub_cat_id']) && !empty($_GET['sub_cat_id']) && $_GET['sub_cat_id'] == $key['id']){
            $body->setContent('select_sub_cat',$key['name']);
            $body->setContent('select_sub_id',$key['id']);
           }
          
    }

    // La sottocategoria è già stata selezionata
    if (isset($_GET['sub_cat_id']) && !empty($_GET['sub_cat_id'])) {
        $body->setContent("cat_id_in_sub", $category_id);
        $body->setContent("title", $_GET['title']);
        $body->setContent("description", $_GET['description']);
        $body->setContent("details", $_GET['details']);
        $body->setContent("code", $_GET['code']);
        $body->setContent("product_image", $_GET['product_image']);
        $body->setContent("category_id",$_GET['category_id']);
        $subcategory_id = $mysqli->real_escape_string($_GET['sub_cat_id']);
    } else {
        $subcategory_id = "NULL";  // Set default if not provided
    }
} else {
    $category_id = "NULL";  // Set default if not provided
    $subcategory_id = "NULL";  // Set default if not provided
}

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
    if (strlen($_POST['descriptionProduct']) < 5) {
        $errors[] = "La descrizione deve avere almeno 5 caratteri.";
    }

    if (strlen($_POST['detailsProduct']) < 5) {
        $errors[] = "Le specifiche devono avere almeno 5 caratteri.";
    }

    // Categoria è obbligatoria
    // if (empty($_POST['category'])) {
    //     $errors[] = "Seleziona una categoria.";
    // }

    // Sottocategoria (non ancora) è obbligatoria
    // if (empty($_POST['subcategory'])) {
    //     $errors[] = "Seleziona una sottocategoria.";
    // }

    // Verifica che il file sia stato caricato
    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] != 0) {
        $errors[] = "Carica un'immagine del prodotto.";
    }

    // Se non ci sono errori, procedi con l'inserimento nel database
    if (empty($errors)) {
        // Recupera l'id della categoria selezionata
        // $categoryName = $mysqli->real_escape_string($_POST['category']);
        // $result = $mysqli->query("SELECT id FROM categories WHERE name='$categoryName'");
        
        // if ($result->num_rows > 0) {
            // $row = $result->fetch_assoc();
            // $categoryId = $row['id'];

            // Esegui l'inserimento nel database
            $code = $mysqli->real_escape_string($_POST['code']);
            $title = $mysqli->real_escape_string($_POST['title']);
            $description = $mysqli->real_escape_string($_POST['description']);
            $details = $mysqli->real_escape_string($_POST['details']);
            $marca = $mysqli->real_escape_string($_POST['marca']);

            echo "Category ID: $category_id<br>";
            echo "Subcategory ID: $subcategory_id<br>";

            $insertQuery = "INSERT INTO products (code, title, description, availability, specification, marca, categories_id, subcategories_id) 
                            VALUES ('$code', '$title', '$description', 0, '$details', '$marca', ".$_GET['cat_id'].",".$_GET['sub_cat_id'].")";

            echo $insertQuery; 

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
}
} else {
        header("Location: /MotorShop/login.php");
        exit;
    }

$main->setContent("body", $body->get());
$main->close();

?>