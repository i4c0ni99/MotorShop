<?php session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";



$main=new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/add-product.html");
 $data=$mysqli->query("SELECT name FROM categories");

 foreach($data as $item){
        $body->setContent('categories',$item['name']);
 }
if(isset($_GET['elimina'])){

}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data=$mysqli->query("SELECT id FROM categories WHERE name='{$_POST['category']}'");
    foreach($data as $item){
       
        $mysqli->query("INSERT INTO products (code,title,description,availability,specification,categories_id) 
                value ('{$_POST['code']}','{$_POST['title']}','{$_POST['description']}',
                               0,'{$_POST['details']}','{$item['id']}')");
 }
    
          header('location:/MotorShop/product-list.php');
}
$main->setContent("body",$body->get());
$main->close();

?>