<?php 

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbmslate2.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    // Load main template
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    // Get the file selected by the user
    if (isset($_POST['file'])) {
        $file = $_POST['file'];
        $allowedFiles = ['privacy-policy.html', 'refund.html', 'shipping.html'];

        // Validate the file name
        if (in_array($file, $allowedFiles)) {
            // Load the HTML content
            $filePath = "skins/motor-html-package/motor/" . $file;
            $htmlContent = file_get_contents($filePath);
        
            // Load body template for editing content
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