<?php

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    // Controlla se il form per creare una nuova pagina è stato inviato
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_page'])) {
        $page_title = htmlspecialchars($_POST['page_title']);
        $page_content = htmlspecialchars($_POST['page_content']);
        
        // Creazione di un nuovo file per la pagina
        $file_name = strtolower(str_replace(" ", "-", $page_title)) . ".html";
        $file_path = "pages/" . $file_name;

        // Contenuto della pagina
        $html_content = "
        <html>
        <head>
            <title>$page_title</title>
        </head>
        <body>
        <section class=\"main-banner mv-wrap\">
            <div class=\"mv-banner-style-1 mv-bg-overlay-dark overlay-0-85 mv-parallax\" data-image-src=\"images/background/demo_bg_1920x1680.png\">
                <div class=\"page-name mv-caption-style-6\">
                    <div class=\"container\">
                        <h1 class=\"mv-title-style-9\"><strong><span class=\"main\">$page_title</span></strong></h1>
                    </div>
                </div>
            </div>
        </section>
        <!-- .main-banner-->
        
        <section class=\"mv-main-body faqs-main mv-bg-gray mv-wrap\">
            <div class=\"container\">
                <div class=\"faqs-inner mv-box-shadow-gray-1 mv-bg-white\">
                    <div class=\"faqs-box mv-accordion-style-3\">
                        <div>
                            <p>Ultimo aggiornamento: Ottobre 2024<br /><br /></p>
                            $page_content
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- .mv-main-body-->
        </body>
        </html>";

        // Salva il contenuto della nuova pagina
        if (file_put_contents($file_path, $html_content)) {
            $message = "Pagina '$page_title' creata con successo.";
        } else {
            $message = "Errore nella creazione della pagina.";
        }
    }

    $body = new Template("skins/motor-html-package/motor/select-file.html");

    // Aggiungi un messaggio per la creazione della pagina
    if (isset($message)) {
        $body->setContent("message", $message);
    }

    // Form per la selezione della pagina da modificare e creazione di una nuova pagina
    $body->setContent("create_page_form", '
        <section class="main-banner mv-wrap">
            <div data-image-src="images/background/demo_bg_1920x1680.png" class="mv-banner-style-1 mv-bg-overlay-dark overlay-0-85 mv-parallax">
                <div class="page-name mv-caption-style-6">
                    <div class="container">
                        <div class="mv-title-style-9"><span class="main">Personalizza Pagine</span></div>
                    </div>
                </div>
            </div>
        </section>
        <!-- .main-banner-->

        <section class="mv-main-body faqs-main mv-bg-gray mv-wrap">
            <div class="container">
                <div class="faqs-inner mv-box-shadow-gray-1 mv-bg-white">
                    <div class="faqs-box mv-accordion-style-3">
                        <form action="edit-content.php" method="POST">
                            <label for="file">Seleziona la pagina da modificare:</label>
                            <select name="file" id="file">
                                <option value="privacy-policy.html">Privacy Policy</option>
                                <option value="refund.html">Refund Policy</option>
                                <option value="shipping.html">Shipping Policy</option>
                                <option value="new">Crea Nuova Pagina</option>
                            </select>
                            
                            <br><br>
                            <button class="mv-btn mv-btn-style-5 btn-5-h-30" type="submit">Seleziona</button>
                        </form>

                        <form method="post">
                            <div class="form-group">
                                <label for="page_title">Titolo della Pagina:</label>
                                <input type="text" id="page_title" name="page_title" required>
                            </div>
                            <div class="form-group">
                                <label for="page_content">Contenuto della Pagina:</label>
                                <textarea id="page_content" name="page_content" rows="10" required></textarea>
                            </div>
                            <button type="submit" name="create_page" class="btn btn-primary">Crea Pagina</button>
                        </form>
                        <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
                        <script>
                            CKEDITOR.replace("page_content");
                        </script>
                    </div>
                </div>
            </div>
        </section>
        <!-- .mv-main-body-->
    ');

    $main->setContent("dynamic", $body->get());
    $main->close();

} else {
    header("Location: /MotorShop/login.php");
    exit;
}
?>