<?php

session_start();
require "include/template2.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    $filePath = "skins/motor-html-package/motor/privacy-policy.html";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $updatedContent = $_POST['html_content'];

        // Salva il contenuto aggiornato nel file
        if (file_put_contents($filePath, $updatedContent) === false) {
            echo "Errore durante la scrittura del file.";
        } else {
            // Reindirizza a una pagina di conferma o ricarica la pagina
            header('Location: /MotorShop/privacy-policy.php');
            exit;
        }
    } else {
        // Leggi il contenuto attuale del file HTML
        $fileContent = file_get_contents($filePath);
    }

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>