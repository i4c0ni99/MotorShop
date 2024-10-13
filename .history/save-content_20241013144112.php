<?php 

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    // Stampa per debug
    var_dump($file); // Debug: valore del file
    var_dump($htmlContent); // Debug: contenuto HTML
    var_dump($allowedFiles); // Debug: lista dei file permessi
    
    if (isset($_POST['file']) && isset($_POST['html_content'])) {
        $file = trim($_POST['file']); // Rimuove spazi bianchi
        $htmlContent = $_POST['html_content'];
        $allowedFiles = ['privacy-policy.html', 'refund.html', 'shipping.html'];

        // Stampa per debug
        var_dump($file); // Debug: valore del file
        var_dump($allowedFiles); // Debug: lista dei file permessi

        // Validate the file name
        if (in_array($file, $allowedFiles)) {
            $filePath = "skins/motor-html-package/motor/" . $file;
            
            // Save the new content
            file_put_contents($filePath, $htmlContent);

            echo "Contenuto salvato correttamente!";
        } else {
            echo "File non valido!";
        }
    } else {
        echo "Dati mancanti!";
    }
    
} else {
    header("Location: /MotorShop/login.php");
    exit;
}
?>