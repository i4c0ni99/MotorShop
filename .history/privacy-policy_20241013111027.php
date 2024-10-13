<?php 

session_start();
require "include/template2.inc.php";

// Controlla se la sessione è attiva e se l'utente è autenticato
if (isset($_SESSION['user'])) {
    
    require "include/auth.inc.php";
    // Se la sessione è attiva, carica frame-customer
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    // Leggi il contenuto HTML della pagina
    $filePath = "skins/motor-html-package/motor/privacy-policy.html";
    $htmlContent = file_get_contents($filePath);

    // Form di modifica del contenuto
    $body = new Template("skins/motor-html-package/motor/edit-content.html");
    $body->setContent('html_content', htmlspecialchars($htmlContent));

    $main->setContent("dynamic", $body->get());
    $main->close();

} else {
    header("Location: /MotorShop/login.php");
exit;
}

$main->setContent('body', $body->get());
$main->close();

?>