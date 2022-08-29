<?php

 session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";


$main = new Template("skins/motor-html-package/motor/home.html");




$oid= $mysqli-> query("INSERT INTO users VALUES('{$_POST['name']}','{$_POST['surname']}',
                         '{$_POST['email']}','{$_POST['phoneNumber']}','{$_POST['password']}');");

                         

$main-> close();


?>