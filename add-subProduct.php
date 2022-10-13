<?php session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/add-subProduct.html");

$body->setContent('id',$_GET['id']) ;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $data=file_get_contents($_FILES['image1']['tmp_name']);
    $data64=base64_encode($data);
    echo $_POST['quantity'].",".$_POST['price']." ,".$_POST['color'].", ".$_POST['id'] ;
    $oid=$mysqli->query("INSERT INTO sub_products(products_id,color,price,quantity,availability) 
    value('{$_POST['id']}','{$_POST['color']}','{$_POST['price']}','{$_POST['quantity']}',1)");
     
    $id=$mysqli->insert_id;
   
    $mysqli->query("INSERT INTO sizes(quantity,size,availability,sub_products_id) value({$_POST['quantity']},'{$_POST['size']}',1,$id)");
   $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64')");
  header("location:/MotorShop/product-detail.php");
}
$main->setContent("body",$body->get());
$main->close();

?>