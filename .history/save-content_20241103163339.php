<?php 

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {
    
    if (isset($_POST['file']) && isset($_POST['html_content'])) {
        $file = trim($_POST['file']); 
        $htmlContent = $_POST['html_content'];
        $allowedFiles = ['privacy-policy.html', 'refund.html', 'shipping.html'];

        // Valida nome file
        if (in_array($file, $allowedFiles)) {
            $filePath = "skins/motor-html-package/motor/" . $file;
            
            // Salva contenut
            file_put_contents($filePath, $htmlContent);

            echo "Contenuto salvato correttamente!";
            header("Location: /MotorShop/select-file.php");
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