<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";


$main = new Template("index.html");


$oid = $mysqli->query("SELECT * FROM news");