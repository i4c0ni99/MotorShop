<?php

require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php";

global $mysqli;

$main = new Template("skins/motor-html-package/motor/login.html");

if (!(isset($_SESSION['auth']) && $_SESSION['auth'] = true)) {
if ($_SERVER["REQUEST_METHOD"] == "POST") {

       doLogin(); 
       
}
} else { header("location:/MotorShop/index.php"); }

$main->close();

?>