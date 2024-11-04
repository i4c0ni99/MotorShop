session_start(); // Avvia la sessione dell'utente

require "include/template2.inc.php"; // Include il file di template principale
require "include/dbms.inc.php"; // Include il file di gestione del database

$main = new Template("skins/multikart_all_in_one/back-end/frame-private.html"); // Inizializza il template principale
$body = new Template("skins/multikart_all_in_one/back-end/user-list.html"); // Inizializza il template per la lista
utenti

if (isset($_SESSION['user']) && $_SESSION['user']['groups'] == '1') { // Verifica se l'utente è loggato e ha i permessi

$current_user_email = $_SESSION['user']; // Recupera l'email dell'utente attuale

// Funzione per caricare gli utenti
function loadUsers($mysqli, $current_user_email, $searchQuery = null) {
global $body; // Rende accessibile il template del corpo

// Query per selezionare gli utenti
$query = "SELECT users.name, users.surname, users.email, groups.roul
FROM users
JOIN users_has_groups ON users.email = users_has_groups.users_email
JOIN groups ON groups.id = users_has_groups.groups_id
WHERE users.email != ?"; // Esclude l'utente attuale

if ($searchQuery) { // Se è presente una query di ricerca
$query .= " AND (users.name LIKE ? OR users.surname LIKE ? OR users.email LIKE ?)";
$stmt = $mysqli->prepare($query);
$likeQuery = "%$searchQuery%"; // Imposta il filtro di ricerca
$stmt->bind_param('ssss', $current_user_email, $likeQuery, $likeQuery, $likeQuery);
} else {
$stmt = $mysqli->prepare($query);
$stmt->bind_param('s', $current_user_email);
}

$stmt->execute(); // Esegue la query
$result = $stmt->get_result(); // Ottiene il risultato

$body->setContent("users", []); // Pulisce il contenuto esistente

// Imposta i dati degli utenti nel template
while ($row = $result->fetch_assoc()) {
$body->setContent("name", $row['name']);
$body->setContent("surname", $row['surname']);
$body->setContent("email", $row['email']);
$body->setContent("roul", $row['roul']);
// Imposta il checkbox a seconda del ruolo
$body->setContent('src', $row['roul'] == 'Admin' ? '<input id="checkall" class="checkbox_animated check-it"
    type="checkbox" checked="" post data-roul=0 data-email=' . $row[' email'] . '>'
    : '<input id="checkall" class="checkbox_animated check-it" type="checkbox" post data-roul=1 data-email=' .
    $row['email'] . '>' ); } } // Carica gli utenti all'inizio loadUsers($mysqli, $current_user_email); if
    (isset($_POST['change_role'])) { // Controlla se è stato inviato un cambiamento di ruolo if
    (isset($_POST['selected_user'])) { $email=$mysqli->real_escape_string($_POST['selected_user']); // Sanifica l'input
$result = $mysqli->query("SELECT groups_id FROM users_has_groups WHERE users_email = '$email'"); // Ottiene il ruolo
attuale
if ($result) {
$row = $result->fetch_assoc();
$currentRole = $row['groups_id'];
$newRole = ($currentRole == 1) ? 2 : 1; // Cambia il ruolo
$updateQuery = "UPDATE users_has_groups SET groups_id = '$newRole' WHERE users_email = '$email'"; // Query per
aggiornare il ruolo
$updateResult = $mysqli->query($updateQuery);

if ($updateResult) {
echo "Il ruolo è stato cambiato con successo.";
echo json_encode(['success' => 'success']); // Restituisce successo in JSON
} else {
echo "Errore nell'aggiornamento del ruolo: " . $mysqli->error; // Gestisce errore
}
} else {
echo "Errore nell'ottenere il ruolo attuale: " . $mysqli->error;
}
} else {
echo "Nessun utente selezionato."; // Messaggio se non ci sono utenti selezionati
}
}

if (isset($_POST['delete-user-button'])) { // Controlla se è stato inviato il comando di eliminazione
if (isset($_POST['selected_user'])) {
$delete = $mysqli->real_escape_string($_POST['selected_user']); // Sanifica l'email da eliminare
$oid = $mysqli->query("DELETE FROM users WHERE email = '$delete'"); // Elimina l'utente dal database
header("location:/../MotorShop/user-list.php"); // Reindirizza alla lista utenti
}
}

if (isset($_POST['search-query'])) { // Controlla se è stata inviata una query di ricerca
$searchQuery = $mysqli->real_escape_string($_POST['search-query']); // Sanifica la query
loadUsers($mysqli, $current_user_email, $searchQuery); // Ricarica gli utenti con la ricerca
}

$main->setContent("body", $body->get()); // Imposta il contenuto del corpo nel template principale
$main->close(); // Chiude il template

} else {
header("Location: /MotorShop/login.php"); // Reindirizza al login se non autorizzato
exit;
}

?>