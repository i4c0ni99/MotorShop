<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

$main = new Template("skins/motor-html-package/motor/frame_public.html");
$body = new Template("skins/motor-html-package/motor/profile.html");

$main->setContent('name',$_SESSION['user']['name']);
$main->setContent('surname',$_SESSION['user']['surname']);
$main->setContent('email',$_SESSION['user']['email']);
$main->setContent('phone',$_SESSION['user']['phone']);

$data=$mysqli->query("SELECT avatar FROM users WHERE email='{$_SESSION['user']['email']}'");
$img=$data->fetch_assoc();
   if($img['avatar']==null){
    $main->setContent('img',"/../MotorShop/skins/multikart_all_in_one/back-end/assets/images/dashboard/user.jpg");
   }else{
    $main->setContent('img',"data:image;base64,"."{$img['avatar']}");
   }

    if (isset($_POST['edit-avatar-button'])) {

        $data = file_get_contents($_FILES['avatar']['tmp_name']);
        $data64 = base64_encode($data);
        echo $data64;
        $mysqli->query("UPDATE users SET avatar = '$data64' WHERE email  ='".$_SESSION['user']['email']."'");

        header("location:/../MotorShop/editCustomerProfile.php");

    }

    if (isset($_POST['edit-details-button'])) {

        $name = $_POST["name"];
        $surname = $_POST["surname"];
        $phone = $_POST["phone"];

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['surname'] = $surname;
        $_SESSION['user']['phone'] = $phone;

        if ($name != "" && $surname != "" && $phone != "" ) {

        $oid = $mysqli->query("UPDATE users SET name ='$name', surname = '$surname', phone= '$phone'
                             WHERE email  ='".$_SESSION['user']['email']."'");

        header("location:/MotorShop/editCustomerProfile.php");

        }

    }

    if (isset($_POST['change-pass-button'])) {

        $currentpassword = $_POST["currentpassword"];
        $newpassword = $_POST["newpassword"];
        $confirmpassword = $_POST["confirmpassword"];

        // Prendo la password MD5 dell'utente
        $password = $mysqli->query("SELECT password from users 
        WHERE email  ='".$_SESSION['user']['email']."'");
       $result= $password->fetch_assoc();

        // Faccio l'hashing della password inserita dall'utente nella form
        $passmd5 = crypto($currentpassword);

        // Verifico se la password corrente è corretta e se la nuova è 
       
        if ($newpassword == $confirmpassword && $passmd5 == $result['password'] ) {
   
        $oid = $mysqli->query("UPDATE users SET password = '".crypto($newpassword)."'
                             WHERE email  ='".$_SESSION['user']['email']."'");

                             header("location:/../MotorShop/editCustomerProfile.php");

        } else {

            echo $result['password'];
            echo "<script type='text/javascript'>alert('Attenzione, le password non coincidono');</script>";
            
        }

    }   

    if (isset($_POST['delete-account-button'])) {

        $oid = $mysqli->query("DELETE FROM users
                             WHERE email  ='".$_SESSION['user']['email']."'");

        header("location:/../MotorShop/logout.php");

    }

    if (isset($_POST['add-address-button'])) {

        $name = $_POST["name"];
        $surname = $_POST["surname"];
        $phone = $_POST["phone"];
        $province = $_POST["province"];
        $city = $_POST["city"];
        $address = $_POST["address"];
        $cap = $_POST["cap"];
        
        if ($name != "" && $surname != "" && $phone != "" && $province != "" && $city != "" && $address != "" && $cap != "") {
        
        $mysqli->query("INSERT INTO shipping_address (users_email, name, surname, phone, province, city, streetAddress, cap) VALUE ('".$_SESSION['user']['email']."', '$name', '$surname', '$phone', '$province', '$city', '$address', '$cap')");

        header("location:/../MotorShop/editCustomerProfile.php");

        }

    }

    $main->setContent("dynamic", $body->get());
    
    $main->close();

?>