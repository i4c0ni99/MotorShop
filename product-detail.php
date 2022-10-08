<?php session_start();


require "include/dbms.inc.php";
require "include/template2.inc.php";
require "include/auth.inc.php";
 
 if($_SESSION['user']['goups']==1){
    $main =new Template("skins/multikart_all_in_one/back-end/product-detail.html");
    }else{
       $main =new Template("skins/motor-html-package/motor/product-detail.html");
    }
    /* $oid=$mysqli->query("SELECT title,id FROM products");
     $result= $oid;
    
    if($result->num_rows>0){
    foreach($result as $key){
    
     $main->setContent("id",$key['id']);
     $main->setContent("title",$key['title']);
    
     $data= $mysqli->query("SELECT images.imgsrc,sub_products.price FROM products join sub_products ON sub_products.products_id=products.id 
        join images ON images.sub_products_id=sub_products.id where products.id={$key['id']}");
     if($data->num_rows>0){
        
    
        foreach($data as $item ){
           
            
            $main->setContent("img",$item['imgsrc']);
            $main->setContent("price",$item['price']);
        }
      }
    }
    }else{
        $main->setContent("id",'');
     $main->setContent("title",'');
    
    }*/

 $main->close();
?>