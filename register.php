<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/motor-html-package/motor/login.html");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

if ($_POST['password']!=$_POST['confirmPassword']) {
  echo "<script type='text/javascript'>alert('Le password non coincidono');</script>";  
} else {
    doSignUp();
}

}

?>