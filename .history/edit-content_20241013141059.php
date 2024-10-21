<?php 

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    // Load main template
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    // Get the file selected by the user
    if (isset($_POST['file'])) {
        $file = $_POST['file'];
        $allowedFiles = ['privacy-policy.html', 'refund.html', 'shipping.html'];

        // Validate the file name
        
        
        
        

            // Load body template for editing content
            $body = new Template("skins/motor-html-package/motor/edit-content.html");
            $body->setContent('html_content', $htmlContent);

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