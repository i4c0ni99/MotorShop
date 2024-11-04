<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require_once "include/utils/priceFormatter.php";

if (isset($_SESSION['user'])) {
   
    require "include/auth.inc.php";
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    $body = new Template("skins/motor-html-package/motor/home.html");
    
    $body->setContent('name', htmlspecialchars($_SESSION['user']['name']));
    $body->setContent('surname', htmlspecialchars($_SESSION['user']['surname']));
    $body->setContent('email', htmlspecialchars($_SESSION['user']['email']));
    $body->setContent('phone', htmlspecialchars($_SESSION['user']['phone']));

} else {
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

// 3 categorie principali
$helmet = $mysqli->query("SELECT products.*,sub_products.*,images.*,brands.name as brand_name FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id JOIN brands on brands.id = brand_id WHERE sub_products.price = ( SELECT MAX(sub_products.price) FROM products JOIN sub_products WHERE products.categories_id =14 AND sub_products.products_id = products.id) AND products.availability = 1 AND sub_products.availability = 1")->fetch_assoc();

$body->setContent('helmetTitle', $helmet['title']);
$body->setContent('helmetBrand', $helmet['brand_name']);
$body->setContent('helemtImg', $helmet['imgsrc']);
$body->setContent('helmetPrice', $helmet['price']);
$body->setContent('helmetId', $helmet['products_id']);
$stivali = $mysqli->query("SELECT products.*,sub_products.*,images.*,brands.name as brand_name FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id JOIN brands on brands.id = brand_id WHERE sub_products.price = ( SELECT MAX(sub_products.price) FROM products JOIN sub_products WHERE products.categories_id =15 AND sub_products.products_id = products.id) AND products.availability = 1 AND sub_products.availability = 1")->fetch_assoc();
$body->setContent('stivaliTitle', $stivali['title']);
$body->setContent('stivaliBrand', $stivali['brand_name']);
$body->setContent('stivalImg', $stivali['imgsrc']);
$body->setContent('stivaliPrice', $stivali['price']);
$body->setContent('stivalId', $stivali['products_id']);
$giacca = $mysqli->query("SELECT products.*,sub_products.*,images.*,brands.name as brand_name FROM sub_products JOIN products ON sub_products.products_id = products.id JOIN images ON images.product_id=products.id JOIN brands on brands.id = brand_id WHERE sub_products.price = ( SELECT MAX(sub_products.price) FROM products JOIN sub_products WHERE products.categories_id =20 AND sub_products.products_id = products.id) AND products.availability = 1 AND sub_products.availability = 1")->fetch_assoc();
$body->setContent('giaccaTitle', $giacca['title']);
$body->setContent('giaccaBrand', $giacca['brand_name']);
$body->setContent('giaccaImg', $giacca['imgsrc']);
$body->setContent('giaccaPrice', $giacca['price']);
$body->setContent('giaccaId', $giacca['products_id']);

$oidGiacca = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='GIACCA') AND products.availability = 1 AND sub_products.availability = 1 AND products.mediumRate >= 2.5 GROUP BY products.id ORDER BY mediumRate DESC limit 0,5;") ;

