<?php
 
session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {
    
    // Load main template
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    // Load body template for selecting a file
    $body = new Template("skins/motor-html-package/motor/select-file.html");

    // Set available files
    $body->setContent("privacy_policy", "skins/motor-html-package/motor/privacy-policy.html");
    $body->setContent("refund", "skins/motor-html-package/motor/refund.html");
    $body->setContent("shipping", "skins/motor-html-package/motor/shipping.html");

    // Set body content and display
    $main->setContent("dynamic", $body->get());
    $main->close();
} else {
    header("Location: /MotorShop/login.php");
    exit;
}
?>