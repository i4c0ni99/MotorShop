<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/add-product.html");

$main->setContent('name', $_SESSION['user']['name']);

// carica le marche
$brands = $mysqli->query("SELECT id, name FROM brands");

foreach ($brands as $marca) {
    $body->setContent('brand_name', $marca['name']);
    $body->setContent('brand_id', $marca['id']);
    if (isset($_GET['brand_id']) && !empty($_GET['brand_id']) && $_GET['brand_id'] == $marca['id']){
        $body->setContent('select_brand', $marca['name']);
        $body->setContent('select_brand_id', $marca['id']);
    }
}

if(!isset($_GET['brand_id'])){
    $body->setContent('select_brand','Scegli una marca');
    $body->setContent('select_brand_id','');
}

// categorie per la form
$data = $mysqli->query("SELECT id, name FROM categories");
if(!isset($_GET['cat_id'])){
    $body->setContent('select_cat','Scegli una categoria');
    $body->setContent('select_cat_id','');
}
if(!isset($_GET['sub_cat_id'])){
    $body->setContent('select_sub_cat','Segli una sotto categoria');
    $body->setContent('select_sub_id','');
}

foreach ($data as $item) {
    $body->setContent('cat_id', $item['id']);
    $body->setContent('categories', $item['name']);
    if (isset($_GET['cat_id']) && !empty($_GET['cat_id']) && $_GET['cat_id'] == $item['id']){

     $body->setContent('select_cat',$item['name']);
     $body->setContent('select_cat_id',$item['id']);
    }
    
}

// categoria selezionata
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
    $body->setContent("information",$_GET['information']);
    $body->setContent("product_image", $_GET['product_image']);
    $body->setContent("category_id",$_GET['category_id']);
    $body->setContent('brand_id', $_GET['brand_id']);
    
    foreach($subCat as $key) {
        $body->setContent("sub_cat_id", $key['id']);
        $body->setContent("sub_cat_name", $key['name']);
        if (isset($_GET['sub_cat_id']) && !empty($_GET['sub_cat_id']) && $_GET['sub_cat_id'] == $key['id']){
            $body->setContent('select_sub_cat',$key['name']);
            $body->setContent('select_sub_id',$key['id']);
           }
          
    }

    // sottocategoria selezionata
    if (isset($_GET['sub_cat_id']) && !empty($_GET['sub_cat_id'])) {
        $body->setContent("cat_id_in_sub", $category_id);
        $body->setContent("title", $_GET['title']);
        $body->setContent("description", $_GET['description']);
        $body->setContent("details", $_GET['details']);
        $body->setContent("code", $_GET['code']);
        $body->setContent("information",$_GET['information']);
        $body->setContent("product_image", $_GET['product_image']);
        $body->setContent("category_id",$_GET['category_id']);
        $body->setContent('brand_id', $_GET['brand_id']);
        $subcategory_id = $mysqli->real_escape_string($_GET['sub_cat_id']);
    } else {
        $subcategory_id = "NULL"; 
    }
} else {
    $category_id = "NULL"; 
    $subcategory_id = "NULL";  
}
if(isset($_GET['brand_id'])){
    $body->setContent("cat_id_in_sub", $category_id);
    $body->setContent("title", $_GET['title']);
    $body->setContent("description", $_GET['description']);
    $body->setContent("details", $_GET['details']);
    $body->setContent("information",$_GET['information']);
    $body->setContent("code", $_GET['code']);
    $body->setContent("product_image", $_GET['product_image']);
    $body->setContent('brand_id', $_GET['brand_id']);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $errors = [];

    // Verifica dati inseriti
   
    if (strlen($_POST['title']) < 3) {
        $errors[] = "Il titolo deve avere almeno 3 caratteri.";
    }
    
    if (!preg_match('/^[A-Za-z0-9]{5}$/', $_POST['code'])) {
        $errors[] = "Il codice prodotto deve essere composto da 5 caratteri alfanumerici.";
    }
    
    if (strlen($_POST['description']) < 5) {
        $errors[] = "La descrizione deve avere almeno 5 caratteri.";
    }

    if (strlen($_POST['details']) < 5) {
        $errors[] = "Le specifiche devono avere almeno 5 caratteri.";
    }
    
    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] != 0) {
        $errors[] = "Carica un'immagine del prodotto.";
    }

    // controllo unicità di "code" e "title"
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
       
        $description = $mysqli->real_escape_string(strip_tags($_POST['description']));
        $details = $mysqli->real_escape_string(strip_tags($_POST['details']));
        $information = isset($_POST['information']) ? $mysqli->real_escape_string(strip_tags($_POST['information'])) : NULL;
 
        echo "Category ID: $category_id<br>";
        echo "Subcategory ID: $subcategory_id<br>";

        $insertQuery = "INSERT INTO products (code, title, description, availability, specification, information, brand_id, categories_id, subcategories_id) 
            VALUES ('$code', '$title', '$description', 0, '$details', '$information', ".$_GET['brand_id'].", ".$_GET['cat_id'].", ".$_GET['sub_cat_id'].")";

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