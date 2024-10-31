<?php 

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    // Controlla se è stata selezionata una pagina
    if (isset($_POST['file'])) {
        $file = $_POST['file'];

        // Se l'utente ha scelto di creare una nuova pagina
        if ($file === 'new' && isset($_POST['newFileName'])) {
            $newFileName = $_POST['newFileName'];
            $newFilePath = "skins/motor-html-package/motor/{$newFileName}.html";

            // Crea il nuovo file HTML con contenuto predefinito
            $defaultContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$newFileName}</title>
</head>
<body>
<section class="main-banner mv-wrap">
    <div data-image-src="images/background/demo_bg_1920x1680.png" class="mv-banner-style-1 mv-bg-overlay-dark overlay-0-85 mv-parallax">
        <div class="page-name mv-caption-style-6">
            <div class="container">
                <h1 class="mv-title-style-9"><strong><span class="main">{$newFileName}</span></strong></h1>
            </div>
        </div>
    </div>
</section>
<section class="mv-main-body faqs-main mv-bg-gray mv-wrap">
    <div class="container">
        <div class="faqs-inner mv-box-shadow-gray-1 mv-bg-white">
            <div class="faqs-box mv-accordion-style-3">
                <p>Contenuto da modificare tramite editor.</p>
            </div>
        </div>
    </div>
</section>
</body>
</html>
HTML;

            file_put_contents($newFilePath, $defaultContent);
            // Reindirizza a edit-content.php per modificare la nuova pagina
            header("Location: edit-content.php?file={$newFileName}.html");
            exit;

        } else {
            // Carica il file esistente
            $allowedFiles = ['privacy-policy.html', 'refund.html', 'shipping.html'];
            if (in_array($file, $allowedFiles)) {
                $filePath = "skins/motor-html-package/motor/" . $file;
                $htmlContent = file_get_contents($filePath);

                $body = new Template("skins/motor-html-package/motor/edit-content.html");
                $body->setContent('html_content', $htmlContent);
                $body->setContent('file', $file); 
                $main->setContent("dynamic", $body->get());
                $main->close();
            } else {
                echo "File non valido!";
            }
        }
    } else {
        echo "Nessun file selezionato!";
    }

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>