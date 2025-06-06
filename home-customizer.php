<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/home-customizer.html");

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    // carica slide
    $slides = $mysqli->query("SELECT * FROM slider"); 
    $slide_result = $slides;
    if($slide_result && $slide_result -> num_rows > 0) {
        foreach ($slide_result as $page) {
            $body->setContent("sliderId",$page['id']);
            $body->setContent("sliderTitle",$page['title']);
            $body->setContent("sliderDescription",$page['description']);  
            $body->setContent("manage",' <form method="post" action="/MotorShop/home-customizer.php">
                                            <input type="hidden" name="sliderId" value="'.$page['id'].'">
                                            <button type="submit" name="delete" class="btn btn-danger">Elimina</button>
                                        </form>'); 
    }
} else{
    $body->setContent("sliderId",'');
    $body->setContent("sliderTitle",'');
    $body->setContent("sliderDescription",'');  
    $body->setContent("manage",''); 
}

    if (isset($_POST['submit'])) {

        // inserisci slider
        $title = $mysqli->real_escape_string($_POST['title']);
        $description = $mysqli->real_escape_string($_POST['description']);

        // gestisci il caricamento dell'immagine
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $data = file_get_contents($fileTmpPath);
        $data64 = base64_encode($data);

        $insertQuery = "INSERT INTO slider (title, description, image) 
                VALUES ('$title', '$description', '$data64')";
                if ($mysqli->query($insertQuery)) {
                    $product_id = $mysqli->insert_id;
                }
    }

    if (isset($_POST['delete'])) {
        if (isset($_POST['sliderId']) && is_numeric($_POST['sliderId'])) {
            $id = intval($_POST['sliderId']);
            
            $mysqli->begin_transaction();
            try {
                // eliminazione la slide
                $deleteSlider = $mysqli->prepare("DELETE FROM slider WHERE id = ?");
                $deleteSlider->bind_param("i", $id);  // 'i' integer (id)
                $deleteSlider->execute();
                
                $mysqli->commit();
                
                $_SESSION['message'] = "Slide eliminata con successo.";
            } catch (Exception $e) {
                // errore
                $mysqli->rollback();
                $_SESSION['error'] = "Errore durante l'eliminazione della slide.";
            }
            
            header('Location: /MotorShop/home-customizer.php');
            exit();
        }
    }    
    
    $main->setContent('user',$_SESSION['user']['name']);
    $main->setContent("body", $body->get());
    $main->close();

} else {
    
    header("Location: /MotorShop/login.php");
    exit();
    
}

?>