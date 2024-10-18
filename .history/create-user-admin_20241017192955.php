<?php
session_start();

require "include/template2.inc.php";
require "include/dbms.inc.php";
require "include/auth.inc.php";
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html");
$body = new Template("skins/multikart_all_in_one/back-end/create-user.html");

// Funzione per cambiare il ruolo dell'utente
function toggleUserRole($userId) {
    global $mysqli;

    // Verifica il gruppo corrente dell'utente
    $groupQuery = "SELECT groups_id FROM users_has_groups WHERE users_email = '$userId'";
    $groupResult = $mysqli->query($groupQuery);
    
    if ($groupResult->num_rows > 0) {
        $groupData = $groupResult->fetch_assoc();
        $currentGroupId = $groupData['groups_id'];

        // Cambia il gruppo a 2 se è 1 e viceversa
        $newGroupId = ($currentGroupId == 1) ? 2 : 1;
        $updateGroupQuery = "UPDATE users_has_groups SET groups_id = '$newGroupId' WHERE users_email = '$userId'";
        $mysqli->query($updateGroupQuery);
    }
}

// Gestione della richiesta di cambio ruolo
if (isset($_GET['toggleRole'])) {
    $userId = $mysqli->real_escape_string($_GET['toggleRole']);
    toggleUserRole($userId);
}

// Paginazione
$usersPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $usersPerPage;

// Recupera il numero totale di utenti
$totalUsersQuery = "SELECT COUNT(*) AS total FROM users";
$totalUsersResult = $mysqli->query($totalUsersQuery);
$totalUsersRow = $totalUsersResult->fetch_assoc();
$totalUsers = $totalUsersRow['total'];

// Calcola il numero totale di pagine
$totalPages = ceil($totalUsers / $usersPerPage);

// Recupera gli utenti per la pagina corrente
$usersQuery = "SELECT users.email, users.name, users.surname, users.phone, groups_id FROM users 
               JOIN users_has_groups ON users.email = users_has_groups.users_email
               LIMIT $offset, $usersPerPage";
$usersResult = $mysqli->query($usersQuery);

// Visualizza gli utenti
if ($usersResult && $usersResult->num_rows > 0) {
    while ($user = $usersResult->fetch_assoc()) {
        $body->setContent("user_email", $user['email']);
        $body->setContent("user_name", $user['name']);
        $body->setContent("user_surname", $user['surname']);
        $body->setContent("user_phone", $user['phone']);
        $body->setContent("user_role", ($user['groups_id'] == 1) ? 'Utente' : 'Amministratore');
        $body->setContent("toggle_role_link", "/MotorShop/create-user-admin.php?toggleRole=" . $user['email']);
    }
} else {
    $body->setContent("no_users", "Nessun utente trovato.");
}

// Visualizzazione della paginazione
if ($totalPages > 1) {
    $pagination = '';
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $page) {
            $pagination .= "<span class='current-page'>$i</span> "; // Pagina corrente
        } else {
            $pagination .= "<a href='/MotorShop/create-user-admin.php?page=$i'>$i</a> "; // Link per altre pagine
        }
    }
    $body->setContent("pagination", $pagination);
} else {
    $body->setContent("pagination", ''); // Nessuna paginazione necessaria
}

$main->setContent("body", $body->get());
$main->close();
?>