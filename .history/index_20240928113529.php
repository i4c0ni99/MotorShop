<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require_once "include/utils/priceFormatter.php";
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

} else {
    // Se la sessione non è attiva, carica frame-public
    $main = new Template("skins/motor-html-package/motor/frame_public.html");
    $body = new Template("skins/motor-html-package/motor/home.html");
}

$slides = $mysqli->query("SELECT * FROM slider");
$slide_result = $slides;
if ($slide_result && $slide_result->num_rows > 0) {
    foreach ($slide_result as $page) {
        $body->setContent("sliderTitle", $page['title']);
        $body->setContent("sliderDescription", $page['description']);
        $body->setContent("sliderButton", $page['button']);
        $body->setContent("sliderLink", $page['link']);
    }
}

$helmet = $mysqli->query("SELECT * FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id WHERE sub_products.products_id = ( SELECT MAX(id) FROM products WHERE categories_id =14 )")->fetch_assoc();
$body->setContent('helmetTitle', $helmet['title']);
$body->setContent('helmetBrand', $helmet['marca']);
$body->setContent('helemtImg', $helmet['imgsrc']);
$body->setContent('helmetPrice', $helmet['price']);
$body->setContent('helmetId', $helmet['products_id']);
$stivali = $mysqli->query("SELECT * FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id WHERE sub_products.products_id = ( SELECT MAX(id) FROM products WHERE categories_id =15 )")->fetch_assoc();
$body->setContent('stivaliTitle', $stivali['title']);
$body->setContent('stivaliBrand', $stivali['marca']);
$body->setContent('stivalImg', $stivali['imgsrc']);
$body->setContent('stivaliPrice', $stivali['price']);
$body->setContent('stivalId', $stivali['products_id']);
$giacca = $mysqli->query("SELECT * FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id WHERE sub_products.products_id = ( SELECT MAX(id) FROM products WHERE categories_id =20 )")->fetch_assoc();
$body->setContent('giaccaTitle', $giacca['title']);
$body->setContent('giaccaBrand', $giacca['marca']);
$body->setContent('giaccaImg', $giacca['imgsrc']);
$body->setContent('giaccaPrice', $giacca['price']);
$body->setContent('giaccaId', $giacca['products_id']);


$oidGiacca = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
                      sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='GIACCA')
                      ORDER BY mediumRate DESC limit 0,5");

$resultCat = $oidGiacca;
if ($resultCat->num_rows > 0) {
    foreach ($resultCat as $key) {
        $imgOidCat = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCat = $imgOidCat->fetch_assoc();
        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem){
            $price = $key['price'];
            $pricePercentage=formatPrice($price - ($price * ($offerItem['percentage']/100)));
            $price=formatPrice($price);
            $$body->setContent(
                "giacche",
                '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item jackets">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['title'].'"><img
                                                        src="data:image;base64,'.$imgCat['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['title'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                                        <div class="content-sale-off mv-label-style-3 label-primary">
                        <div class="label-inner">-'.$offerItem['percentage'].'%</div>
                    </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$pricePercentage.' </span>
                                                <span class="old-price">€ '.$price.'</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>'
            );
           
        }else $body->setContent('giacche','<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item jackets">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgCat['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                             
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$key['price'].' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>');
    }
}

$oidCasco = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
                          sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='CASCO')
                          ORDER BY mediumRate DESC limit 0,5");

$resultCatCasco = $oidCasco;
if ($resultCatCasco->num_rows > 0) {
    foreach ($resultCatCasco as $key) {
        $imgOidCatCasco = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCatCasco = $imgOidCatCasco->fetch_assoc();
        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem){
            $price = $key['price'];
            $pricePercentage=formatPrice($price - ($price * ($offerItem['percentage']/100)));
            $price=formatPrice($price);
            $body->setContent(
                "caschi",
                '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item helmet">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgCatCasco['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                                        <div class="content-sale-off mv-label-style-3 label-primary">
                        <div class="label-inner">-'.$offerItem['percentage'].'%</div>
                    </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$pricePercentage.' </span>
                                                <span class="old-price">€ '.$price.'</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>'
            );
           
        }else $body->setContent('caschi','<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item helmet">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgCatCasco['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                             
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$key['price'].' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>');
     
       
    }
}

$resultCatStivali = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='STIVALI')
ORDER BY mediumRate DESC limit 0,9");


