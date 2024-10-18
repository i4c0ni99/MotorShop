<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/home-customizer.html");


if (isset($_SESSION['user'])) {

// Verifica se l'utente appartiene al gruppo 1
if ($_SESSION['user']['groups'] != '1') {
    header("Location: /MotorShop/login.php");
    exit();
}

    $slides = $mysqli->query("SELECT * FROM slider"); 
    $slide_result = $slides;
    if($slide_result && $slide_result -> num_rows > 0) {
        foreach ($slide_result as $page) {
            $body->setContent("sliderId",$page['id']);
            $body->setContent("sliderTitle",$page['title']);
            $body->setContent("sliderDescription",$page['description']);
            $body->setContent("image", $_GET['image']);
        $body->setContent("sliderLink",$page['link']);
    }
} 

    if (isset($_POST['submit'])) {

        // Gestisci il caricamento dell'immagine
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $data = file_get_contents($fileTmpPath);
        $data64 = base64_encode($data);

        // Inserisci pagina slider
        $title = $mysqli->real_escape_string($_POST['title']);
        $description = $mysqli->real_escape_string($_POST['description']);
        $insertQuery = "INSERT INTO slider (title, description, image) 
                VALUES ('$title', '$description', '$data64')";
                if ($mysqli->query($insertQuery)) {
                    $product_id = $mysqli->insert_id;
                    header('location: /MotorShop/home-customizer.php');
                    exit();
                } else {
                    
                }
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        $errors[] = "Carica un'immagine in alta qualità per lo slider.";
    }

    if (isset($_POST['delete'])) {
        if (isset($_POST['sliderId']) && is_numeric($_POST['sliderId'])) {
            $id = intval($_POST['sliderId']);
    
            // Inizia una transazione
            $mysqli->begin_transaction();
            
            try {
                // Query preparata per eliminare la slide
                $deleteSlider = $mysqli->prepare("DELETE FROM slider WHERE id = ?");
                $deleteSlider->bind_param("i", $id);  // 'i' per tipo integer
                $deleteSlider->execute();
    
                // Conferma l'eliminazione
                $mysqli->commit();
    
                // Messaggio di successo
                $_SESSION['message'] = "Slide eliminata con successo.";
            } catch (Exception $e) {
                // In caso di errore, esegui un rollback
                $mysqli->rollback();
    
                // Messaggio di errore
                $_SESSION['error'] = "Errore durante l'eliminazione della slide.";
            }
    
            // Redirect per aggiornare la pagina
            header('Location: /MotorShop/home-customizer.php');
            exit();
        }
    }    
    
    $main->setContent('user',$_SESSION['user']['name']);
    $main->setContent("body", $body->get());
    $main->close();

}

?>