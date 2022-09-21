<?php
require "include/template2.inc.php";
require "include/dbms.inc.php";
 
$main =new Template("skins/multikart_all_in_one/back-end/product-list.html");

 $oid=$mysqli->query("SELECT title,id FROM products");
 $result= $oid;

if($result->num_rows>0){
foreach($result as $key){

 $main->setContent("code",$key['id']);
 $main->setContent("title",$key['title']);
 $data= $mysqli->query("SELECT images.imgsrc FROM products join sub_products ON sub_products.products_id=products.id 
    join images ON images.sub_products_id=sub_products.id where products.id={$key['id']}");
 if($data->num_rows>0){
    

    foreach($data as $item ){
       
        
        $main->setContent("img",' <img src="data:image/png;base64,' .  base64_encode($item['imgsrc'])  . '" class="img-fluid blur-up lazyload bg-img"/>');
       
    }
  }
}
}else{
    $main->setContent("id",'');
 $main->setContent("title",'');

}



$main->close();

?>