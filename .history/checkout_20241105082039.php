<?php

session_start();

require "include/template2.inc.php";
require "include/auth.inc.php";
require "include/dbms.inc.php";
include "include/utils/priceFormatter.php";

if (isset($_SESSION['user']['groups'])) {
    $main = new Template("skins/motor-html-package/motor/frame-customer.html");
} else {
    header("Location: /MotorShop/login.php");
    exit();
}

$body = new Template("skins/motor-html-package/motor/checkout.html");

// titolo del prodotto
function getProductTitle($subproductId) {
    global $mysqli; 

    $query = "SELECT title FROM products WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $subproductId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['title'];
    } else {
        return 'Titolo non disponibile';
    }
}

$userEmail = $mysqli->real_escape_string($_SESSION['user']['email']);

// indirizzi di spedizione 
$addressesQuery = "SELECT * FROM shipping_address WHERE users_email = ?";
$stmtAddresses = $mysqli->prepare($addressesQuery);
$stmtAddresses->bind_param("s", $userEmail);
$stmtAddresses->execute();
$resultAddresses = $stmtAddresses->get_result();

$addresses = [];
while ($row = $resultAddresses->fetch_assoc()) {
    $addresses[] = $row;
}

$stmtAddresses->close();

foreach ($addresses as $address) {
    $body->setContent("id", $address['id']);
    $body->setContent("ADname", $address['name']);
    $body->setContent("ADsurname", $address['surname']);
    $body->setContent("ADphone", $address['phone']);
    $body->setContent("ADprovince", $address['province']);
    $body->setContent("ADcity", $address['city']);
    $body->setContent("ADstreetAddress", $address['streetAddress']);
    $body->setContent("ADcap", $address['cap']);
}

