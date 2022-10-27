<?php session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/add-subProduct.html");
$body->setContent('id', $_GET['id']);
if(isset($_GET['id'])){
$result=$mysqli->query("SELECT categories.name FROM categories join products on categories.id=products.categories_id where products.id={$_GET['id'] }");
$data=$result->fetch_assoc();
if($data['name']!='STIVALI'){
$body->setContent('sizes','<div class="col-xl-8 col-sm-7">
    <select class="form-control digits"
        id="exampleFormControlSelect1" name="size">
        <option>XS</option>
        <option>S</option>
        <option>M</option>
        <option>L</option>
        <option>XL</option>
        <option>XXL</option>
    </select>  
</div>
');
$body->setContent('quantity',' <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                       <div class="input-group">
                           <input class="touchspin" type="text" name="quantity"
                               value="1">
                       </div>
                    </fieldset>
                    <p> </p>');
}}
/*if(isset($_POST['id'])){
    $result=$mysqli->query("SELECT categories.name FROM categories join products on categories.id=products.categories_id where products.id={$_GET['id'] }");
    $data=$result->fetch_assoc();
    if($data['name']!='STIVALI'){
    $body->setContent('sizes','<div class="col-xl-8 col-sm-7">
        <select class="form-control digits"
            id="exampleFormControlSelect1" name="size">
            <option>XS</option>
            <option>S</option>
            <option>M</option>
            <option>L</option>
            <option>XL</option>
            <option>XXL</option>
        </select>  
    </div>
    ');
    $body->setContent('quantity',' <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity"
                                   value="1">
                           </div>
                        </fieldset>
                        <p> </p>');
    } 
}*/
if(isset($_POST['addSize'])){
    $_SESSION['sizes']+=[$_POST['size']=>$_POST['quantity']];
    //array_push($_SESSION['sizes'],$_POST['size']);
    //$_SESSION['sizes'][$_POST['size']]=$_POST['quantity'];
    //print_r($_SESSION['sizes']);
  
    header('Location:'.$_SERVER['PHP_SELF'].'?'.'id='.$_POST['code']); die; 
}
/*
if($data['name']!='STIVALI'){
    $body->setContent('sizes', '<h5>S</h5><input class="checkbox_animated check-it" name="s" type="checkbox" value="S" id="flexCheckDefault" data-id="1">
<h5>M</h5><input class="checkbox_animated check-it" type="checkbox" name="m" value="M" id="flexCheckDefault" data-id="1">
<h5>L</h5><input class="checkbox_animated check-it" type="checkbox" name="l" value="L" id="flexCheckDefault" data-id="1">
<h5>XL</h5><input class="checkbox_animated check-it" type="checkbox" name="xl" value="XL" id="flexCheckDefault" data-id="1">
<h5>XXL</h5><input class="checkbox_animated check-it" type="checkbox" name="xxl" value="XXL" id="flexCheckDefault" data-id="1">');
$body->setContent('quantity',' <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                       <div class="input-group">
                           <input class="touchspin" type="text" name="quantity"
                               value="1" for=s>
                       </div>
                    </fieldset>
                    <p> </p>
                    <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                       <div class="input-group">
                           <input class="touchspin" type="text" name="quantity1"
                               value="1" for=m>
                       </div>
                    </fieldset>
                    <p> </p>
                    <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                       <div class="input-group">
                           <input class="touchspin" type="text" name="quantity2"
                               value="1" for=l>
                       </div>
                    </fieldset>
                    <p> </p>
                    <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                       <div class="input-group">
                           <input class="touchspin" type="text" name="quantity3"
                               value="1" for=xl>
                       </div>
                    </fieldset>
                    <p> </p>
                    <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                       <div class="input-group">
                           <input class="touchspin" type="text" name="quantity4"
                               value="1" for=xxl>
                       </div>
                       <p> </p>
                    </fieldset>');
}else{
        $body->setContent('sizes', '<input class="checkbox_animated check-it" name="s" type="checkbox" value="S" id="flexCheckDefault" data-id="1"><h5>38</h5>
    <input class="checkbox_animated check-it" type="checkbox" name="m" value="M" id="flexCheckDefault" data-id="1"><h5>39</h5>
    <input class="checkbox_animated check-it" type="checkbox" name="l" value="L" id="flexCheckDefault" data-id="1"><h5>40</h5>
  <input class="checkbox_animated check-it" type="checkbox" name="xl" value="XL" id="flexCheckDefault" data-id="1"><h5>41</h5>
<input class="checkbox_animated check-it" type="checkbox" name="xxl" value="XXL" id="flexCheckDefault" data-id="1"><h5>42</h5>
<input class="checkbox_animated check-it" type="checkbox" name="xxl" value="XXL" id="flexCheckDefault" data-id="1"><h5>43</h5>
<input class="checkbox_animated check-it" type="checkbox" name="xxl" value="XXL" id="flexCheckDefault" data-id="1"><h5>44</h5>
<input class="checkbox_animated check-it" type="checkbox" name="xxl" value="XXL" id="flexCheckDefault" data-id="1"><h5>45</h5>
<input class="checkbox_animated check-it" type="checkbox" name="xxl" value="XXL" id="flexCheckDefault" data-id="1"><h5>46</h5>');
    $body->setContent('quantity',' <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity"
                                   value="1" >
                           </div>
                        </fieldset>
                        <p> </p>
                        <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity1"
                                   value="1" >
                           </div>
                        </fieldset>
                        <p> </p>
                        <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity2"
                                   value="1" >
                           </div>
                        </fieldset>
                        <p> </p>
                        <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity3"
                                   value="1" >
                           </div>
                        </fieldset>
                        <p> </p>
                        <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity4"
                                   value="1">
                           </div
                        </fieldset>
                        <p> </p>
                        <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity5"
                                   value="1">
                           </div
                        </fieldset>
                        <p> </p>
                        <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity6"
                                   value="1">
                           </div
                        </fieldset>
                        <p> </p>
                        <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity7"
                                   value="1">
                           </div
                        </fieldset>
                        <p> </p>
                        <fieldset class="qty-box col-xl-9 col-xl-8 col-sm-7">
                           <div class="input-group">
                               <input class="touchspin" type="text" name="quantity8"
                                   value="1">
                           </div
                        </fieldset>
                        ');

}*/

  if(isset($_POST['save'])) {
  if($_FILES['image1']['tmp_name']!=0){
    $data1=file_get_contents($_FILES['image1']['tmp_name']);
    $data64_1=base64_encode($data);
    $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_1')");
  }
    if($_FILES['image2']['tmp_name']!=0){
    $data2=file_get_contents($_FILES['image2']['tmp_name']);
    $data64_2=base64_encode($data);
    $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_2')");
    }
    if($_FILES['image3']['tmp_name']!=0){
    $data3=file_get_contents($_FILES['image3']['tmp_name']);
    $data64_3=base64_encode($data);
    $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_3')");
    }
    if($_FILES['image4']['tmp_name']!=0){
    $data4=file_get_contents($_FILES['image4']['tmp_name']);
    $data64_4=base64_encode($data);
    $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_4')");
    }
    if($_FILES['image5']['tmp_name']!=0){
    $data5=file_get_contents($_FILES['image5']['tmp_name']);
    $data64_5=base64_encode($data);
    $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_5')");
    }
    if($_FILES['image6']['tmp_name']!=0){
    $data6=file_get_contents($_FILES['image6']['tmp_name']);
    $data64_6=base64_encode($data);
    $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_6')");
    }
    $data=file_get_contents($_FILES['image1']['tmp_name']);
    $data64=base64_encode($data);
    echo $_POST['quantity'].",".$_POST['price']." ,".$_POST['color'].", ".$_POST['id'] ;
    $oid=$mysqli->query("INSERT INTO sub_products(products_id,color,price,quantity,availability) 
    value('{$_POST['id']}','{$_POST['color']}','{$_POST['price']}','{$_POST['quantity']}',1)");
     
    $id=$mysqli->insert_id;
   
    foreach($_SESSION['sizes'] as $item){
      echo $item."=>".array_search($item,$_SESSION['sizes'])." ";
      $mysqli->query("INSERT INTO sizes(quantity,size,availability,sub_products_id) value($item,".array_search($item,$_SESSION['sizes']).",1,$id)");
    }
    
  
  header("location:/MotorShop/product-detail.php");
}
$main->setContent("body", $body->get());

$main->close();
?>