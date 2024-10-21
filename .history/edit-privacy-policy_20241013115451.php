<?php 
session_start();
require "include/template2.inc.php";
require "include/auth.inc.php";

// Check if session is active and user is admin
if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {
    
    // Load main template
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    // Load the HTML content
    $filePath = "skins/motor-html-package/motor/privacy-policy.html";
    $htmlContent = file_get_contents($filePath);

    // Load body template for editing content
    $body = new Template("skins/motor-html-package/motor/edit-content.html");
    $body->setContent('html_content', $htmlContent);


    // Set body content and display
    $main->setContent("dynamic", $body->get());
    $main->close();
} else {
    header("Location: /MotorShop/login.php");
    exit;
}
?>