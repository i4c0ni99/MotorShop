<?php 

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";

// Controlla se la sessione è attiva e se l'utente è autenticato
if (isset($_SESSION['user'])) {
    // Se la sessione è attiva, carica frame-customer
    require "include/auth.inc.php";
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    $body = new Template("skins/motor-html-package/motor/home.html");

    // Popola il template con i dati dell'utente
    $body->setContent('name', htmlspecialchars($_SESSION['user']['name']));
    $body->setContent('surname', htmlspecialchars($_SESSION['user']['surname']));
    $body->setContent('email', htmlspecialchars($_SESSION['user']['email']));
    $body->setContent('phone', htmlspecialchars($_SESSION['user']['phone']));

    $slides = $mysqli->query("SELECT * FROM slider"); 
    $slide_result = $slides;
    if($slide_result && $slide_result -> num_rows > 0) {
        foreach ($slide_result as $page) {
            $body->setContent("sliderTitle",$page['title']);
            $body->setContent("sliderDescription",$page['description']);
            $body->setContent("sliderButton",$page['button']);
            $body->setContent("sliderLink",$page['link']);
        }
    } 

} else {
    // Se la sessione non è attiva, carica frame-public
    $main = new Template("skins/motor-html-package/motor/frame_public.html");
    $body = new Template("skins/motor-html-package/motor/home.html");
  
    
}


$helmet= $mysqli->query("SELECT * FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id WHERE sub_products.products_id = ( SELECT MAX(id) FROM products WHERE categories_id =14 )")->fetch_assoc(); 
$body-> setContent('helmetTitle',$helmet['title']);
$body-> setContent('helmetBrand',$helmet['marca']);
$body -> setContent('helemtImg',$helmet['imgsrc']);
$body -> setContent('helmetPrice',$helmet['price']);
$body-> setContent('helmetId',$helmet['products_id']);
$stivali= $mysqli->query("SELECT * FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id WHERE sub_products.products_id = ( SELECT MAX(id) FROM products WHERE categories_id =15 )")->fetch_assoc(); 
$body-> setContent('stivaliTitle',$stivali['title']);
$body-> setContent('stivaliBrand',$stivali['marca']);
$body -> setContent('stivalImg',$stivali['imgsrc']);
$body -> setContent('stivaliPrice',$stivali['price']);
$body-> setContent('stivalId',$stivali['products_id']);
$giacca= $mysqli->query("SELECT * FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id WHERE sub_products.products_id = ( SELECT MAX(id) FROM products WHERE categories_id =20 )")->fetch_assoc(); 
$body-> setContent('giaccaTitle',$giacca['title']);
$body-> setContent('giaccaBrand',$giacca['marca']);
$body -> setContent('giaccaImg',$giacca['imgsrc']);
$body -> setContent('giaccaPrice',$giacca['price']);
$body-> setContent('giaccaId',$giacca['products_id']);


$oidGiacca=$mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
                      sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='GIACCA')
                      ORDER BY mediumRate DESC limit 0,5");

$resultCat=$oidGiacca;
if($resultCat->num_rows > 0){
foreach($resultCat as $key){
    $imgOidCat= $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
    $imgCat=$imgOidCat->fetch_assoc();


$body->setContent("priceCatGiacche",$key['price']);
$body->setContent("titleCatGiacche",$key['title']);
$body->setContent("imgCatGiacche",$imgCat['imgsrc']);
//aggiungere il medium rate 
}
}

$oidCasco=$mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
                          sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='CASCO')
                          ORDER BY mediumRate DESC limit 0,5");

$resultCatCasco=$oidCasco;
if($resultCatCasco->num_rows > 0){
    foreach($resultCatCasco as $key){
        $imgOidCatCasco= $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCatCasco=$imgOidCatCasco->fetch_assoc();
    
    
    $body->setContent("priceCatCasco",$key['price']);
    $body->setContent("titleCatCasco",$key['title']);
    $body->setContent("imgCatCasco",$imgCatCasco['imgsrc']);
    //aggiungere il medium rate 
    }
}

$resultCatStivali=$mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='STIVALI')
ORDER BY mediumRate DESC limit 0,9");


if($resultCatStivali->num_rows > 0){
foreach($resultCatStivali as $key){
$imgOidCatStivali= $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}")->fetch_assoc();



$body->setContent("priceCatStivali",$key['price']);
$body->setContent("titleCatStivali",$key['title']);
$body->setContent("imgCatStivali",$imgOidCatStivali['imgsrc']);
//aggiungere il medium rate 
}
}
$oidProtezioni=$mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='PROTEZIONI')
ORDER BY mediumRate DESC limit 0,9");

$resultProtezioni=$oidProtezioni;
if($resultProtezioni->num_rows > 0){
foreach($resultProtezioni as $key){
    $imgOidCatProtezioni= $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
    $imgCatProtezioni=$imgOidCatProtezioni->fetch_assoc();
    $body->setContent("priceCatProtezioni",$key['price']);
    $body->setContent("titleCatProtezioni",$key['title']);
    $body->setContent("imgCatProtezioni",$imgCatProtezioni['imgsrc']);
    //aggiungere il medium rate 
}
}
$oidPantaloni=$mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='PANTALONI')
ORDER BY mediumRate DESC limit 0,9");

$resultCatPantaloni=$oidPantaloni;
if($resultCatPantaloni->num_rows > 0){
foreach($resultCatPantaloni as $key){
$imgOidCatPantaloni= $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
$imgCatPantaloni=$imgOidCatPantaloni->fetch_assoc();


$body->setContent("priceCatPantaloni",$key['price']);
$body->setContent("titleCatPantaloni",$key['title']);
$body->setContent("imgCatPantaloni",$imgCatPantaloni['imgsrc']);
//aggiungere il medium rate 
}
}
$oidTute=$mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='TUTE')
ORDER BY mediumRate DESC limit 0,9");

$resultCatTute=$oidTute;
if($resultCatTute->num_rows > 0){
foreach($resultCatTute as $key){
$imgOidCatTute= $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
$imgCatTute=$imgOidCatTute->fetch_assoc();


$body->setContent("priceCatTute",$key['price']);
$body->setContent("titleCatTute",$key['title']);
$body->setContent("imgCatTute",$imgCatTute['imgsrc']);
//aggiungere il medium rate 
}
}

$bestPrice = $mysqli->query("SELECT * FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id WHERE sub_products.price = ( SELECT MAX(price) FROM sub_products )")->fetch_assoc(); 
$body->setContent('titleBestPrice',$bestPrice['title']);
$body ->setContent('descriptionBestPrice',$bestPrice['description']);
$body -> setContent('bestPriceImg',$bestPrice['imgsrc']);

$minPrice = $mysqli->query("SELECT * FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id WHERE sub_products.price = ( SELECT MIN(price) FROM sub_products )")->fetch_assoc(); 
$body->setContent('titleminPrice',$minPrice['title']);
$body ->setContent('descriptionminPrice',$minPrice['description']);
$body -> setContent('minPriceImg',$minPrice['imgsrc']);


$main->setContent("dynamic", $body->get());
$main->close();

?>