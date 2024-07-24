<?php session_start();
require "include/template2.inc.php";
require "include/dbms.inc.php";


if($_GET['code']==100){
$main = new Template("skins/multikart_all_in_one/back-end/error.html");
  foreach($_SESSION['user']['script'] as $item) 
echo  $item.","; 
}
//da mettere dentro il tasto che salva la taglia header('Location:'.$_SERVER['PHP_SELF'].'?'.'id='.$_POST['code']); die; 

?>