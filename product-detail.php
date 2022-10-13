<?php session_start();


require "include/dbms.inc.php";
require "include/template2.inc.php";

 
 if($_SESSION['user']['goups']==1){
    $main =new Template("skins/multikart_all_in_one/back-end/product-detail.html");
    }else{
       $main =new Template("skins/motor-html-package/motor/product-detail.html");
    }
    $oid=$mysqli->query("SELECT title,id FROM products");
     $result= $oid;
    
    if($result->num_rows>0){
    foreach($result as $key){
    
     $main->setContent("id",$key['id']);
     $main->setContent("title",$key['title']);
    
     $data= $mysqli->query("SELECT images.imgsrc,images.sub_products_id,sub_products.price,sub_products.quantity,sub_products.color FROM products join sub_products ON sub_products.products_id=products.id 
        join images ON images.sub_products_id=sub_products.id where products.id={$key['id']}");

        
     if($data->num_rows>0){
        
    
        foreach($data as $item ){
            $main->setContent("colorDiv",'<li><a href="http://localhost/MotorShop/product-detail.php?id='.$key['id'].'&subId='.$item['sub_products_id'].'">
            <span style="background-color:'.$item['color'].'" class="icon-color"></span>
          </a>
      </li>');
            
           
            $main->setContent("subId",$item['sub_products_id']);
            $main->setContent("color",$item['color']);
            $main->setContent("img",$item['imgsrc']);
            $main->setContent("price",$item['price']);
            $main->setContent("quantity",$item['quantity']);
           
        }
        if(isset($_GET['subId'])){
         $data1=$mysqli->query("SELECT sizes.size from sizes where sizes.sub_products_id={$_GET['subId']}");
         $item2=$data1->fetch_assoc();
         $main->setContent("size",'<li class="active"><a href="#" class="mv-btn mv-btn-style-8">'.  $item2['size'].'</a></li>');
        }
      }
    }
    }else{
        $main->setContent("id",'');
     $main->setContent("title",'');
    
    }

 $main->close();