if ($resultCatStivali->num_rows > 0) {
    foreach ($resultCatStivali as $key) {
        $imgOidCatStivali = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}")->fetch_assoc();

        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem){
            $price = $key['price'];
            $pricePercentage=formatPrice($price - ($price * ($offerItem['percentage']/100)));
            $price=formatPrice($price);
            $body->setContent(
                "stivali",
                '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item boots">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgOidCatStivali['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                                        <div class="content-sale-off mv-label-style-3 label-primary">
                        <div class="label-inner">-'.$offerItem['percentage'].'%</div>
                    </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$pricePercentage.' </span>
                                                <span class="old-price">€ '.$price.'</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>'
            );
           
        }else $body->setContent('stivali','<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item boots">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgOidCatStivali['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                             
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$key['price'].' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>');
        //aggiungere il medium rate 
    }
}
$oidProtezioni = $mysqli->query("SELECT products.title as title ,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='PROTEZIONI')
ORDER BY mediumRate DESC limit 0,9");

$resultProtezioni = $oidProtezioni;
if ($resultProtezioni->num_rows > 0) {
    foreach ($resultProtezioni as $key) {
        $imgOidCatProtezioni = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCatProtezioni = $imgOidCatProtezioni->fetch_assoc();
        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem){
            $price = $key['price'];
            $pricePercentage=formatPrice($price - ($price * ($offerItem['percentage']/100)));
            $price=formatPrice($price);
            $$body->setContent(
                "protezioni",
                '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item protection">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgCatProtezioni['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                                        <div class="content-sale-off mv-label-style-3 label-primary">
                        <div class="label-inner">-'.$offerItem['percentage'].'%</div>
                    </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$pricePercentage.' </span>
                                                <span class="old-price">€ '.$price.'</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>'
            );
           
        }else $body->setContent('protezioni','<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item protection">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgCatProtezioni['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$key['price'].' </span>
                                            </div>
                                            <div class="content-desc"><a
                                            href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                            title="'.$key['tile'].'" class="mv-overflow-ellipsis">
                                            '.$key['tile'].'
                                            </a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>');
    }
}
$oidPantaloni = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='PANTALONI')
ORDER BY mediumRate DESC limit 0,9");

$resultCatPantaloni = $oidPantaloni;
if ($resultCatPantaloni->num_rows > 0) {
    foreach ($resultCatPantaloni as $key) {
        $imgOidCatPantaloni = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCatPantaloni = $imgOidCatPantaloni->fetch_assoc();

        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem){
            $price = $key['price'];
            $pricePercentage=formatPrice($price - ($price * ($offerItem['percentage']/100)));
            $price=formatPrice($price);
            $body->setContent(
                "pantaloni",
                '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item pants">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgCatPantaloni['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                                        <div class="content-sale-off mv-label-style-3 label-primary">
                        <div class="label-inner">-'.$offerItem['percentage'].'%</div>
                    </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$pricePercentage.' </span>
                                                <span class="old-price">€ '.$price.'</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>'
            );
           
        }else $body->setContent('pantaloni','<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item pants">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgCatPantaloni['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                             
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$key['price'].' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>');
        //aggiungere il medium rate 
    }
}
$oidTute = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON 
sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='TUTE')
ORDER BY mediumRate DESC limit 0,9");

$resultCatTute = $oidTute;
if ($resultCatTute->num_rows > 0) {
    foreach ($resultCatTute as $key) {
        $imgOidCatTute = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCatTute = $imgOidCatTute->fetch_assoc();

        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem){
            $price = $key['price'];
            $pricePercentage=formatPrice($price - ($price * ($offerItem['percentage']/100)));
            $price=formatPrice($price);
            $body->setContent(
                "tute",
                '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item suits">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgCatTute['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                                        <div class="content-sale-off mv-label-style-3 label-primary">
                        <div class="label-inner">-'.$offerItem['percentage'].'%</div>
                    </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$pricePercentage.' </span>
                                                <span class="old-price">€ '.$price.'</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>'
            );
           
        }else $body->setContent('tute','<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item suits">
                                
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"><img
                                                        src="data:image;base64,'.$imgCatTute['imgsrc'].'"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id='.$key["prod_id"].'"
                                                    title="'.$key['tile'].'"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        

                             
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ '.$key['price'].' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.'.$key["prod_id"].'"
                                                    title="'.$key['tile'].'" class="mv-overflow-ellipsis">'.$key['tile'].'</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">
                                                <button type="button" onclick="window.location.href='."product-detail.php?id=".$key['prod_id'].'" title="Scopri"
                                                    class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                                    <span class="btn-inner">
                                                        <span class="btn-text">Scopri</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             
                            </article>');
    }
}

$main->setContent("dynamic", $body->get());
$main->close();

?>