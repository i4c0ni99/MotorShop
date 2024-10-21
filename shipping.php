<?php 

require "include/template2.inc.php";
require "include/auth.inc.php";

// Controlla se la sessione è attiva e se l'utente è autenticato
if (isset($_SESSION['user'])) {
    
    // Se la sessione è attiva, carica frame-customer
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    $body = new Template("skins/motor-html-package/motor/shipping.html");

    // Popola il template con i dati dell'utente
    $body->setContent('name', htmlspecialchars($_SESSION['user']['name']));
    $body->setContent('surname', htmlspecialchars($_SESSION['user']['surname']));
    $body->setContent('email', htmlspecialchars($_SESSION['user']['email']));
    $body->setContent('phone', htmlspecialchars($_SESSION['user']['phone']));
} else {
    // Se la sessione non è attiva, carica frame-public
    $main = new Template("skins/motor-html-package/motor/frame_public.html");
    $body = new Template("skins/motor-html-package/motor/shipping.html");
}


$main->setContent("dynamic", $body->get());
$main->close();

?>