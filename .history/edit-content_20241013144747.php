<?php 

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    // Load main template
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    // Debug: Controlla se l'utente è autenticato
    var_dump($_SESSION['user']); // Stampa i dati dell'utente per il debug

    // Get the file selected by the user
    if (isset($_POST['file'])) {
        $file = $_POST['file'];
        $allowedFiles = ['privacy-policy.html', 'refund.html', 'shipping.html'];

        // Debug: Stampa il nome del file ricevuto
        var_dump($file); // Stampa il valore del file

        // Validate the file name
        if (in_array($file, $allowedFiles)) {
            // Load the HTML content
            $filePath = "skins/motor-html-package/motor/" . $file;

            // Debug: Controlla il percorso del file
            var_dump($filePath); // Stampa il percorso del file

            if (file_exists($filePath)) {
                $htmlContent = file_get_contents($filePath);
                
                // Debug: Controlla il contenuto del file caricato
                var_dump($htmlContent); // Stampa il contenuto del file

                // Load body template for editing content
                $body = new Template("skins/motor-html-package/motor/edit-content.html");
                $body->setContent('html_content', $htmlContent);
                $body->setContent('file', $file);  // Pass the correct file name
            
                // Set body content and display
                $main->setContent("dynamic", $body->get());
                $main->close();
            } else {
                echo "Il file non esiste!";
            }
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