<?php

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/add-product.html");

if ($_SERVER["REQUEST_METHOD"] == "POST") {



}
$main->close();

?>