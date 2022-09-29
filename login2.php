<?php

require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php";



$main = new Template("skins/motor-html-package/motor/login.html");
if (!(isset($_SESSION['auth']) && $_SESSION['auth'] = true)) {
if ($_SERVER["REQUEST_METHOD"] == "POST") {

       doLogin(); 
     
}
} else { header("location:/MotorShop/index.php"); }

$main->close();

?>
if (!isset($_SESSION['user']['script'][basename($_SERVER['SCRIPT_NAME'])] )) {
        if(!$_SESSION['user']['script'][basename($_SERVER['SCRIPT_NAME'])]){
          //controlla se l'utente ha i permessi per quella pagina
            Header("Location: error.php?code=".ERROR_SCRIPT_PERMISSION);
            exit;  
        }
        
    }