if (isset($_POST['add-address-button'])) {
    // Aggiunta nuovo indirizzo di spedizione
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $phone = $_POST["phone"];
    $province = $_POST["province"];
    $city = $_POST["city"];
    $address = $_POST["streetAddress"];
    $cap = $_POST["cap"];

    if ($name != "" && $surname != "" && $phone != "" && $province != "" && $city != "" && $address != "" && $cap != "") {
        $mysqli->query("INSERT INTO shipping_address (users_email, name, surname, phone, province, city, streetAddress, cap) 
                        VALUE ('{$_SESSION['user']['email']}', '$name', '$surname', '$phone', '$province', '$city', '$address', '$cap')");               
        
    }
}

// totale ordine
$totalPrice = 0;
$products = [];
$sub_quantity = 0;

// prodotti dal carrello
$userEmail = $_SESSION['user']['email'];
$query = "SELECT c.subproduct_id, c.quantity, sp.products_id, sp.price, sp.quantity AS prod_quantity, sp.availability, sp.color, sp.size, i.imgsrc, i.id 
          FROM cart c 
          JOIN sub_products sp ON c.subproduct_id = sp.id 
          INNER JOIN images i ON sp.products_id = i.product_id 
          WHERE c.user_email = ? 
          GROUP BY sp.products_id";
$stmt = $mysqli->prepare($query);
if ($stmt) {
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($cartItem = $result->fetch_assoc()) {
        $subproductId = $cartItem['subproduct_id'];
        $sub_quantity = $cartItem['prod_quantity'];
        $productQuery = "SELECT title FROM products WHERE id = ?";
        $stmt_product = $mysqli->prepare($productQuery);
        if ($stmt_product) {
            $stmt_product->bind_param("i", $cartItem['products_id']);
            $stmt_product->execute();
            $productResult = $stmt_product->get_result();
            if ($productData = $productResult->fetch_assoc()) {
                $title = $productData['title'];
                $size = $cartItem['size'];
                $color = $cartItem['color'];

                $price = floatval($cartItem['price']);
                $quantity = intval($cartItem['quantity']);
                $availability = intval($cartItem['availability']);
                $stockQuantity = intval($cartItem['prod_quantity']);

                // verifica se il sottoprodotto è disponibile 
                if ($availability == 1 && $quantity <= $stockQuantity) {
                    
                    $subtotal = $price * $quantity;
                    $totalPrice += $subtotal;

                    // dettagli del prodotto nell'array
                    $products[] = [
                        'subproduct_id' => $subproductId, 
                        'title' => $title,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal,
                        'quantityCheck' => $stockQuantity - $quantity
                    ];
                    $body->setContent("title", $title);
                    $body->setContent("quantity", $quantity);
                    $body->setContent("size", $size);
                    $body->setContent("color", $color);
                    $body->setContent("price", priceFormatter($subtotal));
                } else {
                    echo "Prodotto con ID $subproductId non disponibile o quantità insufficiente. Sarà escluso dall'ordine.<br>";
                }
            }
            $stmt_product->close();
        }
    }
    $stmt->close();
    $body->setContent("total_price", priceFormatter($totalPrice));
} else {
    echo "Errore nella query del carrello: " . $mysqli->error;
}

// verifica se ci sono prodotti da acquistare
if (empty($products)) {
    echo "Nessun prodotto disponibile nel carrello per l'ordine.";
    exit;
}

// vrifica se l' indirizzo di spedizione e il metodo di pagamento sono stati selezionati
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address_list']) && isset($_POST['payment_method'])) {
    
    $shippingAddressId = $mysqli->real_escape_string($_POST['address_list']);
    
    // Prodotti nel carrello dell'utente
    $queryCart = "SELECT c.subproduct_id, c.quantity, sp.products_id, sp.price,sp.quantity as prod_quantity, sp.availability, sp.color, sp.size, i.imgsrc,i.id FROM cart c JOIN sub_products sp ON c.subproduct_id = sp.id INNER JOIN images i ON sp.products_id = i.product_id WHERE c.user_email ='$userEmail' GROUP BY sp.products_id";
    $resultCart = $mysqli->query($queryCart);


if ($resultCart) {

    $products = []; // salva i dettagli dei prodotti

     // calcola importo totale e salva i dati dei prodotti nell'array
     while ($row = $resultCart->fetch_assoc()) {
        
        // Dati carrello
        $subproductId = $row['subproduct_id'];
        $quantity = $row['quantity'];
        // Dati sottoprodotto
        $price = $row['price'];
        $availability = $row['availability'];
        $stockQuantity = $row['stock'];
         
         if ($availability == 1 && $cartQuantity <= $stockQuantity) {
            
        // totale parziale per ciascun sottoprodotto nel carrello
        $subtotal = $price * $quantity;
        $totalPrice += $subtotal; 

        $productTitle = getProductTitle($subproductId);
        
        $products[] = [
            'subproduct_id' => $subproductId, 
            'title' => $productTitle,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'quantityCheck' => $stockQuantity - $quantity
        ];
    } else {
            echo "Prodotto con ID $subproductId non disponibile o quantità insufficiente. Sarà escluso dall'ordine.<br>";
        }
    }
    
    // numero random per il codice ordine
    $uniqueOrderNumber = generateUniqueOrderNumber($mysqli);
    $orderDetails = $mysqli->real_escape_string($_POST['details']);
    $paymentMethod = $mysqli->real_escape_string($_POST['payment_method']);

    // Data corrente
    $currentDate = date('Y-m-d H:i:s');

    // Stato dell'ordine, di default
    $orderState = "pending";

    // inserisci ordine
    $insertOrderQuery = "INSERT INTO orders (shipping_address_id, totalPrice, details, paymentMethod, date, state, number, users_email) 
                     VALUES ('$shippingAddressId', $totalPrice, '$orderDetails', '$paymentMethod', '$currentDate', '$orderState', '$uniqueOrderNumber', '$userEmail')";

if ($mysqli->query($insertOrderQuery)) {
    // ID dell'ordine inserito
    $orderId = $mysqli->insert_id;

    // Aggiungi i prodotti associati all'ordine nella tabella orders_has_products
    foreach ($products as $product) {
        $subproductId = $product['subproduct_id']; 
        $quantity = $product['quantity']; // Quantità nel carrello

        // Query per inserire i dati nella tabella orders_has_products
        $insertOrderHasProductsQuery = "INSERT INTO orders_has_products (order_id, sub_products_id, quantity)
                                        VALUES (?, ?, ?)";

        $stmtOrderHasProducts = $mysqli->prepare($insertOrderHasProductsQuery);
        $stmtOrderHasProducts->bind_param("iii", $orderId, $subproductId, $quantity);

        if ($stmtOrderHasProducts->execute()) {
            echo "Prodotto con ID $subproductId inserito correttamente nell'ordine.<br>";
        } else {
            // Errore
            echo "Errore durante l'inserimento del prodotto con ID $subproductId nell'ordine: " . $stmtOrderHasProducts->error . "<br>";
        }

        $stmtOrderHasProducts->close();
    }
    

echo "Ordine inserito con successo! Numero ordine: " . $uniqueOrderNumber;

// Recupera i dettagli dell'indirizzo di spedizione selezionato
$addressQuery = "SELECT * FROM shipping_address WHERE id = ?";
$stmtAddress = $mysqli->prepare($addressQuery);
$stmtAddress->bind_param("i", $shippingAddressId);
$stmtAddress->execute();
$resultAddress = $stmtAddress->get_result();
$address = $resultAddress->fetch_assoc();
$stmtAddress->close();

// email
$to = $userEmail;
$subject = 'Conferma Ordine #' . $uniqueOrderNumber;
$message = '<html><body>';
$message .= '<h2>Gentile cliente,</h2>';
$message .= '<p>Grazie per il tuo ordine! Ecco i dettagli:</p>';
$message .= '<h3>Indirizzo di Spedizione</h3>';
$message .= '<p>';
$message .= 'Nome: ' . $address['name'] . '<br>';
$message .= 'Cognome: ' . $address['surname'] . '<br>';
$message .= 'Telefono: ' . $address['phone'] . '<br>';
$message .= 'Provincia: ' . $address['province'] . '<br>';
$message .= 'Città: ' . $address['city'] . '<br>';
$message .= 'Indirizzo: ' . $address['streetAddress'] . '<br>';
$message .= 'CAP: ' . $address['cap'] . '<br>';
$message .= '</p>';
$message .= '<h3>Riepilogo Ordine</h3>';
$message .= '<table border="1">';
$message .= '<tr><th>Prodotto</th><th>Quantità</th><th>Subtotale</th></tr>';
foreach ($products as $product) {
    $message .= '<tr>';
    $message .= '<td>' . htmlspecialchars($product['title']) . '</td>';
    $message .= '<td>' . $product['quantity'] . '</td>';
    $message .= '<td>' . priceFormatter($product['subtotal']) . '</td>';
    $message .= '</tr>';
}
$message .= '</table>';
$message .= '<p><strong>Totale Ordine:</strong> ' . priceFormatter($totalPrice) . '</p>';
$message .= '<p>Dettagli aggiuntivi: ' . $orderDetails . '</p>';
$message .= '<p>Metodo di Pagamento: ' . $paymentMethod . '</p>';
$message .= '<p>Grazie per aver scelto il nostro negozio!</p>';
$message .= '</body></html>';
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: <noreply@motorshop.com>' . "\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "Email di conferma inviata con successo.";
} else {
    echo "Errore durante l'invio dell'email di conferma.";
}
        
        
        // svuota il carrello
$deleteCartQuery = "DELETE FROM cart WHERE user_email = '$userEmail'";
if ($mysqli->query($deleteCartQuery)) {
    echo "Carrello svuotato con successo.";
    foreach ($products as $prod) {
        // Verifica se la quantità richiesta è maggiore di 0
        if ($prod['quantityCheck'] > 0) {
            // Ottieni la quantità disponibile del sottoprodotto
            $result = $mysqli->query("SELECT quantity FROM sub_products WHERE id = " . intval($prod['subproduct_id']));
            $subProduct = $result->fetch_assoc();
            $availableQuantity = $subProduct['quantity'];

            // Se la quantità da acquistare è maggiore della quantità disponibile, inserisci solo la quantità disponibile
            $quantityToBuy = min(intval($prod['quantity']), $availableQuantity);

            // Aggiorna la quantità del sottoprodotto
            $mysqli->query("UPDATE sub_products SET quantity = quantity - $quantityToBuy WHERE id = " . intval($prod['subproduct_id']));

            // Se la quantità disponibile diventa zero, aggiorna la disponibilità a 0
            if ($availableQuantity - $quantityToBuy <= 0) {
                $mysqli->query("UPDATE sub_products SET availability = 0 WHERE id = " . intval($prod['subproduct_id']));
            }

            echo "Quantità inserita nell'ordine: $quantityToBuy per il sottoprodotto ID " . $prod['subproduct_id'];
        } else {
            // Se la quantità da acquistare è zero o negativa, aggiorna direttamente la disponibilità a 0
            $mysqli->query("UPDATE sub_products SET quantity = quantity - " . intval($prod['quantity']) . ", availability = 0 WHERE id = " . intval($prod['subproduct_id']));
            echo "Quantità zero inserita nell'ordine per il sottoprodotto ID " . $prod['subproduct_id'];
        }
        echo "Errore durante l'eliminazione dei prodotti dal carrello: " . $mysqli->error;
    }
    echo json_encode(['success' => 'success']);
} else {
    echo "Errore nello svuotamento del carrello.";
        
        }
        
    } else {
        echo "Errore durante l'inserimento dell'ordine: 1" . $mysqli->error;
    }
} else {
    echo "Errore durante la query del carrello: " . $mysqli->error;
}
    
} else{
    echo "Errore: nessun indirizzo di spedizione selezionato.";
}

// generare un numero casuale univoco, di 5 cifre, per l'ordine
function generateUniqueOrderNumber($mysqli) {
    $uniqueNumber = mt_rand(10000, 99999);
    $query = "SELECT number FROM orders WHERE number = '$uniqueNumber'";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        // Se il numero è già presente, richiama la funzione per generare un altro numero
        return generateUniqueOrderNumber($mysqli);
    } else {
        return $uniqueNumber;
    }
}

// formattazione prezzo
function priceFormatter($price) {
// 2 decimali
    return '€ ' . number_format($price, 2);
}

$main->setContent("dynamic", $body->get());
$main->close();

?>