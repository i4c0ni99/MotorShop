<?php 

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

    $main = new Template("skins/motor-html-package/motor/frame-private.html");
    $body = new Template("skins/motor-html-package/motor/shipping.html");

    $body->setContent('name', htmlspecialchars($_SESSION['user']['name']));
    $body->setContent('surname', htmlspecialchars($_SESSION['user']['surname']));
    $body->setContent('email', htmlspecialchars($_SESSION['user']['email']));
    $body->setContent('phone', htmlspecialchars($_SESSION['user']['phone']));
    
}
 
 


$main->setContent("dynamic", $body->get());
$main->close();

?>