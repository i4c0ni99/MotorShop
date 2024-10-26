<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require_once "include/utils/priceFormatter.php";

// Verifica se l'utente è loggato
if (isset($_SESSION['user'])) {
    require "include/auth.inc.php";
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
    $body = new Template("skins/motor-html-package/motor/product-grid-3.html");
    // Popola il template con i dati dell'utente
    $body->setContent('name', htmlspecialchars($_SESSION['user']['name']));
    $body->setContent('surname', htmlspecialchars($_SESSION['user']['surname']));
    $body->setContent('email', htmlspecialchars($_SESSION['user']['email']));
    $body->setContent('phone', htmlspecialchars($_SESSION['user']['phone']));
} else {
    // Se l'utente non è loggato, carica frame_public
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
$TO = 9;
$currentPage = 1;

if (isset($_GET['page']) && isset($_GET['to'])) {
    $currentPage = max(1, intval($_GET['page']));
    if ($currentPage > 1) {
        $PAGE = ($currentPage - 1) * 9;
    }

    $to = $_GET['to'];
    $TO = ($to > 9 || $to < 1) ? 9 : $to;
}

// Aggiunta dei parametri di prezzo min e max con le nuove condizioni
$min_price = isset($_GET['min_price']) ? max(10, floatval($_GET['min_price'])) : 10;
$max_price = isset($_GET['max_price']) ? min(2500, floatval($_GET['max_price'])) : 2500;
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
    SELECT products.title, products.id 
    FROM products  
    JOIN sub_products ON sub_products.products_id = products.id 
    WHERE EXISTS (SELECT 1 FROM sub_products WHERE sub_products.products_id = products.id) AND sub_products.availability = 1 
    AND products.availability = 1";

// Aggiungi la condizione per il filtro di prezzo
$product_query_base .= " AND sub_products.price BETWEEN $min_price AND $max_price  AND sub_products.availability = 1";
$product_query_base .= $category_condition;
$product_query_base .= $brand_condition;
$product_query_base.= $sub_cat_condition;

// Aggiungi la condizione per il filtro di testo di ricerca se specificato
if (isset($_GET['search_text']) && !empty($_GET['search_text'])) {
    $searchText = $mysqli->real_escape_string($_GET['search_text']);
    $product_query_base .= " AND products.title LIKE '%$searchText%' ";
}

if(isset($_GET['offert_percentage'])){
    $product_query_base = "SELECT products.title, products.id FROM products JOIN sub_products ON sub_products.products_id 
    = products.id JOIN offers ON sub_products.id = offers.subproduct_id WHERE EXISTS (SELECT 1 FROM sub_products WHERE sub_products.products_id = products.id) and offers.percentage >= 10";
}
// Completamento della query SQL per contare i prodotti
$count_query = "SELECT COUNT(DISTINCT products.id) as total_products 
                FROM products
                JOIN sub_products ON sub_products.products_id = products.id
                WHERE products.availability = 1 
                AND sub_products.availability = 1
                AND EXISTS (SELECT 1 FROM sub_products WHERE sub_products.products_id = products.id)";

// Aggiungi la condizione per il filtro di prezzo nella query di conteggio
$count_query .= " AND sub_products.price BETWEEN $min_price AND $max_price AND sub_products.availability = 1 ";
if(isset($_GET['size'])){
$product_query_base .= "AND sub_products.size ='{$_GET['size']}'";
$count_query .= "AND sub_products.size ='{$_GET['size']}'";
}

// Aggiungi la condizione per il filtro di categoria se specificato nella query di conteggio
$count_query .= $category_condition;

// Aggiungi la condizione per il filtro di testo di ricerca se specificato nella query di conteggio
if (isset($_GET['search_text']) && !empty($_GET['search_text'])) {
    $searchText = $mysqli->real_escape_string($_GET['search_text']);
    $count_query .= " AND products.title LIKE '%$searchText%' ";
}

$count_result = $mysqli->query($count_query);
$total_products = $count_result->fetch_assoc()['total_products'];
$total_pages = ceil($total_products / 12);

// Completamento della query SQL per selezionare i prodotti con limitazione
$product_query = $product_query_base . " GROUP BY products.id LIMIT $PAGE, $TO";

$result = $mysqli->query($product_query);

if ($result && $result->num_rows > 0) {
    while ($key = $result->fetch_assoc()) {
        
        $body->setContent("id", $key['id']);
        $body->setContent("title", $key['title']);

        $product_id = $mysqli->real_escape_string($key['id']);
        $title = $mysqli->real_escape_string($key['title']);

        $image_query = "
            SELECT images.imgsrc, sub_products.price,sub_products.id 
            FROM products 
            JOIN sub_products ON sub_products.products_id = products.id 
            JOIN images ON images.product_id = products.id 
            WHERE products.id = '$product_id'
            LIMIT 1
        ";

        $image_data = $mysqli->query($image_query);
        
        if ($image_data && $image_data->num_rows > 0 ) {
            
            $item = $image_data->fetch_assoc();
            
            $offer = $mysqli->query("SELECT * FROM offers WHERE subproduct_id ={$item['id']}");
            $offerItem = $offer->fetch_assoc();
            if($offerItem){
            $price = $item['price'];
            $img =  $item['imgsrc'];
            $pricePercentage=formatPrice($price - ($price * ($offerItem['percentage']/100)));
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
                                <li class="number">-'.$offerItem['percentage'].'%</li>
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
            $img =  $item['imgsrc'];
            $price=formatPrice($item['price']);
            if($item['id'] != null){
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
            // Immagine non trovata
            $body->setContent("img", "/../MotorShop/skins/multikart_all_in_one/back-end/assets/images/dashboard/shopping-trolley.png"); // Placeholder image path
            // $body->setContent("price", "0.00"); // Placeholder price
        }
;
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

// Paginazione
// Impostazioni di paginazione
$items_per_page = 2;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Inizializza la query per il conteggio dei prodotti
$count_query = "SELECT COUNT(DISTINCT products.id) as total_products 
                FROM products
                JOIN sub_products ON sub_products.products_id = products.id
                WHERE products.availability = 1 
                AND sub_products.availability = 1
                AND EXISTS (SELECT 1 FROM sub_products WHERE sub_products.products_id = products.id)";

// Aggiungi la condizione per il filtro di testo di ricerca se specificato nella query di conteggio
if (isset($_GET['search_text']) && !empty($_GET['search_text'])) {
    $searchText = $mysqli->real_escape_string($_GET['search_text']);
    $count_query .= " AND products.title LIKE '%$searchText%' ";
}

$count_result = $mysqli->query($count_query);
$total_products = $count_result->fetch_assoc()['total_products'];
$total_pages = ceil($total_products / $items_per_page);




























// Limitare la pagina corrente
if ($currentPage < 1) {
    $currentPage = 1;
} elseif ($currentPage > $total_pages) {
    $currentPage = $total_pages;
}

// Calcola l'offset per la query di recupero dei prodotti
$offset = ($currentPage - 1) * $items_per_page;

// Query per recuperare i prodotti per la pagina corrente
$product_query = "SELECT products.* 
                  FROM products
                  JOIN sub_products ON sub_products.products_id = products.id
                  WHERE products.availability = 1 
                  AND sub_products.availability = 1
                  AND EXISTS (SELECT 1 FROM sub_products WHERE sub_products.products_id = products.id)";

// Aggiungi la condizione per il filtro di testo di ricerca
if (isset($_GET['search_text']) && !empty($_GET['search_text'])) {
    $product_query .= " AND products.title LIKE '%$searchText%' ";
}

// Aggiungi l'ordinamento e la limitazione
$product_query .= " LIMIT $items_per_page OFFSET $offset";

$product_result = $mysqli->query($product_query);

// Genera i pulsanti di paginazione
$pagination_html = '';
if ($total_pages > 1) {
    $pagination_html .= '<ul class="pagination">';
    
    // Pulsante "Indietro"
    if ($currentPage > 1) {
        $pagination_html .= '<li class="prev"><a href="?page=' . ($currentPage - 1) . '&search_text=' . urlencode($searchText) . '">Indietro</a></li>';
    }

    // Pulsanti delle pagine
    for ($i = 1; $i <= $total_pages; $i++) {
        $active_class = ($i == $currentPage) ? 'class="active"' : '';
        $pagination_html .= '<li ' . $active_class . '><a href="?page=' . $i . '&search_text=' . urlencode($searchText) . '">' . $i . '</a></li>';
        
        // Aggiungi i puntini di sospensione
        if ($i == 2 && $currentPage > 3) {
            $pagination_html .= '<li><span>...</span></li>';
        } elseif ($i == $total_pages - 1 && $currentPage < $total_pages - 2) {
            $pagination_html .= '<li><span>...</span></li>';
        }
    }

    // Pulsante "Avanti"
    if ($currentPage < $total_pages) {
        $pagination_html .= '<li class="next"><a href="?page=' . ($currentPage + 1) . '&search_text=' . urlencode($searchText) . '">Avanti</a></li>';
    }

    $pagination_html .= '</ul>';
}

// Imposta il contenuto della paginazione nel tuo body
$body->setContent("pagination", $pagination_html);

// Passa il conteggio dei prodotti e il numero di pagine al template
$body->setContent("total_products", $total_products);
$body->setContent("total_pages", $total_pages);

// Passa le opzioni di taglia e colore al template
$body->setContent("sizes", $sizes);
$body->setContent("colors", $colors);
$body->setContent("selected_size", $selected_size);
$body->setContent("selected_color", $selected_color);

$main->setContent("dynamic", $body->get());
$main->close();
?>