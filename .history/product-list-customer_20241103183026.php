<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require_once "include/utils/priceFormatter.php";


if (isset($_SESSION['user'])) {
    require "include/auth.inc.php";
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    $body = new Template("skins/motor-html-package/motor/product-grid-3.html");
    
    $body->setContent('name', htmlspecialchars($_SESSION['user']['name']));
    $body->setContent('surname', htmlspecialchars($_SESSION['user']['surname']));
    $body->setContent('email', htmlspecialchars($_SESSION['user']['email']));
    $body->setContent('phone', htmlspecialchars($_SESSION['user']['phone']));
} else {
    
    $main = new Template("skins/motor-html-package/motor/frame_public.html");
    $body = new Template("skins/motor-html-package/motor/product-grid-3.html");
}

// Ottieni l'ID del prodotto dall'URL
$product_id = isset($_GET['id']) ? $mysqli->real_escape_string($_GET['id']) : null;

// Carica tutte le categorie dal database
$categories_query = "SELECT id, name FROM categories";
$categories_result = $mysqli->query($categories_query);
$categories = [];

$brands_query = "SELECT id, name FROM brands";
$brands_result = $mysqli->query($brands_query);
$brands = [];

while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

while ($row = $brands_result->fetch_assoc()) {
    $brands[] = $row;
}

$PAGE = 0;
$TO = 12;
$currentPage = 1;

if (isset($_GET['page']) && isset($_GET['to'])) {
    $currentPage = max(1, intval($_GET['page']));
    if ($currentPage > 1) {
        $PAGE = ($currentPage - 1) * 12;
    }

    $to = $_GET['to'];
    $TO = ($to > 12 || $to < 1) ? 12 : $to;
}

// Aggiunta dei parametri di prezzo min e max con le nuove condizioni
// Paginazione
// Impostazioni di paginazione
$items_per_page = 12;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Inizializza la query per il conteggio dei prodotti




