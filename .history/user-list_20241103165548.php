<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/user-list.html");

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

$current_user_email = $_SESSION['user'];

// Funzione per caricare utenti
function loadUsers($mysqli, $current_user_email) {
    global $body;
    
    $query = "SELECT users.name, users.surname, users.email, groups.roul 
              FROM users 
              JOIN users_has_groups ON users.email = users_has_groups.users_email 
              JOIN groups ON groups.id = users_has_groups.groups_id
              WHERE users.email != ?";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $current_user_email);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Svuota il contenuto degli utenti
    $body->setContent("users", []);

    // Imposta i dati degli utenti nel template
    while ($row = $result->fetch_assoc()) {
        $body->setContent("name", $row['name']);
        $body->setContent("surname", $row['surname']);
        $body->setContent("email", $row['email']);
        $body->setContent("roul", $row['roul']);
        // Checkbox a seconda del ruolo
        $body->setContent('src', $row['roul'] == 'Admin' ? '<input id="checkall" class="checkbox_animated check-it" type="checkbox" checked="" post data-roul=0 data-email=' . $row['email'] . '>' : '<input id="checkall" class="checkbox_animated check-it" type="checkbox" post data-roul=1 data-email=' . $row['email'] . '>');
    }
}

// Carica lista degli utenti
loadUsers($mysqli, $current_user_email);

if (isset($_POST['change_role'])) {
    if (isset($_POST['selected_user'])) {
        $email = $mysqli->real_escape_string($_POST['selected_user']);
        
        // Query ruolo
        $result = $mysqli->query("SELECT groups_id FROM users_has_groups WHERE users_email = '$email'");
        if ($result) {
            // Ottieni ruolo attuale
            $row = $result->fetch_assoc();
            $currentRole = $row['groups_id'];
            // Cambia ruolo
            $newRole = ($currentRole == 1) ? 2 : 1;
            $updateQuery = "UPDATE users_has_groups SET groups_id = '$newRole' WHERE users_email = '$email'";
            $updateResult = $mysqli->query($updateQuery);
            
            // Verifica aggiornamento
            if ($updateResult) {
                echo "Il ruolo è stato cambiato con successo.";
                echo json_encode(['success' => 'success']);
                
            } else {
                echo "Errore nell'aggiornamento del ruolo: " . $mysqli->error;
            }
        } else {
            echo "Errore nell'ottenere il ruolo attuale: " . $mysqli->error;
        }
    } else {
        echo "Nessun utente selezionato.";
    }
}

if (isset($_POST['delete-user-button'])) {
    // Elimina utenti selezionati
    if (isset($_POST['selected_user'])) {
        $delete = $mysqli->real_escape_string($_POST['selected_user']);
        $oid = $mysqli->query("DELETE FROM users WHERE email = '$delete'");
        header("location:/../MotorShop/user-list.php");
    }
}

$main->setContent("body", $body->get());
$main->close();

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>