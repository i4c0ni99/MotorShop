<?php

require "include/dbms.inc.php";
require "include/template2.inc.php";

session_start();

 $main = new Template("skins/multikart_all_in_one/back-end/index.html");

 $main->close();

?>