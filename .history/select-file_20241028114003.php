<?php
 
session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

    $main = new Template("skins/motor-html-package/motor/frame-customer.html");

    $body = new Template("skins/motor-html-package/motor/select-file.html");

    // Set available files
    $body->setContent("privacy-policy", "privacy-policy.html");
    $body->setContent("refund", "refund.html");
    $body->setContent("shipping", "shipping.html");

    // Set body content and display
    $main->setContent("dynamic", $body->get());
    $main->close();
    
} else {
    header("Location: /MotorShop/login.php");
    exit;
}
?>