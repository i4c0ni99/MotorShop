<?php
require "include/template2.inc.php";
require "include/dbms.inc.php";
 
$main =new Template("skins/multikart_all_in_one/back-end/product-list.html");
 $oid=$mysqli->query("SELECT users.name,users.surname,users.email,groups.roul FROM users 
                      JOIN users_has_groups ON users.email=users_has_groups.users_email 
                      JOIN groups ON groups.id=users_has_groups.groups_id;");
 $result= $oid;
$id=1;
if($result->num_rows>0){
foreach($result as $key){
 $id++; 
 $main->setContent("id",$id);
 $main->setContent("name",$key['name']);
 $main->setContent("surname",$key['surname']);
 $main->setContent("email",$key['email']);
 $main->setContent("ruolo",$key['roul']);
}
}else{
    $main->setContent("id",'');
 $main->setContent("name",'');
 $main->setContent("surname",'');
 $main->setContent("email",'');
 $main->setContent("ruolo",'');
}



$main->close();

?>