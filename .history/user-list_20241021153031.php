<?php

session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/user-list.html");

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') {

$current_user_email = $_SESSION['user'];

// Funzione per caricare utenti
function loadUsers($mysqli, $current_user_email, $searchQuery = null) {
    global $body;
    
    $query = "SELECT users.name, users.surname, users.email, groups.roul 
              FROM users 
              JOIN users_has_groups ON users.email = users_has_groups.users_email 
              JOIN groups ON groups.id = users_has_groups.groups_id
              WHERE users.email != ?";
    
    if ($searchQuery) {
        $query .= " AND (users.name LIKE ? OR users.surname LIKE ? OR users.email LIKE ?)";
        $stmt = $mysqli->prepare($query);
        $likeQuery = "%$searchQuery%";
        $stmt->bind_param('ssss', $current_user_email, $likeQuery, $likeQuery, $likeQuery);
    } else {
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s', $current_user_email);
    }
    
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
    }
}

// Carica gli utenti all'inizio
loadUsers($mysqli, $current_user_email);

if (isset($_POST['change-role-button'])) {
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

if (isset($_POST['search-query'])) {
    $searchQuery = $mysqli->real_escape_string($_POST['search-query']);
    loadUsers($mysqli, $current_user_email, $searchQuery);
}

$main->setContent("body", $body->get());
$main->close();

} else {
    header("Location: /MotorShop/login.php");
    exit;
}

?>