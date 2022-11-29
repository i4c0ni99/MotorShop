<?php session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/add-subProduct.html");
$body->setContent('id', $_GET['id']);
if(isset($_GET['id'])){
if( sizeof($_SESSION['sizes'])>=1){
    $body->setContent('price', $_SESSION['subProduct']['price']);
    $body->setContent('description', $_SESSION['subProduct']['description']);
}
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
}else{$body->setContent('sizes','<div class="col-xl-8 col-sm-7">
    <select class="form-control digits"
        id="exampleFormControlSelect1" name="size">
        <option>36</option>
        <option>37</option>
        <option>38</option>
        <option>39</option>
        <option>40</option>
        <option>41</option>
        <option>42</option>
        <option>43</option>
        <option>44</option>
        <option>45</option>
        <option>46</option>
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
if(isset($_POST['addSize'])){
    $_SESSION['sizes']+=[$_POST['size']=>$_POST['quantity']];
    $_SESSION['subProduct']['price']=$_POST['price'];
    $_SESSION['subProduct']['description']=$_POST['description'];

  //array_push($_SESSION['sizes'],$_POST['size']);
    //$_SESSION['sizes'][$_POST['size']]=$_POST['quantity'];
    //print_r($_SESSION['sizes']);

    header('Location:'.$_SERVER['PHP_SELF'].'?'.'id='.$_POST['code']); die;
}

if (isset($_GET['delete'])) {
       
  $mysqli->query(" DELETE FROM products WHERE id = '".$_GET['id']."' ");
        
  header('location:/MotorShop/product-list.php');


}


  if(isset($_POST['save'])) {

    $oid=$mysqli->query("INSERT INTO sub_products(products_id,color,price,availability)
    value('{$_POST['code']}','{$_POST['color']}','{$_POST['price']}',1)");

   $id=$mysqli->insert_id;

   if($_FILES['image1']['tmp_name']!=''){
        $data1=file_get_contents($_FILES['image1']['tmp_name']);
        $data64_1=base64_encode($data1);
        $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_1')");
      }
        if($_FILES['image2']['tmp_name']!=''){
        $data2=file_get_contents($_FILES['image2']['tmp_name']);
        $data64_2=base64_encode($data2);
        $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_2')");
        }
        if($_FILES['image3']['tmp_name']!=''){
        $data3=file_get_contents($_FILES['image3']['tmp_name']);
        $data64_3=base64_encode($data3);
        $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_3')");
        }
        if($_FILES['image4']['tmp_name']!=''){
        $data4=file_get_contents($_FILES['image4']['tmp_name']);
        $data64_4=base64_encode($data4);
        $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_4')");
        }
        if($_FILES['image5']['tmp_name']!=''){
        $data5=file_get_contents($_FILES['image5']['tmp_name']);
        $data64_5=base64_encode($data5);
        $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_5')");
        }
        if($_FILES['image6']['tmp_name']!=''){
        $data6=file_get_contents($_FILES['image6']['tmp_name']);
        $data64_6=base64_encode($data6);
        $mysqli->query("INSERT INTO images (sub_products_id,imgsrc) value($id,'$data64_6')");
        };

    foreach($_SESSION['sizes'] as $item){
        $size=array_search($item,$_SESSION['sizes']);
        $quantity=$item;
      $mysqli->query("INSERT INTO sizes(quantity,size,availability,sub_products_id) value('$quantity','$size',1,$id)");
    }

  unset($$_SESSION['sizes']);
  unset($_SESSION['subProduct']['price']);
  unset($_SESSION['subProduct']['description']);
  header("location:/MotorShop/product-detail.php?id=".$_GET['id']);
}
$main->setContent("body", $body->get());

$main->close();
?>
