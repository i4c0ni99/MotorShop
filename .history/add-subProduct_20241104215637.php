<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";

if (isset($_SESSION['user'])) {

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/add-subProduct.html");    

// ID prodotto passato via GET
if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    $body->setContent('id', $_GET['id']);
 
    $result = $mysqli->query("SELECT categories.name FROM categories JOIN products ON categories.id = products.categories_id WHERE products.id = {$productId} LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Imposta le taglie - in base alla categoria
        if ($data['name'] != 'STIVALI') {
            $body->setContent('sizes', '
                <div class="col-xl-8 col-sm-7">
                    <select class="form-control digits" id="exampleFormControlSelect1" name="size" required>
                        <option value="">Scegli una taglia</option>
                        <option>XS</option>
                        <option>S</option>
                        <option>M</option>
                        <option>L</option>
                        <option>XL</option>
                        <option>XXL</option>
                    </select>
                </div>
            ');
        } else {
            echo "<script>console.log('".$data['name']."');</script>";
            $body->setContent('sizes', '
                <div class="col-xl-8 col-sm-7">
                    <select class="form-control digits" id="exampleFormControlSelect1" name="size" required>
                    <option value="">Scegli una taglia</option>
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
        }
    } else {
        $_SESSION['error'] = "Errore durante il recupero delle informazioni del prodotto.";
        header("Location: /MotorShop/product-list.php");
        exit();
    }
} else {
    // l'ID del prodotto non valido o mancante
    $_SESSION['error'] = "ID del prodotto non valido o mancante.";
    header("Location: /MotorShop/dashBoard.php");
    exit();
}

if (isset($_POST['save'])) {
    // validazione dati
    $errors = array();

    // Prezzo
    if (isset($_POST['price'])) {
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        if ($price === false || $price < 1.00 || $price > 10000.00) {
            echo "<script>console.log('Il prezzo deve essere compreso tra 1.00 e 10000.00.');</script>";
            $errors[] = "Il prezzo deve essere compreso tra 1.00 e 2000.00.";
        }
    } else {
        echo "<script>console.log('Il prezzo è obbligatorio');</script>";
        $errors[] = "Il prezzo è obbligatorio.";
    }

    // stock
    if (isset($_POST['quantity'])) {
        $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT, array(
            "options" => array("min_range" => 1, "max_range" => 9999)
        ));
        if ($quantity === false) {
            echo "<script>console.log('Il numero di sottoprodotti deve essere compreso tra 1 e 9999.');</script>";
            $errors[] = "Il numero di sottoprodotti deve essere compreso tra 1 e 9999.";
        }
    } else {
        echo "<script>console.log(Il numero di sottoprodotti è obbligatorio.');</script>";
        $errors[] = "Il numero di sottoprodotti è obbligatorio.";
    }

    // Taglia
    if (empty($_POST['size'])) {
        $errors[] = "La taglia è obbligatoria.";
        echo "<script>console.log('La taglia è obbligatoria.');</script>";
    } else {
        $size = $_POST['size'];
    }

    // Colore
    if (empty($_POST['color'])) {
        $errors[] = "Il colore è obbligatorio.";
        echo "<script>console.log('Il colore è obbligatorio');</script>";
    } else {
        $color = $_POST['color'];
    }

    // Verifica errori
    if (empty($errors)) {

        // inserimento del sottoprodotto
        
        $insertSubProductQuery = "INSERT INTO sub_products (products_id, color, price, availability, quantity, size) VALUES ";
        $insertSubProductQuery .= "(" . intval($productId) . ", '" . $mysqli->real_escape_string($color) . "', ";
        $insertSubProductQuery .= floatval($price) . ", 1, " . intval($quantity) . ", '" . $mysqli->real_escape_string($size) . "')";
       
        if ($mysqli->query($insertSubProductQuery)) {
            $subProductId = $mysqli->insert_id; // ID del sottoprodotto appena inserito

            // immagini
            $uploadedImages = array();
            for ($i = 1; $i <= 6; $i++) {
                if ($_FILES['image' . $i]['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['image' . $i]['tmp_name'];
                    $data = file_get_contents($fileTmpPath);
                    $data64 = base64_encode($data);

                    // Inserisci l'immagine nella tabella 'images'
                    $insertImageQuery = "INSERT INTO images (product_id, sub_products_id, imgsrc) VALUES ";
                    $insertImageQuery .= "(" . intval($productId) . ", " . intval($subProductId) . ", '" . $mysqli->real_escape_string($data64) . "')";

                    // Esegui la query per l'inserimento dell'immagine
                    if ($mysqli->query($insertImageQuery)) {
                        $uploadedImages[] = "Immagine " . $i . " caricata con successo.";
                    } else {
                        $errors[] = "Errore durante l'inserimento dell'immagine " . $i . ": " . $mysqli->error;
                    }
                }
            }

            $_SESSION['success'] = "Sottoprodotto aggiunto con successo. " . implode(" ", $uploadedImages);
            header("Location: /MotorShop/subproduct-list.php?id=".$productId);
            exit();
        } else {
            $errors[] = "Errore durante l'inserimento dei dati del sottoprodotto: " . $mysqli->error;
        }
    }

    if (!empty($errors)) {
        $body->setContent("errors", implode("<br>", $errors));
    }
}
} else {
    header("Location: /MotorShop/login.php");
    exit;
}

$main->setContent('body', $body->get());
$main->close();
?>