$brand_condition = '';
if (isset($_GET['brand_id']) && !empty($_GET['brand_id'])) {
    $brand_id = $mysqli->real_escape_string($_GET['brand_id']);
    $brand_condition = " AND products.brand_id = $brand_id ";
}
$category_condition = '';
if (isset($_GET['cat_id']) && !empty($_GET['cat_id'])) {
    $category_id = $mysqli->real_escape_string($_GET['cat_id']);
    $category_condition = " AND products.categories_id = $category_id ";
    $subCat = $mysqli -> query(" SELECT * FROM subcategories WHERE  categories_id={$category_id}");
    $body -> setContent("cat_id_in_sub",$category_id);
    $code='<div class="mv-aside mv-aside-tags">
                            <div class="aside-title mv-title-style-11">sub tags</div>
                            <div class="aside-body">
                              <div class="tag-list">
                                <div class="mv-btn-group">
                                  <div class="group-inner">';
    $code2='                    </div>
                              </div>
                            </div>
                          </div>';   
                          
     $body ->setContent('sub_tags_start',$code);  
                       
    foreach($subCat as  $key){
        $body -> setContent("sub_tag",'
    
                                                        
                                    <a name="sub_tag'.$key['id'].'" href="product-list-customer.php?cat_id='.$category_id.'&sub_cat_id='.$key['id'].'" class="mv-btn mv-btn-style-22">'.$key['name'].'</a>
             
                               
    ');
    }
    $body ->setContent('sub_tags_end',$code2);   
}
$sub_cat_condition = '';
if(isset($_GET['sub_cat_id'])){
    $sub_cat = $mysqli->real_escape_string($_GET['sub_cat_id']);
    $sub_cat_condition = " AND products.subcategories_id = $sub_cat ";
}
// Costruisci la parte iniziale della query SQL per selezionare i prodotti
$product_query_base ="
    SELECT products.title, products.id,offers.percentage,sub_products.price FROM products JOIN sub_products ON sub_products.products_id = products.id LEFT JOIN offers ON offers.subproduct_id = sub_products.id WHERE EXISTS (SELECT sub_products.id FROM sub_products WHERE sub_products.products_id = products.id) AND products.availability = 1 AND sub_products.availability = 1";

// Aggiungi la condizione per il filtro di prezzo

$product_query_base .= $category_condition;
$product_query_base .= $brand_condition;
$product_query_base.= $sub_cat_condition;

// Aggiungi la condizione per il filtro di testo di ricerca se specificato
if (isset($_GET['search_text']) && !empty($_GET['search_text'])) {
    $searchText = $mysqli->real_escape_string($_GET['search_text']);
    $product_query_base .= " AND products.title LIKE '%$searchText%' ";
}



// Aggiungi la condizione per il filtro di prezzo nella query di conteggio
if(isset($_GET['size'])){
$product_query_base .= " AND sub_products.size ='{$_GET['size']}'";

}
if(isset($_GET['min_price']) && isset($_GET['max_price'])){
$min_price = floatval($_GET['min_price']);
$max_price =  floatval($_GET['max_price']);
$product_query_base .= " AND sub_products.price BETWEEN $min_price AND $max_price ";
}
// Aggiungi la condizione per il filtro di categoria se specificato nella query di conteggio

// Aggiungi la condizione per il filtro di testo di ricerca se specificato nella query di conteggio
if (isset($_GET['search_text']) && !empty($_GET['search_text'])) {
    $searchText = $mysqli->real_escape_string($_GET['search_text']);
}



$product_query = $product_query_base . " GROUP BY sub_products.id ORDER BY offers.percentage ASC ";

if(isset($_GET['offert_percentage'])){
    $product_query = "SELECT products.title, products.id,offers.percentage,sub_products.price FROM products JOIN sub_products ON sub_products.products_id 
    = products.id LEFT JOIN offers ON sub_products.id = offers.subproduct_id WHERE EXISTS (SELECT sub_products.id FROM sub_products WHERE sub_products.products_id = products.id) and offers.percentage >= 0 AND products.availability = 1 AND sub_products.availability = 1 GROUP BY products.id ORDER BY offers.percentage ASC ";
}






// Completamento della query SQL per selezionare i prodotti con limitazione


$result = $mysqli->query($product_query);
$prodotti = []; // Definisci l'array fuori dal ciclo

if ($result && $result->num_rows > 0) {
    foreach ($result as $key) {
        $prodotti[$key['id']] = $key; // Usa l'id come chiave per garantire l'unicità
    }
}


$total_products = count($prodotti);
$total_pages = ceil($total_products / $items_per_page);
// Calcola l'offset per la query di recupero dei prodotti
$offset = ($currentPage - 1) * $items_per_page ;



// Limitare la pagina corrente
if ($currentPage < 1) {
    $currentPage = 1;
} elseif ($currentPage > $total_pages) {
    $currentPage = $total_pages;
}   

// Genera i pulsanti di paginazione
$pagination_html = '';
if ($total_pages > 1) {
    $pagination_html .= '<ul class="pagination">';
    
    // Pulsante "Indietro"
    if ($currentPage > 1) {
        $pagination_html .= '<li class="prev"><a href="?page=' . ($currentPage - 1) .  '">Indietro</a></li>';
    }

    // Pulsanti delle pagine
    for ($i = 1; $i <= $total_pages; $i++) {
        $active_class = ($i == $currentPage) ? 'class="active"' : '';
        $pagination_html .= '<li ' . $active_class . '><a href="?page=' . $i .'">' . $i . '</a></li>';
        
        // Aggiungi i puntini di sospensione
        if ($i == 2 && $currentPage > 3) {
            $pagination_html .= '<li><span>...</span></li>';
        } elseif ($i == $total_pages - 1 && $currentPage < $total_pages - 2) {
            $pagination_html .= '<li><span>...</span></li>';
        }
    }

    // Pulsante "Avanti"
    if ($currentPage < $total_pages) {
        $pagination_html .= '<li class="next"><a href="?page=' . ($currentPage + 1) .  '">Avanti</a></li>';
    }

    $pagination_html .= '</ul>';
}

if ($prodotti && $result->num_rows > 0) {
    $prodotti_values=array_values($prodotti);
    for($i=$offset;$i < $offset + 12;$i++) {
        if($prodotti_values[$i] == null) break;
    $body->setContent("id", $prodotti_values[$i]['id']);
    $body->setContent("title",$prodotti_values[$i]['title']);
    
      $product_id = $prodotti_values[$i]['id'];  
      $title = $prodotti_values[$i]['title']; 
     $img = $mysqli->query("
        SELECT images.imgsrc, sub_products.price,sub_products.id 
        FROM products 
        JOIN sub_products ON sub_products.products_id = products.id 
        JOIN images ON images.product_id = products.id 
        WHERE products.id = '".$prodotti_values[$i]['id']."'
        LIMIT 1
    ")->fetch_assoc();
            if($prodotti_values[$i]['percentage'] ){
                $price = $prodotti_values[$i]['price'];
                $img =  $img['imgsrc'];
                $pricePercentage=formatPrice($price - ($price * ($prodotti_values[$i]['percentage']/100)));
                $price=formatPrice($price);
                
                $body->setContent("code",
                '<article class="col-xs-6 col-sm-4 col-md-6 col-lg-4 item item-product-grid-3 post">
                    <div class="item-inner mv-effect-translate-1 mv-box-shadow-gray-1">
                        <div style="background-color: #fff;" class="content-thumb">
                            <div class="thumb-inner mv-effect-relative">
                            
                                <a href="product-detail.php?id='.$product_id.'" title="'.$title.'">
                                    <img src="data:image;base64,'.$img.'" alt="demo" class="mv-effect-item" />
                                </a>
                                <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs">
                                    <span class="btn-inner"></span>
                                </a>
                
                                <div class="content-message mv-message-style-1">
                                    <div class="message-inner"></div>
                                </div>
                            
                            <div onclick="$(this).remove()" class="content-sale-off mv-label-style-2 text-center">
                                    <div class="label-2-inner">
                                        <ul class="label-2-ul">
                                            <li class="number">-'.$prodotti_values[$i]['percentage'].'%</li>
                                            <li class="text">Sconto</li>
                                        </ul>
                                    </div>
                            </div>
                            
                            </div>
                        </div>
            
                        <div class="content-default">
                            <div class="content-desc">
                                <a href="#" class="mv-overflow-ellipsis">'.$title.'</a>
                            </div>
                            <br>
                            <div class="content-price">
                                <span class="new-price">€ '.$pricePercentage.' </span>
                                <span class="old-price">€ '.$price.'</span>
                            </div>
                            <input type="hidden" value="'.$product_id.'" name="id" href="javascript:void(0)">
                        </div>
                
                        <div class="content-hover">
                            <div class="content-button mv-btn-group text-center">
                                <div class="group-inner">
                                    <a href="product-detail.php?id='.$product_id.'"  class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                            <span class="btn-inner">
                                                <span class="btn-text">Scopri</span>
                                            </span>
                                        </a>
                                </div>
                            </div>
                        </div>                                
                    </div>
        </article>');
            }else{
            $img =  $img['imgsrc'];
            $price=formatPrice($prodotti_values[$i]['price']);
            
            $body->setContent("code",
            '<article class="col-xs-6 col-sm-4 col-md-6 col-lg-4 item item-product-grid-3 post">
            <div class="item-inner mv-effect-translate-1 mv-box-shadow-gray-1">
            <div style="background-color: #fff;" class="content-thumb">
                <div class="thumb-inner mv-effect-relative">
                  
                    <a href="product-detail.php?id='.$product_id.'" title="'.$title.'">
                        <img src="data:image;base64,'.$img.'" alt="demo" class="mv-effect-item" />
                    </a>
                    <a href="product-detail.php?id='.$product_id.'" class="mv-btn mv-btn-style-25 btn-readmore-plus hidden-xs">
                        <span class="btn-inner"></span>
                    </a>
    
                            <div class="content-message mv-message-style-1">
                                <div class="message-inner"></div>
                            </div>
                
                </div>
            </div>
    
            <div class="content-default">
                <div class="content-desc">
                    <a href="#" class="mv-overflow-ellipsis">'.$title.'</a>
                </div>
                <br>
                <div class="content-price">
                    <span class="new-price">€ '.$price.' </span>
                </div>
                <input type="hidden" value="'.$product_id.'" name="id" href="javascript:void(0)">
            </div>
    
            <div class="content-hover">
                <div class="content-button mv-btn-group text-center">
                    <div class="group-inner">
                       
                            <a href="product-detail.php?id='.$product_id.'"  class="mv-btn mv-btn-style-1 btn-1-h-40 responsive-btn-1-type-2 btn-add-to-wishlist">
                                <span class="btn-inner">
                                    <span class="btn-text">Scopri</span>
                                </span>
                            </a>
                        
                    </div>
                </div>
            </div>                                
        </div>
    </article>');
            
        } 
    }
} else {
    // Nessun prodotto trovato
   $body->setContent('code','<p>Nessun prodotto trovato</p>');
}

// Passa le categorie al template
foreach ($categories as $category) {
    $body->setContent("cat_id", $category['id']);
    $body->setContent("cat_name", $category['name']);
}

foreach ($brands as $brand) {
    $body->setContent("brand_id", $brand['id']);
    $body->setContent("brand_name", $brand['name']);
}



// Imposta il contenuto della paginazione nel tuo body
$body->setContent("pagination_html", $pagination_html);

// Passa il conteggio dei prodotti e il numero di pagine al template

$body->setContent("total_pages", $total_pages);
$body->setContent("total_products", $total_products);
// Passa le opzioni di taglia e colore al template
$body->setContent("sizes", $sizes);
$body->setContent("colors", $colors);
$body->setContent("selected_size", $selected_size);
$body->setContent("selected_color", $selected_color);

$main->setContent("dynamic", $body->get());
$main->close();
?>