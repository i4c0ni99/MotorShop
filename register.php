<?php


require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";






$main = new Template("skins/motor-html-package/motor/home.html");


if($_POST['password']!=$_POST['confirmPassword']){
    echo "<script type='text/javascript'>alert('password non coincidono');</script>";
}else{
    signUp();
}


?>