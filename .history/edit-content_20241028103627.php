<?php 

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    // Carica il file scelto dall'admin
    if (isset($_POST['file'])) {
        $file = $_POST['file'];
        $allowedFiles = ['privacy-policy.html', 'refund.html', 'shipping.html'];

        if (in_array($file, $allowedFiles)) {
            $filePath = "skins/motor-html-package/motor/" . $file;
            $htmlContent = file_get_contents($filePath);
        
            // Carica il contenuto nel body, se valido
            $body = new Template("skins/motor-html-package/motor/edit-content.html");
            $body->setContent('html_content', $htmlContent);
            $body->setContent('file', $file); 
        
            // Set body content and display
            $main->setContent("dynamic", $body->get());
            $main->close();
        
        } else {
            echo "File non valido!";
        }
    } else {
        echo "Nessun file selezionato!";
    }

} else {
    header("Location: /MotorShop/login.php");
    exit;
}
?>