$resultCat = $oidGiacca;
if ($resultCat->num_rows > 0) {
    foreach ($resultCat as $key) {
        $imgOidCat = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCat = $imgOidCat->fetch_assoc();
        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem) {
            $price = $key['price'];
            $pricePercentage = formatPrice($price - ($price * ($offerItem['percentage'] / 100)));
            $price = formatPrice($price);
            $body->setContent(
                "giacche",
                '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item jackets">

                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCat['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="content-sale-off mv-label-style-3 label-primary">
                            <div class="label-inner">-' . $offerItem['percentage'] . '%</div>
                        </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ ' . $pricePercentage . ' </span>
                                                <span class="old-price">€ ' . $price . '</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">

                                               <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                               

                                        </div>
                                    </div>
                                </div>

                            </article>'
            );
        } else {
            $body->setContent('giacche', '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item jackets">

                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCat['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
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
                                                <span class="new-price">€ ' . $key['price'] . ' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                        <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                               ');
        }

    }
}

 $oidCasco = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='CASCO') AND products.availability = 1 AND sub_products.availability = 1 AND products.mediumRate >= 2.5 GROUP BY products.id ORDER BY mediumRate DESC limit 0,5");

$resultCatCasco = $oidCasco;
if ($resultCatCasco->num_rows > 0) {
    foreach ($resultCatCasco as $key) {
        $imgOidCatCasco = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCatCasco = $imgOidCatCasco->fetch_assoc();
        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem) {
            $price = $key['price'];
            $pricePercentage = formatPrice($price - ($price * ($offerItem['percentage'] / 100)));
            $price = formatPrice($price);
            $body->setContent(
                "caschi",
                '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item helmet">

                                 <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCatCasco['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="content-sale-off mv-label-style-3 label-primary">
                            <div class="label-inner">-' . $offerItem['percentage'] . '%</div>
                        </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ ' . $pricePercentage . ' </span>
                                                <span class="old-price">€ ' . $price . '</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                        <div class="content-button mv-btn-group text-center">
                                            <div class="group-inner">

                                               <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                               

                                        </div>
                                    </div>
                                </div>
                                </article>'
            );

        } else {
            $body->setContent('caschi', 
                            '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item helmet">

                               <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCatCasco['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
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
                                                <span class="new-price">€ ' . $key['price'] . ' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                        <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>');
        }

    }
}

$resultCatStivali = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='STIVALI') AND products.availability = 1 AND sub_products.availability = 1 AND products.mediumRate >= 2.5 GROUP BY products.id ORDER BY mediumRate DESC limit 0,5");

if ($resultCatStivali->num_rows > 0) {
    foreach ($resultCatStivali as $key) {
        $imgOidCatStivali = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}")->fetch_assoc();

        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem) {
            $price = $key['price'];
            $pricePercentage = formatPrice($price - ($price * ($offerItem['percentage'] / 100)));
            $price = formatPrice($price);
            $body->setContent(
                'stivali',
                '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item boots">

                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgOidCatStivali['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="content-sale-off mv-label-style-3 label-primary">
                                            <div class="label-inner">-' . $offerItem['percentage'] . '%</div>
                                        </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ ' . $pricePercentage . ' </span>
                                                <span class="old-price">€ ' . $price . '</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                     <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>'
            );

        } else {
            $body->setContent('stivali', '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item boots">

                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgOidCatStivali['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
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
                                                <span class="new-price">€ ' . $key['price'] . ' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                      <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>');
        }
    }
}
$oidProtezioni = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='PROTEZIONI') AND products.availability = 1 AND sub_products.availability = 1 AND products.mediumRate >= 2.5 GROUP BY products.id ORDER BY mediumRate DESC limit 0,5");

$resultProtezioni = $oidProtezioni;
if ($resultProtezioni->num_rows > 0) {
    foreach ($resultProtezioni as $key) {
        $imgOidCatProtezioni = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCatProtezioni = $imgOidCatProtezioni->fetch_assoc();
        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem) {
            $price = $key['price'];
            $pricePercentage = formatPrice($price - ($price * ($offerItem['percentage'] / 100)));
            $price = formatPrice($price);
            $body->setContent(
                "protezioni",
                            '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item protection">
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCatProtezioni['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="content-sale-off mv-label-style-3 label-primary">
                                            <div class="label-inner">-' . $offerItem['percentage'] . '%</div>
                                        </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ ' . $pricePercentage . ' </span>
                                                <span class="old-price">€ ' . $price . '</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                     <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>'
            );

        } else {
            $body->setContent('protezioni', 
                            '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item protection">
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCatProtezioni['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
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
                                                <span class="new-price">€ ' . $key['price'] . ' </span>
                                            </div>
                                            <div class="content-desc"><a
                                            href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                            title="' . $key['title'] . '" class="mv-overflow-ellipsis">
                                            ' . $key['title'] . '
                                            </a></div>
                                        </div>
                                    </div>

                                    <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>');
        }

    }
}
$oidPantaloni = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='PANTALONI') AND products.availability = 1 AND sub_products.availability = 1 AND products.mediumRate >= 2.5 GROUP BY products.id ORDER BY mediumRate DESC limit 0,5");

$resultCatPantaloni = $oidPantaloni;
if ($resultCatPantaloni->num_rows > 0) {
    foreach ($resultCatPantaloni as $key) {
        $imgOidCatPantaloni = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCatPantaloni = $imgOidCatPantaloni->fetch_assoc();

        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem) {
            $price = $key['price'];
            $pricePercentage = formatPrice($price - ($price * ($offerItem['percentage'] / 100)));
            $price = formatPrice($price);
            $body->setContent(
                "pantaloni",
                            '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item pants">

                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCatPantaloni['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="content-sale-off mv-label-style-3 label-primary">
                                            <div class="label-inner">-' . $offerItem['percentage'] . '%</div>
                                        </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ ' . $pricePercentage . ' </span>
                                                <span class="old-price">€ ' . $price . '</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                   <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>'
            );

        } else {
            $body->setContent('pantaloni', '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item pants">

                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCatPantaloni['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
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
                                                <span class="new-price">€ ' . $key['price'] . ' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                     <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>');
        }
    }
}
$oidTute = $mysqli->query("SELECT products.title,products.id as prod_id ,sub_products.* FROM sub_products JOIN products ON sub_products.products_id=products.id WHERE categories_id=(SELECT id FROM categories WHERE name='TUTE') AND products.availability = 1 AND sub_products.availability = 1 AND products.mediumRate >= 2.5 GROUP BY products.id ORDER BY mediumRate DESC limit 0,5");

$resultCatTute = $oidTute;
if ($resultCatTute->num_rows > 0) {
    foreach ($resultCatTute as $key) {
        $imgOidCatTute = $mysqli->query("SELECT imgsrc from images where product_id={$key['prod_id']}");
        $imgCatTute = $imgOidCatTute->fetch_assoc();

        $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$key['id']}");
        $offerItem = $offer->fetch_assoc();
        if ($offerItem) {
            $price = $key['price'];
            $pricePercentage = formatPrice($price - ($price * ($offerItem['percentage'] / 100)));
            $price = formatPrice($price);
            $body->setContent(
                "tute",
                            '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item suits">

                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCatTute['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
                                                    class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs"><span
                                                        class="btn-inner"></span></a>

                                                <div class="content-message mv-message-style-1">
                                                    <div class="message-inner"></div>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="content-sale-off mv-label-style-3 label-primary">
                                            <div class="label-inner">-' . $offerItem['percentage'] . '%</div>
                                         </div>
                                    </div>

                                    <div class="content-main">
                                        <div class="content-text">
                                            <div class="content-price">
                                                <span class="new-price">€ ' . $pricePercentage . ' </span>
                                                <span class="old-price">€ ' . $price . '</span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                      <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>'
            );

        } else {
            $body->setContent('tute', 
                            '<article class="col-xs-6 col-sm-4 col-md-3 item post filter-item suits">
                                <div class="item-inner mv-effect-translate-1">
                                    <div class="content-default">
                                        <div class="content-thumb">
                                            <div class="thumb-inner mv-effect-relative"><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"><img
                                                        src="data:image;base64,' . $imgCatTute['imgsrc'] . '"
                                                        alt="demo" class="mv-effect-item" /></a><a
                                                    href="/MotorShop/product-detail.php?id=' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '"
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
                                                <span class="new-price">€ ' . $key['price'] . ' </span>
                                            </div>
                                            <div class="content-desc"><a
                                                    href="/MotorShop/product-detail.php?id=.' . $key["prod_id"] . '"
                                                    title="' . $key['title'] . '" class="mv-overflow-ellipsis">' . $key['title'] . '</a></div>
                                        </div>
                                    </div>

                                      <div class="content-hover">
                                            <div class="content-button mv-btn-group text-center">
                                                <div class="group-inner">
                                                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>');
        }

    }
} 

$outlet = "SELECT products.title, products.id FROM products JOIN sub_products ON sub_products.products_id 
    = products.id JOIN offers ON sub_products.id = offers.subproduct_id WHERE EXISTS (SELECT 1 FROM sub_products WHERE sub_products.products_id = products.id) and offers.percentage >= 10 AND products.availability = 1 AND sub_products.availability = 1" 

. " GROUP BY products.id LIMIT 3";

$result = $mysqli->query($outlet);

$offert_prew = $mysqli->query("SELECT products.title, products.id,offers.percentage,sub_products.price FROM products JOIN sub_products ON sub_products.products_id 
    = products.id LEFT JOIN offers ON sub_products.id = offers.subproduct_id WHERE EXISTS (SELECT sub_products.id FROM sub_products WHERE sub_products.products_id = products.id) and offers.percentage >= 0 AND products.availability = 1 AND sub_products.availability = 1 GROUP BY products.id LIMIT 5") ;

if ($result && $result->num_rows > 0 && $offert_prew && $offert_prew->num_rows > 0) {
        $key= $offert_prew->fetch_assoc();
        $row = $result->fetch_all(PDO::FETCH_NUM);
        

        $product_id = $row[0][1];
        $product_id2 = $row[1][1];
    if($product_id  && $product_id2){



        $image_query = "
            SELECT images.imgsrc, sub_products.price,sub_products.id 
            FROM products 
            JOIN sub_products ON sub_products.products_id = products.id 
            JOIN images ON images.product_id = products.id 
            WHERE products.id = $product_id
            LIMIT 1
        ";

        $image_query2 = "
            SELECT images.imgsrc, sub_products.price,sub_products.id 
            FROM products 
            JOIN sub_products ON sub_products.products_id = products.id 
            JOIN images ON images.product_id = products.id 
            WHERE products.id = $product_id2
            LIMIT 1
        ";


        $image_data = $mysqli->query($image_query);

        $image_data2= $mysqli->query($image_query2);

        if ($image_data && $image_data->num_rows > 0 && $image_data2 && $image_data2->num_rows > 0 ) {
            $image_data = $image_data->fetch_assoc(); 
            $image_data2 = $image_data2->fetch_assoc(); 
            $body->setContent('offer_poster','
             <div class="featured-title mv-title-style-2">
            <div class="title-2-inner"><img
                    src="/../MotorShop/skins/motor-html-package/motor/images/icon/icon_m.png" alt="icon"
                    class="icon image-live-view" /><span class="main">IN OFFERTA</span><span class="sub"></span>
            </div>
        </div>
            <div class="block-style bg-white">
            <div class="mv-block-style-14">
                <div class="block-14-main text-center">
                    <div class="featured-title mv-title-style-2">
                        <div class="title-2-inner">
                            <span class="main">FUORI TUTTO</span><span class="sub">Nuovi sconti per te</span>
                        </div>
                    </div>
                    <div class="block-14-content mv-title-style-3 f-300">
                        <div class="title-3-text"><span class="behind">shock</span><span class="main"> <span
                                    class="sub-2">discount</span><span>shock</span><span class="sub">Contro lo spreco</span></span></div>
                    </div><a href="../MotorShop/product-list-customer.php?offert_percentage"
                        class="mv-btn mv-btn-style-1 responsive-btn-1-type-2 shop-now-button"><span
                            class="btn-inner"><i class="btn-icon fa fa-cart-plus"></i><span
                                class="btn-text">Vai alle offerte</span></span></a>
                </div>
                <!-- .block-14-main-->
                <img src="data:image;base64,'.$image_data['imgsrc'].'" alt="demo"
                    class="block-14-img left hidden-xs hidden-sm" /><img src="data:image;base64,'.$image_data2['imgsrc'].'"
                    alt="demo" class="block-14-img right hidden-xs hidden-sm" />
            </div>
            <!-- .mv-block-style-14-->
        </div>');
            
                
            
        }


        

        foreach($offert_prew as $key) {
            $product_id= $key['id'];
         $img = $mysqli->query("
            SELECT images.imgsrc, sub_products.price,sub_products.id 
            FROM products 
            JOIN sub_products ON sub_products.products_id = products.id 
            JOIN images ON images.product_id = products.id 
            WHERE products.id = ".$key['id']."
            LIMIT 1
        ")->fetch_assoc();

        $price = $key['price'];
        $pricePercentage=formatPrice($price - ($price * ($offerItem['percentage']/100)));
        $price=formatPrice($price);

        $body->setContent("offer_cat",'
                    <article class="col-xs-6 col-sm-4 col-md-3 item post">
                        <div class="item-inner mv-effect-translate-1">
                          <div class="content-thumb">
                            <div class="thumb-inner mv-effect-relative">
                                <a href="product-detail.php?id='.$product_id.'" title="'.$title.'">
                                    <img src="data:image;base64,'.$img['imgsrc'].'" alt="demo" class="mv-effect-item" />
                                </a>
      
                              <div onclick="$(this).remove()" class="content-sale-off mv-label-style-2 text-center">
                                <div class="label-2-inner">
                                  <ul class="label-2-ul">
                                    <li class="number">-'.$key['percentage'].'%</li>
                                    <li class="text">Sconto</li>
                                  </ul>
                                </div>
                              </div>
      
                              <div class="content-message mv-message-style-1">
                                <div class="message-inner"></div>
                              </div>
                            </div>
      
                            <div class="content-hover">
                              <div class="content-button mv-btn-group text-center">
                                <div class="group-inner">
                                  <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-1 responsive-btn-1-type-5"><span
                            class="btn-inner"><i class="btn-icon fa fa-long-arrow-right"></i><span class="btn-text">read
                                more</span></span></a>
                                </div>
                              </div>
                            </div>
                          </div>
      
                          <div class="content-default">
                            <div data-rate="true" data-score="5.0" class="content-rate mv-rate text-center">
                              <div class="rate-inner mv-f-14 text-left">
                                <div class="content-price"><span class="new-price">€ '.$pricePercentage.'</span><span class="old-price">€ '.$price.'</span></div>
                              </div>
                            </div>
      
                            
                            <div class="content-desc"><a href="product-detail.php?id='.$product_id.'" title="'.$key['title'].'" class="mv-overflow-ellipsis">'.$key['title'].'</a></div>
                          </div>
                        </div>
                    </article>
        ');
        }
    }
    
}





$main->setContent("dynamic", $body->get());
$main->close();